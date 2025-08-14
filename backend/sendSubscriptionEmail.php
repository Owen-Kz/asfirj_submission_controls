<?php

function SendSubscriptionEmail($RecipientEmail) {
    require_once __DIR__ . '/../vendor/autoload.php';
    include __DIR__ . '/exportENV.php';
    include __DIR__ . '/db.php';

    $apiKey = $_ENV['BREVO_API_KEY'];
    $senderEmail = $_ENV["BREVO_EMAIL"];

    if (!filter_var($RecipientEmail, FILTER_VALIDATE_EMAIL)) {
        return ['status' => 'error', 'message' => 'Invalid email format'];
    }

    try {
        $config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(
            new \GuzzleHttp\Client(),
            $config
        );

        $currentYear = date("Y");
        $subject = "Welcome to ASFI Research Journal Newsletter";
        
        // Create email object
        $email = new \Brevo\Client\Model\SendSmtpEmail();
        
        // Set sender
        $sender = new \Brevo\Client\Model\SendSmtpEmailSender();
        $sender->setEmail($senderEmail);
        $sender->setName("ASFI Research Journal");
        $email->setSender($sender);

        // Set recipient
        $email->setTo([['email' => $RecipientEmail]]);
        
        // Set unsubscribe headers
        $headers = new \stdClass();
        $headers->{'List-Unsubscribe'} = '<https://asfirj.org/unsubscribe?email='.urlencode($RecipientEmail).'>';
        $headers->{'List-Unsubscribe-Post'} = 'List-Unsubscribe=One-Click';
        $email->setHeaders($headers);

        // HTML content with proper styling
        $htmlContent = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Subscription</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .button { background-color: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .footer { font-size: 0.8em; color: #7f8c8d; border-top: 1px solid #eee; padding-top: 15px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Thank you for subscribing!</h2>
    </div>
    
    <p>Welcome to the ASFI Research Journal newsletter. We'll keep you updated with the latest information and news about our journal platform.</p>
    
    <p>If you haven't already signed up for our journal platform, please register here:</p>
    
    <p style="margin: 20px 0;">
        <a href="https://asfirj.org/portal/signup/" class="button">Sign Up for Journal Platform</a>
    </p>
    
    <div class="footer">
        <p>ASFI Research Journal &copy; $currentYear</p>
        <p style="font-size: 0.8em;">
            <a href="https://asfirj.org/unsubscribe?email=$RecipientEmail">Unsubscribe</a> | 
            <a href="https://asfirj.org/contact">Contact Us</a>
        </p>
    </div>
</body>
</html>
EOT;

        $email->setSubject($subject);
        $email->setHtmlContent($htmlContent);

        $response = $apiInstance->sendTransacEmail($email);
        return ['status' => 'success', 'message' => 'Subscription email sent successfully'];

    } catch (\Brevo\Client\ApiException $e) {
        return ['status' => 'error', 'message' => 'Failed to send email: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}