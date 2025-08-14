<?php

function AcceptanceEmailToEditor($RecipientEmail, $subject, $message, $editor_email, $article_id, $ccEmails, $bccEmails) {
    require_once __DIR__ . '/../vendor/autoload.php';
    require __DIR__ . '/../backend/exportENV.php';
    include __DIR__ . '/../backend/db.php';

    $apiKey = $_ENV['BREVO_API_KEY'];
    $senderEmail = $_ENV['BREVO_EMAIL'];
    $currentYear = date('Y');
    $currentDate = date('F j, Y');

    if (!filter_var($RecipientEmail, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid recipient email: $RecipientEmail");
        return ['status' => 'error', 'message' => 'Invalid email format'];
    }

    try {
        $config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(
            new \GuzzleHttp\Client(), 
            $config
        );

        // Create email object
        $email = new \Brevo\Client\Model\SendSmtpEmail();
        
        // Set sender
        $sender = new \Brevo\Client\Model\SendSmtpEmailSender();
        $sender->setEmail($senderEmail);
        $sender->setName('ASFI Research Journal');
        $email->setSender($sender);

        // Set recipient
        $email->setTo([['email' => $RecipientEmail]]);
        
        // Set subject with proper escaping
        $email->setSubject(htmlspecialchars($subject, ENT_QUOTES, 'UTF-8'));
        
        // Set CC recipients if provided
        if (!empty($ccEmails) && is_array($ccEmails)) {
            $ccRecipients = [];
            foreach ($ccEmails as $ccEmail) {
                if (filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
                    $ccRecipients[] = ['email' => $ccEmail];
                }
            }
            if (!empty($ccRecipients)) {
                $email->setCc($ccRecipients);
            }
        }

        // Set BCC recipients if provided
        if (!empty($bccEmails) && is_array($bccEmails)) {
            $bccRecipients = [];
            foreach ($bccEmails as $bccEmail) {
                if (filter_var($bccEmail, FILTER_VALIDATE_EMAIL)) {
                    $bccRecipients[] = ['email' => $bccEmail];
                }
            }
            if (!empty($bccRecipients)) {
                $email->setBcc($bccRecipients);
            }
        }

        // HTML content with proper styling
        $htmlContent = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article Accepted for Publication</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            max-width: 600px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        .header { 
            color: #2c3e50; 
            border-bottom: 1px solid #eee; 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
        }
        .highlight { 
            background-color: #f8f9fa; 
            padding: 15px; 
            border-left: 4px solid #27ae60;
            margin: 20px 0; 
        }
        .footer { 
            font-size: 0.8em; 
            color: #7f8c8d; 
            border-top: 1px solid #eee; 
            padding-top: 15px; 
            margin-top: 30px; 
        }
        .button {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Article Accepted for Publication</h2>
        <p>$currentDate</p>
    </div>
    
    <div class="highlight">
        <p>The article with ID <strong>$article_id</strong> has been accepted by the handling editor and is ready for publication.</p>
    </div>
    
    <p>You can view the article details in the editorial dashboard:</p>
    
    <a href="https://asfirj.org/portal/editor/dashboard?article=$article_id" class="button">View Article in Dashboard</a>
    
    <p>Please proceed with the publication process at your earliest convenience.</p>
    
    <div class="footer">
        <p>ASFI Research Journal &copy; $currentYear</p>
        <p>
            <a href="https://asfirj.org/">Website</a> | 
            <a href="mailto:editorial@asfirj.org">Editorial Office</a>
        </p>
    </div>
</body>
</html>
EOT;

        $email->setHtmlContent($htmlContent);

        // Send email
        $response = $apiInstance->sendTransacEmail($email);

        // Update database status
        $stmt = $con->prepare("UPDATE `sent_emails` SET `status` = 'Delivered', `delivered_at` = NOW() WHERE `article_id` = ? AND `sender` = ? AND `subject` = ?");
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $stmt->error);
        }
        $stmt->bind_param("sss", $article_id, $editor_email, $subject);
        $stmt->execute();

        return ['status' => 'success', 'message' => 'Acceptance email sent successfully'];

    } catch (\Brevo\Client\ApiException $e) {
        error_log("Brevo API Exception: " . $e->getMessage());
        
        // Update database with failure status
        $stmt = $con->prepare("UPDATE `sent_emails` SET `status` = 'Failed', `error_message` = ? WHERE `article_id` = ? AND `sender` = ? AND `subject` = ?");
        if ($stmt) {
            $errorMsg = substr($e->getMessage(), 0, 255);
            $stmt->bind_param("ssss", $errorMsg, $article_id, $editor_email, $subject);
            $stmt->execute();
        }
        
        return ['status' => 'error', 'message' => 'Failed to send email: ' . $e->getMessage()];
    } catch (Exception $e) {
        error_log("General Exception: " . $e->getMessage());
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}