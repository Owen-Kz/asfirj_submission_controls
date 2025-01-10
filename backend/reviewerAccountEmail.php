<?php

function ReviewerAccountEmail($RecipientEmail, $subject, $message, $editor_email, $article_id, $ccEmails, $bccEmails, $attachments)
{
    require_once __DIR__ . '/../vendor/autoload.php';
    require __DIR__ . '/../backend/exportENV.php';
    include __DIR__ . '/../backend/db.php';

    $apiKey = $_ENV['BREVO_API_KEY'];
    $senderEmail = $_ENV['BREVO_EMAIL'];
    $currentYear = date('Y');  // Get the current year

    if ($RecipientEmail) {
        $encryptedButton = md5($RecipientEmail);

        $config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(
            new GuzzleHttp\Client(),
            $config
        );

        function convertToHTML($contentArray)
        {
            $html = '';
            $listOpen = false;

            foreach ($contentArray as $item) {
                if (isset($item['attributes']['list'])) {
                    if ($item['attributes']['list'] === 'ordered') {
                        if (!$listOpen) {
                            $html .= '<ol>';
                            $listOpen = true;
                        }
                        $html .= '<li>' . htmlspecialchars($item['insert'], ENT_QUOTES, 'UTF-8') . '</li>';
                    } elseif ($item['attributes']['list'] === 'bullet') {
                        if (!$listOpen) {
                            $html .= '<ul>';
                            $listOpen = true;
                        }
                        $html .= '<li>' . htmlspecialchars($item['insert'], ENT_QUOTES, 'UTF-8') . '</li>';
                    }
                } else {
                    if ($listOpen) {
                        $html .= (isset($item['attributes']['list']) && $item['attributes']['list'] === 'ordered') ? '</ol>' : '</ul>';
                        $listOpen = false;
                    }

                    if (isset($item['insert']['image'])) {
                        $src = htmlspecialchars($item['insert']['image'], ENT_QUOTES, 'UTF-8');
                        $html .= '<img src="' . $src . '" alt="Image">';
                    } else {
                        $text = nl2br(htmlspecialchars($item['insert'], ENT_QUOTES, 'UTF-8'));
                        if (isset($item['attributes'])) {
                            if (isset($item['attributes']['link'])) {
                                $link = htmlspecialchars($item['attributes']['link'], ENT_QUOTES, 'UTF-8');
                                $text = '<a href="' . $link . '">' . $text . '</a>';
                            }
                            if (isset($item['attributes']['underline'])) {
                                $text = '<u>' . $text . '</u>';
                            }
                            if (isset($item['attributes']['color'])) {
                                $color = htmlspecialchars($item['attributes']['color'], ENT_QUOTES, 'UTF-8');
                                $text = '<span style="color:' . $color . ';">' . $text . '</span>';
                            }
                            if (isset($item['attributes']['bold'])) {
                                $text = '<strong>' . $text . '</strong>';
                            }
                        }
                        $html .= $text;
                    }
                }
            }

            if ($listOpen) {
                $html .= (isset($item['attributes']['list']) && $item['attributes']['list'] === 'ordered') ? '</ol>' : '</ul>';
            }

            return $html;
        }

        try {
            $contentArray = json_decode($message, true);
            $htmlContent = convertToHTML($contentArray);
            $emailContent = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Content</title>
</head>
<body>
    <div>
        $htmlContent
    </div>
    <footer>
        <p>ASFI Research Journal (c) $currentYear</p>
    </footer>
</body>
</html>
EOT;

            $email = new \Brevo\Client\Model\SendSmtpEmail();
            $email->setSubject($subject);
            $email->setHtmlContent($emailContent);

            // Create and set sender
            $sender = new \Brevo\Client\Model\SendSmtpEmailSender();
            $sender->setEmail($senderEmail);
            $sender->setName('ASFI Research Journal');
            $email->setSender($sender);

            // Set recipient
            $recipient = new \Brevo\Client\Model\SendSmtpEmailTo();
            $recipient->setEmail($RecipientEmail);
            $email->setTo([$recipient]);

            // Set CC recipients if provided

            if (!empty($ccEmails) && count($ccEmails) > 0 && $ccEmails != [""]) {
                $ccRecipients = [];
                foreach ($ccEmails as $ccEmail) {
                    $ccRecipient = new \Brevo\Client\Model\SendSmtpEmailCc();
                    $ccRecipient->setEmail($ccEmail);
                    $ccRecipients[] = $ccRecipient;
                }
                $email->setCc($ccRecipients);
            }

            // Set BCC recipients if provided
            if (!empty($bccEmails) && count($bccEmails) > 0 && $bccEmails != [""]) {
                $bccRecipients = [];
                foreach ($bccEmails as $bccEmail) {
                    $bccRecipient = new \Brevo\Client\Model\SendSmtpEmailBcc();
                    $bccRecipient->setEmail($bccEmail);
                    $bccRecipients[] = $bccRecipient;
                }
                $email->setBcc($bccRecipients);
            }

            // Add attachments to the email
            if (!empty($attachments) && is_array($attachments)) {
                $emailAttachments = [];
                foreach ($attachments as $attachment) {
                    if (isset($attachment['content'], $attachment['name'])) {
                        $emailAttachments[] = [
                            'content' => $attachment['content'],
                            'name' => $attachment['name'],
                        ];
                    } else {
                        // Handle invalid attachment structure
                        error_log("Invalid attachment structure: " . print_r($attachment, true));
                    }
                }
                $email->setAttachment($emailAttachments);
            }


            $result = $apiInstance->sendTransacEmail($email);

            // Update database status
            $stmt = $con->prepare("UPDATE `sent_emails` SET `status` = 'Delivered' WHERE `article_id` = ? AND `sender` = ? AND `subject` = ?");
            $stmt->bind_param("sss", $article_id, $editor_email, $subject);
            $stmt->execute();

            return true;
        } catch (\Brevo\Client\ApiException $e) {

            $response = array('status' => 'Internal Error', 'message' => 'Caught exception: ' . $e->getMessage() . "\n");

            echo json_encode($response);

            return false;
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Invalid Request');
        // echo json_encode($response);
        return false;
    }
}
?>