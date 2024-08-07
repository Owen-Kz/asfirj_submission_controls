<?php

function SendNewSubmissionEmail($RecipientEmail, $manuscriptTitle, $manuscriptId) {
    require_once __DIR__ . '/../vendor/autoload.php';

    // Import Environment Variables
    include __DIR__ . '/exportENV.php';
    include __DIR__ . '/db.php';

    $apiKey = $_ENV['BREVO_API_KEY'];
    $senderEmail = $_ENV["BREVO_EMAIL"];

    if ($RecipientEmail) {
        $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` = ?");
        $stmt->bind_param("s", $RecipientEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = mysqli_num_rows($result);

        if ($count > 0) {
            $row = mysqli_fetch_array($result);
            $prefix = $row["prefix"];
            $RecipientName = $row["firstname"];

            $config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
            $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(
                new \GuzzleHttp\Client(), 
                $config
            );

            $email = new \Brevo\Client\Model\SendSmtpEmail();
            
            // Set sender
            $sender = new \Brevo\Client\Model\SendSmtpEmailSender();
            $sender->setEmail($senderEmail);
            $sender->setName('ASFI Research Journal');
            $email->setSender($sender);
            
            // Set recipient
            $email->setTo([['email' => $RecipientEmail, 'name' => $RecipientName]]);
            $email->setSubject("Paper Submitted Successfully");

            $htmlContent = <<<EOT
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Email Content</title>
            </head>
            <body>
                <h2> Hi, $prefix $RecipientName</h2>
                <p>Your Paper has been submitted successfully. You will receive a notification via this email when a decision is made.</p>
                <p><b>Manuscript Title & ID:</b> $manuscriptTitle ($manuscriptId)</p>
                <footer>
                    <p>ASFI Research Journal (c) {date("Y")}</p>
                </footer>
            </body>
            </html>
            EOT;

            $email->setHtmlContent($htmlContent);

            try {
                $response = $apiInstance->sendTransacEmail($email);
                $response = array('status' => 'success', 'message' => 'Email sent');
            } catch (\Brevo\Client\ApiException $e) {
                $response = array('status' => 'Internal Error', 'message' => 'Caught exception: ' . $e->getMessage());
            }
        } else {
            $response = array('status' => 'error', 'message' => 'User does not exist on Our servers');
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Invalid Request');
    }

    print_r($response);
}
