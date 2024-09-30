<?php

function AcceptanceEmailToEditor($RecipientEmail, $subject, $message, $editor_email, $article_id,  $ccEmails, $bccEmails) {
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
            new GuzzleHttp\Client(), $config
        );

        try {
            $emailContent = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Content</title>
</head>
<body>
    <div>
        <p>Article With Id: <b>$article_id</b> has been accepted by the Handling editor and is ready for publication</p>
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
  if (!empty($ccEmails)) {
    $ccRecipients = [];
    foreach ($ccEmails as $ccEmail) {
        $ccRecipient = new \Brevo\Client\Model\SendSmtpEmailCc();
        $ccRecipient->setEmail($ccEmail);
        $ccRecipients[] = $ccRecipient;
    }
    $email->setCc($ccRecipients);
}

// Set BCC recipients if provided
if (!empty($bccEmails)) {
    $bccRecipients = [];
    foreach ($bccEmails as $bccEmail) {
        $bccRecipient = new \Brevo\Client\Model\SendSmtpEmailBcc();
        $bccRecipient->setEmail($bccEmail);
        $bccRecipients[] = $bccRecipient;
    }
    $email->setBcc($bccRecipients);
}
            $result = $apiInstance->sendTransacEmail($email);

            // Update database status
            $stmt = $con->prepare("UPDATE `sent_emails` SET `status` = 'Delivered' WHERE `article_id` = ? AND `sender` = ? AND `subject` = ?");
            $stmt->bind_param("sss", $article_id, $editor_email, $subject);
            $stmt->execute();

            return true;
        } catch (\Brevo\Client\ApiException $e) {
            $response = array('status' => 'Internal Error', 'message' => 'Caught exception: ' . $e->getMessage() . "\n");
            return false;
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Invalid Request');
        return false;
    }
}
?>
