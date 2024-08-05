<?php

function EditorAccountEmail($RecipientEmail, $acceptInvitationLink, $rejectInvitationLink) {
    require_once __DIR__ . '/../vendor/autoload.php'; // If you're using Composer (recommended)
    require __DIR__ . '/../backend/exportENV.php';
    include __DIR__ . '/../backend/db.php';

    $apiKey = $_ENV['BREVO_API_KEY'];
    $senderEmail = $_ENV['BREVO_EMAIL'];

    if ($RecipientEmail) {
        $encryptedButton = md5($RecipientEmail);

        $config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(
            new GuzzleHttp\Client(), $config
        );

        try {
            $subject = "ASFIRJ Review Request";
            $message = <<<EOT
<h2>Hi,</h2>
<p>You have been invited to edit a submission on ASFIRJ.</p>
<p>Please <a href='$acceptInvitationLink'>Accept Invite</a></p>
<p><a href='$rejectInvitationLink'>Reject invitation</a></p>
<p><center><h6>" . date("Y") . " African Science Research Journal</h6></center></p>
EOT;

            $email = new \Brevo\Client\Model\SendSmtpEmail();
            $email->setSubject($subject);
            $email->setHtmlContent($message);

            // Create and set sender
            $sender = new \Brevo\Client\Model\SendSmtpEmailSender();
            $sender->setEmail($senderEmail);
            $sender->setName('ASFIRJ');
            $email->setSender($sender);

            // Set recipient
            $recipient = new \Brevo\Client\Model\SendSmtpEmailTo();
            $recipient->setEmail($RecipientEmail);
            $email->setTo([$recipient]);

            $result = $apiInstance->sendTransacEmail($email);

            $response = array('status' => 'success', 'message' => 'Email sent', 'email' => $encryptedButton);
            print json_encode($response);

        } catch (\Brevo\Client\ApiException $e) {
            $response = array('status' => 'Internal Error', 'message' => 'Caught exception: ' . $e->getMessage() . "\n");
            print json_encode($response);
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Invalid Request');
        print json_encode($response);
    }
}
?>
