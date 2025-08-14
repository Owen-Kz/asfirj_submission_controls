<?php

function SendCoAuthorEmail($RecipientEmail, $password) {
    require_once __DIR__ . '/../vendor/autoload.php';
    include __DIR__ . '/exportENV.php';
    include __DIR__ . '/db.php';

    $apiKey = $_ENV['BREVO_API_KEY'];
    $senderEmail = $_ENV["BREVO_EMAIL"];
    $currentYear = date("Y");

    if (!filter_var($RecipientEmail, FILTER_VALIDATE_EMAIL)) {
        return ['status' => 'error', 'message' => 'Invalid email format'];
    }

    try {
        $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` = ?");
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $stmt->error);
        }
        
        $stmt->bind_param("s", $RecipientEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['status' => 'error', 'message' => 'Co-author not found in our records'];
        }

        $row = $result->fetch_assoc();
        $prefix = htmlspecialchars($row["prefix"]);
        $RecipientName = htmlspecialchars($row["firstname"]);
        $encryptedButton = md5($RecipientEmail);
        $loginUrl = "https://process.asfirj.org/verify?e=" . urlencode($encryptedButton);
        $updateUrl = "https://asfirj.org/portal/updateAccount?e=" . urlencode($encryptedButton);

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
        $email->setTo([['email' => $RecipientEmail, 'name' => "$prefix $RecipientName"]]);
        
        // Set unsubscribe headers
        $headers = new \stdClass();
        $headers->{'List-Unsubscribe'} = '<https://asfirj.org/unsubscribe?email='.urlencode($RecipientEmail).'>';
        $headers->{'List-Unsubscribe-Post'} = 'List-Unsubscribe=One-Click';
        $email->setHeaders($headers);

        // Set subject
        $email->setSubject("Your ASFI Research Journal Co-Author Account");
        
        // HTML content with proper styling
        $htmlContent = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Co-Author Account Created</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .credentials { background-color: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0; }
        .button { background-color: #3498db; color: white; padding: 12px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 10px 0; }
        .password { font-size: 1.1em; font-weight: bold; color: #e74c3c; word-break: break-all; }
        .footer { font-size: 0.8em; color: #7f8c8d; border-top: 1px solid #eee; padding-top: 15px; margin-top: 30px; }
        .warning { color: #e74c3c; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Hi, $prefix $RecipientName</h2>
    </div>
    
    <p>You've been listed as a co-author on a paper submitted to ASFI Research Journal, and an account has been created for you.</p>
    
    <div class="credentials">
        <p><strong>Your login credentials:</strong></p>
        <p><strong>Email:</strong> $RecipientEmail</p>
        <p><strong>Temporary Password:</strong> <span class="password">$password</span></p>
    </div>
    
    <p>Please click below to verify your account and login:</p>
    <a href="$loginUrl" class="button">Verify & Login</a>
    
    <p>After logging in, we strongly recommend you:</p>
    <ol>
        <li>Change your password immediately</li>
        <li>Complete your profile information</li>
    </ol>
    
    <a href="$updateUrl" class="button">Update Account Information</a>
    
    <p class="warning">For security reasons, please change your password immediately after first login.</p>
    
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

        $email->setHtmlContent($htmlContent);

        $response = $apiInstance->sendTransacEmail($email);
        return ['status' => 'success', 'message' => 'Co-author account email sent successfully'];

    } catch (\Brevo\Client\ApiException $e) {
        return ['status' => 'error', 'message' => 'Failed to send email: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}