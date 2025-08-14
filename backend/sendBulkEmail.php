<?php

function convertToHTML($contentArray) {
    $html = '';
    $listOpen = false;
    $listType = '';

    foreach ($contentArray as $item) {
        if (isset($item['attributes']['list'])) {
            $currentListType = $item['attributes']['list'];

            if ($currentListType === 'ordered' || $currentListType === 'bullet') {
                if (!$listOpen) {
                    $html .= ($currentListType === 'ordered') ? '<ol>' : '<ul>';
                    $listOpen = true;
                    $listType = $currentListType;
                } elseif ($listType !== $currentListType) {
                    $html .= ($listType === 'ordered') ? '</ol>' : '</ul>';
                    $html .= ($currentListType === 'ordered') ? '<ol>' : '<ul>';
                    $listType = $currentListType;
                }

                $html .= '<li>' . htmlspecialchars($item['insert'], ENT_QUOTES, 'UTF-8') . '</li>';
            }
        } else {
            if ($listOpen) {
                $html .= ($listType === 'ordered') ? '</ol>' : '</ul>';
                $listOpen = false;
            }

            if (isset($item['insert']['image'])) {
                $src = htmlspecialchars($item['insert']['image'], ENT_QUOTES, 'UTF-8');
                $html .= '<img src="' . $src . '" alt="Image" style="max-width:100%; height:auto;">';
            } else {
                $text = nl2br(htmlspecialchars($item['insert'], ENT_QUOTES, 'UTF-8'));
                if (isset($item['attributes'])) {
                    if (isset($item['attributes']['link'])) {
                        $link = htmlspecialchars($item['attributes']['link'], ENT_QUOTES, 'UTF-8');
                        $text = '<a href="' . $link . '" style="color:#3498db; text-decoration:underline;">' . $text . '</a>';
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
                    if (isset($item['attributes']['italic'])) {
                        $text = '<em>' . $text . '</em>';
                    }
                }
                $html .= '<p style="margin:10px 0;">' . $text . '</p>';
            }
        }
    }

    if ($listOpen) {
        $html .= ($listType === 'ordered') ? '</ol>' : '</ul>';
    }

    return $html;
}

function SendBulkEmail($RecipientEmail, $subject, $message, $editor_email, $article_id, $attachments) {
    require_once __DIR__ . '/../vendor/autoload.php';
    require __DIR__ . "/../backend/exportENV.php";
    include __DIR__ . "/../backend/db.php";

    $apiKey = $_ENV['BREVO_API_KEY'];
    $senderEmail = $_ENV["BREVO_EMAIL"];
    $currentYear = date('Y');

    if (!filter_var($RecipientEmail, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid recipient email: $RecipientEmail");
        return false;
    }

    try {
        $config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(
            new \GuzzleHttp\Client(), 
            $config
        );

        // Convert message to HTML
        $contentArray = json_decode($message, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Invalid message JSON: " . json_last_error_msg());
            return false;
        }
        
        $htmlContent = convertToHTML($contentArray);

        // Create email template with proper styling
        $emailContent = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASFI Research Journal Communication</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            max-width: 600px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        .header { 
            color: #2c3e50; 
            border-bottom: 1px solid #eee; 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
        }
        .footer { 
            font-size: 0.8em; 
            color: #7f8c8d; 
            border-top: 1px solid #eee; 
            padding-top: 15px; 
            margin-top: 30px; 
        }
        a { 
            color: #3498db; 
            text-decoration: underline; 
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>ASFI Research Journal</h2>
    </div>
    
    <div>
        $htmlContent
    </div>
    
    <div class="footer">
        <p>ASFI Research Journal &copy; $currentYear</p>
        <p style="font-size: 0.8em;">
            <a href="https://asfirj.org/unsubscribe?email=$RecipientEmail">Unsubscribe</a> | 
            <a href="https://asfirj.org/contact">Contact Us</a>
        </p>
    </div>
</body>
</html>
EOT;

        // Create email object
        $email = new \Brevo\Client\Model\SendSmtpEmail();
        
        // Set sender
        $sender = new \Brevo\Client\Model\SendSmtpEmailSender();
        $sender->setEmail($senderEmail);
        $sender->setName('ASFI Research Journal');
        $email->setSender($sender);

        // Set recipient
        $email->setTo([['email' => $RecipientEmail]]);
        
        // Set unsubscribe headers
        $headers = new \stdClass();
        $headers->{'List-Unsubscribe'} = '<https://asfirj.org/unsubscribe?email='.urlencode($RecipientEmail).'>';
        $headers->{'List-Unsubscribe-Post'} = 'List-Unsubscribe=One-Click';
        $email->setHeaders($headers);

        // Set subject and content
        $email->setSubject(htmlspecialchars($subject, ENT_QUOTES, 'UTF-8'));
        $email->setHtmlContent($emailContent);

        // Process attachments
        if (!empty($attachments) && is_array($attachments)) {
            $emailAttachments = [];
            foreach ($attachments as $attachment) {
                if (isset($attachment['content'], $attachment['name'])) {
                    $attachmentObj = new \Brevo\Client\Model\SendSmtpEmailAttachment();
                    $attachmentObj->setContent($attachment['content']);
                    $attachmentObj->setName(htmlspecialchars($attachment['name'], ENT_QUOTES, 'UTF-8'));
                    $emailAttachments[] = $attachmentObj;
                }
            }
            if (!empty($emailAttachments)) {
                $email->setAttachment($emailAttachments);
            }
        }

        // Send email
        $response = $apiInstance->sendTransacEmail($email);

        // Update database
        $stmt = $con->prepare("UPDATE `sent_emails` SET `status` = 'Delivered', `delivered_at` = NOW() WHERE `article_id` = ? AND `sender` = ? AND `subject` = ?");
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $stmt->error);
        }
        $stmt->bind_param("sss", $article_id, $editor_email, $subject);
        $stmt->execute();

        return true;

    } catch (\Brevo\Client\ApiException $e) {
        error_log("Brevo API Exception: " . $e->getMessage());
        
        // Update database with failure status
        $stmt = $con->prepare("UPDATE `sent_emails` SET `status` = 'Failed', `error_message` = ? WHERE `article_id` = ? AND `sender` = ? AND `subject` = ?");
        if ($stmt) {
            $errorMsg = substr($e->getMessage(), 0, 255); // Ensure it fits in the column
            $stmt->bind_param("ssss", $errorMsg, $article_id, $editor_email, $subject);
            $stmt->execute();
        }
        
        return false;
    } catch (Exception $e) {
        error_log("General Exception: " . $e->getMessage());
        return false;
    }
}