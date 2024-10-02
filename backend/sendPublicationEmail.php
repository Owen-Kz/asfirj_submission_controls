<?php

$data = json_decode(file_get_contents("php://input"), true);
$subject = $data["subject"];
$RecipientEmail  = $data["to"];
$message = $data["message"];
$fileName = $data["fileName"];
// function SendPublicationEMail($RecipientEmail, $message, $subject) {
    require_once __DIR__ . '/../vendor/autoload.php';

    // Import Environment Variables
    include __DIR__ . '/exportENV.php';
    include __DIR__ . '/db.php';

    $apiKey = $_ENV['BREVO_API_KEY'];        echo $ccEmail;

    $senderEmail = $_ENV["BREVO_EMAIL"];

    if (isset($RecipientEmail)) {
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
            $attachment = new \Brevo\Client\Model\SendSmtpEmailAttachment();
            // Set sender
            $sender = new \Brevo\Client\Model\SendSmtpEmailSender();
            $sender->setEmail($senderEmail);
            $sender->setName('ASFI Research Journal');
            $email->setSender($sender);
            $attachment->setName($fileName); // Set the name of your file
            // $attachment->setUrl("https://asfirj.org/useruploads/manuscripts/$fileName"); 
            // Offline 
            
            $attachment->setUrl("https://asfirj.org/useruploads/manuscripts/$fileName"); 

            $email->setAttachment([$attachment]);
            // Set recipient
            $email->setTo([['email' => $RecipientEmail, 'name' => $RecipientName]]);
            $email->setSubject("$subject");
            $date = date('d-M-Y');
            $htmlContent = <<<EOT
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>$subject</title>
                </head>
                <body>
                    <p>$date</p>
                    
                    <p>Dear $prefix $RecipientName</p>
                    $message
                <p>Sincerely,</p>

                <p>ASFIRJ Article Production Office<br>
                <a href="mailto:production@asfirj.org">production@asfirj.org</a></p>

                <p>ASFI Research Journal<br>
                Excellence. Quality. Impact<br>
                "Raising the bar of scientific publishing in Africa"<br>
                <a href="https://asfirj.org/">https://asfirj.org/</a><br>
                <a href="mailto:asfirj@asfirj.org">asfirj@asfirj.org</a><br>
                LinkedIn: <a href="https://www.linkedin.com/in/asfi-research-journal-1b9929309">www.linkedin.com/in/asfi-research-journal-1b9929309</a><br>
                X (formerly Twitter): <a href="https://twitter.com/asfirj1">https://twitter.com/asfirj1</a><br>
                Instagram: <a href="https://www.instagram.com/asfirj1/">https://www.instagram.com/asfirj1/</a><br>
                WhatsApp: <a href="https://chat.whatsapp.com/L8o0N0pUieOGIUHJ1hjSG3">https://chat.whatsapp.com/L8o0N0pUieOGIUHJ1hjSG3</a></p>
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
        $response = array('status' => 'error', 'message' => "Invalid Request $RecipientEmail");
    }

    echo json_encode($response);
// }
