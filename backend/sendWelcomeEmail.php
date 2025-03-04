<?php

function SendWelcomeEmail($RecipientEmail) {

    require_once __DIR__ . '/../vendor/autoload.php'; // If you're using Composer (recommended)

    // Import Environment Variables
    include __DIR__ . '/exportENV.php';
    include __DIR__ . '/db.php';

    $apiKey = $_ENV['BREVO_API_KEY'];
    $senderEmail = $_ENV["BREVO_EMAIL"];

    if ($RecipientEmail) {

        $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` = ?");
        if (!$stmt) {
            print("Error preparing statement: " . $stmt->error);
            return;
        }
        $stmt->bind_param("s", $RecipientEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->num_rows;

        // If user record is available in database then $count will be equal to 1
        if ($count > 0) {
            $row = $result->fetch_assoc();
            $email = $row["email"];
            $prefix = $row["prefix"];
            $RecipientName = $row["firstname"];

            $encryptedButton = md5($RecipientEmail);

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
            $recipient->setName($RecipientName);
            $email->setTo([$recipient]);

            // Set the subject and content
            $currentYear = date("Y");
            $subject = "ASFI Research Journal Account Created";
            $htmlContent = "<h2> Hi, $prefix $RecipientName</h2>
                <p>Welcome to ASFI Research Journal</p>
                <p><a href='https://process.asfirj.org/verify?e=$encryptedButton'>click here</a> to verify your account.</p>
                <p>Or paste this <a href='https://process.asfirj.org/verify?e=$encryptedButton'>https://process.asfirj.org/verify?e=$encryptedButton</a> link in your browser</p>
                <p><center><h6>African Science Research Journal</h6></center></p>";

            $message = <<<EOT
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

            $email->setSubject($subject);
            $email->setHtmlContent($message);

            try {
                $response = $apiInstance->sendTransacEmail($email);
                $response = array('status' => 'success', 'message' => 'Email sent');
                // print $response;
            } catch (\Brevo\Client\ApiException $e) {
                $response = array('status' => 'Internal Error', 'message' => 'Caught exception: ' . $e->getMessage() . "\n");
                // print $response;
            }
        } else {
            $response = array('status' => 'error', 'message' => 'User does not exist on Our servers');
            // print $response;
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Invalid Request');
        // print $response;
    }

    // print_r($response);
}

