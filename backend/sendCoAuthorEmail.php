<?php

function SendCoAuthorEmail($RecipientEmail, $password) {

    require_once __DIR__ . '/../vendor/autoload.php'; // If you're using Composer

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

            $subject = "ASFI Research Journal Account Created";
            $htmlContent = "<h2> Hi, $prefix $RecipientName</h2>
                <p> A paper was submitted listing you as a co-author, and an account has been created for you with the following details:</p>
                <ul>
                    <li>Email: <b>$RecipientEmail</b></li>
                    <li>Password: <b>$password</b></li>
                </ul>
                <p><a href='https://authors.asfirj.org/verify?a=$encryptedButton'>click here</a> to verify your account and login.</p>
                <p>Or paste this <a href='https://authors.asfirj.org/verify?a=$encryptedButton'>https://authors.asfirj.org/verify?a=$encryptedButton</a> link in your browser.</p>
                <p>You can always come back to update your password and other required information from here: <a href='https://asfirj.org/portal/updateAccount?e=$encryptedButton'>https://asfirj.org/portal/updateAccount?e=$encryptedButton</a></p>
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
                // print_r($response);
            } catch (\Brevo\Client\ApiException $e) {
                // print $e->getMessage();
            }
        } else {
            $response = ['status'=> 'error', 'message' => 'User does not exist on Our servers'];
            // print_r($response);
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Invalid Request'];
        // print_r($response);
    }
}
?>
