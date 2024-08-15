<?php

function SendSubscriptionEmail($RecipientEmail) {

    require_once __DIR__ . '/../vendor/autoload.php'; // If you're using Composer

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

            $subject = "ASFI Research Journal Account Created";
            $htmlContent = "

                    <p>Thank you for subscribing to our newsletter. <p>

<p>We will keep you updated on information and news about the Journal platform.</p>

<p>Meanwhile, If you have not signed up, for our journal platform, kindly follow the link below 👇to do so</p> <a href='https://asfirj.org/portal/signup/'>https://asfirj.org/portal/signup/</a>
            ";

            $email = new \Brevo\Client\Model\SendSmtpEmail();
            $sender = new \Brevo\Client\Model\SendSmtpEmailSender();
            $sender->setEmail($senderEmail);
            $sender->setName("ASFI Research Journal");

            $recipient = new \Brevo\Client\Model\SendSmtpEmailTo();
            $recipient->setEmail($RecipientEmail);
            $recipient->setName($RecipientName);

            $email->setSender($sender);
            $email->setTo([$recipient]);
            $email->setSubject($subject);
            $email->setHtmlContent($htmlContent);

            try {
                $response = $apiInstance->sendTransacEmail($email);
                print_r($response);
            } catch (\Brevo\Client\ApiException $e) {
                print $e->getMessage();
            }

    } else {
        $response = ['status' => 'error', 'message' => 'Invalid Request'];
        print_r($response);
    }
}
?>
