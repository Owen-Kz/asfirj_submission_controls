<?php

function SendPasswordResetEmail($RecipientEmail, $resetToken) {
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
            return ['status' => 'error', 'message' => 'Email not found in our system'];
        }

        $row = $result->fetch_assoc();
        $prefix = htmlspecialchars($row["prefix"]);
        $RecipientName = htmlspecialchars($row["firstname"]);

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
        $email->setSubject("Password Reset Request - ASFI Research Journal");
        
        // Create reset link with token
        $resetLink = "https://asfirj.org/portal/reset-password?token=" . urlencode($resetToken) . "&email=" . urlencode($RecipientEmail);

        // HTML content with proper styling
        $htmlContent = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .token { 
            background-color: #f8f9fa; 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin: 20px 0; 
            text-align: center; 
            font-size: 1.2em; 
            word-break: break-all;
        }
        .button { 
            background-color: #3498db; 
            color: white; 
            padding: 12px 20px; 
            text-decoration: none; 
            border-radius: 4px; 
            display: inline-block; 
            margin: 15px 0;
        }
        .footer { 
            font-size: 0.8em; 
            color: #7f8c8d; 
            border-top: 1px solid #eee; 
            padding-top: 15px; 
            margin-top: 30px; 
        }
        .warning { 
            color: #e74c3c; 
            font-weight: bold; 
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Hi, $prefix $RecipientName</h2>
    </div>
    
    <p>We received a request to reset your password for ASFI Research Journal.</p>
    
    <p>Please click the button below to reset your password:</p>
    
    <a href="$resetLink" class="button">Reset Password</a>
    
    <p>Or use this verification code:</p>
    <div class="token">$resetToken</div>
    
    <p class="warning">This link will expire in 24 hours. If you didn't request this password reset, please ignore this email.</p>
    
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
        return ['status' => 'success', 'message' => 'Password reset email sent successfully'];

    } catch (\Brevo\Client\ApiException $e) {
        return ['status' => 'error', 'message' => 'Failed to send email: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}