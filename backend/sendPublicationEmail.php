<?php

$data = json_decode(file_get_contents("php://input"), true);
$subject = $data["subject"];
$RecipientEmail = $data["to"];
$message = $data["message"];
$fileName = $data["fileName"];

require_once __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/exportENV.php';
include __DIR__ . '/db.php';

$apiKey = $_ENV['BREVO_API_KEY'];
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
        
        // Set sender
        $sender = new \Brevo\Client\Model\SendSmtpEmailSender();
        $sender->setEmail($senderEmail);
        $sender->setName('ASFI Research Journal');
        $email->setSender($sender);

        // Set attachment
        $attachment = new \Brevo\Client\Model\SendSmtpEmailAttachment();
        $attachment->setName($fileName);
        $attachment->setUrl("https://asfirj.org/useruploads/manuscripts/$fileName");
        $email->setAttachment([$attachment]);

        // Set recipient
        $email->setTo([['email' => $RecipientEmail, 'name' => "$prefix $RecipientName"]]);
        $email->setSubject($subject);
        
        // PROPERLY FORMATTED headers as object
        $headers = new \stdClass();
        $headers->{'List-Unsubscribe'} = '<https://asfirj.org/unsubscribe?email='.urlencode($RecipientEmail).'>';
        $headers->{'List-Unsubscribe-Post'} = 'List-Unsubscribe=One-Click';
        $email->setHeaders($headers);

        $date = date('d-M-Y');
        
        $htmlContent = <<<EOT
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .unsubscribe { font-size: 0.8em; color: #666; margin-top: 20px; }
    </style>
</head>
<body>
    $message
    <div class="unsubscribe">
        <a href="https://asfirj.org/unsubscribe?email=$RecipientEmail">Unsubscribe</a>
    </div>
</body>
</html>
EOT;

        $email->setHtmlContent($htmlContent);

        try {
            $response = $apiInstance->sendTransacEmail($email);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}