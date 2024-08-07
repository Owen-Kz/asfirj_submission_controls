<?php

function ReviewConfirmationEmail($RecipientEmail, $reviewerEmail, $status) {
    require_once __DIR__ . '/../vendor/autoload.php'; // If you're using Composer (recommended)

    // Import Environment Variables
    include __DIR__ . '/exportENV.php';
    include __DIR__ . '/db.php';

    $apiKey = $_ENV['BREVO_API_KEY'];
    $senderEmail = $_ENV["BREVO_EMAIL"];

    if ($RecipientEmail) {
        // Configure the Brevo client
        $config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(
            new \GuzzleHttp\Client(),
            $config
        );

        // Create email object
        $email = new \Brevo\Client\Model\SendSmtpEmail();

        // Set the sender
        $sender = new \Brevo\Client\Model\SendSmtpEmailSender();
        $sender->setEmail($senderEmail);
        $sender->setName('ASFI Research Journal');
        $email->setSender($sender);

        // Set the recipient
        $recipient = new \Brevo\Client\Model\SendSmtpEmailTo();
        $recipient->setEmail($RecipientEmail);
        $email->setTo([$recipient]);

        // Set the subject and content
        $email->setSubject("Review Request $status by $reviewerEmail");
        $email->setHtmlContent("<h2> Hi,</h2>
            <p>Your Request to Review an Article has been $status by the reviewer. Login to your dashboard to monitor the process.</p>
            <p><center><h6>African Science Research Journal</h6></center></p>");

        try {
            $response = $apiInstance->sendTransacEmail($email);
            $response = array('status' => 'success', 'message' => 'Email sent');
            // print $response;
        } catch (\Brevo\Client\ApiException $e) {
            $response = array('status' => 'Internal Error', 'message' => 'Caught exception: ' . $e->getMessage() . "\n");
            // print $response;
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Invalid Request');
        // print $response;
    }

    print_r($response);
}
?>
