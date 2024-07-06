<?php

function ReviewerAccountEmail($RecipientEmail, $subject, $message, $editor_email, $article_id){
    require_once __DIR__ .'/../vendor/autoload.php';
    require __DIR__. "/../backend/exportENV.php";
    include __DIR__."/../backend/db.php";

    $api = $_ENV['SENDGRID_API_KEY'];
    $senderEmail = $_ENV["SENDGRID_EMAIL"];
    $currentYear = date('Y');  // Get the current year

    if ($RecipientEmail) {
        $encryptedButton = md5($RecipientEmail);

        $sendgrid = new \SendGrid($api);

        try {
            $contentArray = json_decode($message, true);

            function convertToHTML($contentArray) {
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

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom($senderEmail, "ASFIRJ");
            $email->setSubject($subject);
            $email->addTo($RecipientEmail);
            $email->addContent("text/html", $emailContent);

            $response = $sendgrid->send($email);

            $stmt = $con->prepare("UPDATE `sent_emails` SET `status` = 'Delivered' WHERE `article_id` = ? AND `sender` = ? AND `subject` = ?");
            $stmt->bind_param("sss", $article_id, $editor_email, $subject);
            $stmt->execute();

            return true;
        } catch (Exception $e) {
            $response = array('status' => 'Internal Error', 'message' => 'Caught exception: '. $e->getMessage() ."\n");
            return false;
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Invalid Request');
        return false;
    }
}
