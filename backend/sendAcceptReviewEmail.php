<?php

function SendAcceptReviewEmail($RecipientEmail, $manuscriptId, $ccEmails, $bccEmails) {
    require_once __DIR__ . '/../vendor/autoload.php';
    include __DIR__ . '/exportENV.php';
    include __DIR__ . '/db.php';

    $apiKey = $_ENV['BREVO_API_KEY'];
    $senderEmail = $_ENV['BREVO_EMAIL'];
    $currentYear = date("Y");

    if (!filter_var($RecipientEmail, FILTER_VALIDATE_EMAIL)) {
        return ['status' => 'error', 'message' => 'Invalid recipient email format'];
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
            return ['status' => 'error', 'message' => 'Reviewer not found in our records'];
        }

        $row = $result->fetch_assoc();
        $prefix = htmlspecialchars($row["prefix"]);
        $RecipientName = htmlspecialchars($row["firstname"]);
        $reviewDueDate = date('F j, Y', strtotime('+14 days'));

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
        $email->setTo([['email' => $RecipientEmail, 'name' => "$prefix $RecipientName"]]);
        
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

        // Set subject
        $email->setSubject("Invitation to Review Manuscript (ID: $manuscriptId) - ASFI Research Journal");
        
        // Create dashboard link
        $dashboardLink = "https://asfirj.org/portal/reviewer/dashboard?mid=$manuscriptId";

        // HTML content with proper styling
        $htmlContent = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Invitation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .button { background-color: #3498db; color: white; padding: 12px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 15px 0; }
        .footer { font-size: 0.8em; color: #7f8c8d; border-top: 1px solid #eee; padding-top: 15px; margin-top: 30px; }
        .highlight { background-color: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>ASFI Research Journal</h2>
        <p>Manuscript ID: $manuscriptId</p>
        <p>Date: {$currentYear}</p>
    </div>
    
    <p>Dear $prefix $RecipientName,</p>
    
    <div class="highlight">
        <p>Thank you for accepting to review this manuscript for ASFI Research Journal.</p>
    </div>
    
    <p>The manuscript files are now available in your reviewer dashboard:</p>
    
    <a href="$dashboardLink" class="button">Access Reviewer Dashboard</a>
    
    <p>On the Reviewer Scoring Sheet, you will provide your evaluation, scoring, and rating of the different aspects of the manuscript.</p>
    
    <p>For detailed instructions, please visit: <a href="https://asfirj.org/reviewers.html">Reviewer Guidelines</a></p>
    
    <p><strong>Please submit your review by: $reviewDueDate</strong></p>
    
    <div class="footer">
        <p>Sincerely,</p>
        <p>The Editorial Team<br>
        <a href="mailto:submissions@asfirj.org">submissions@asfirj.org</a></p>
        
        <p>ASFI Research Journal<br>
        Excellence. Quality. Impact<br>
        "Raising the bar of scientific publishing in Africa"</p>
        
        <p style="margin-top: 20px;">
            <a href="https://asfirj.org/">Website</a> | 
            <a href="https://asfirj.org/contact">Contact Us</a>
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
        $stmt->bind_param("sss", $manuscriptId, $senderEmail, $subject);
        $stmt->execute();

        return ['status' => 'success', 'message' => 'Review acceptance email sent successfully'];

    } catch (\Brevo\Client\ApiException $e) {
        error_log("Brevo API Exception: " . $e->getMessage());
        
        // Update database with failure status
        $stmt = $con->prepare("UPDATE `sent_emails` SET `status` = 'Failed', `error_message` = ? WHERE `article_id` = ? AND `sender` = ? AND `subject` = ?");
        if ($stmt) {
            $errorMsg = substr($e->getMessage(), 0, 255);
            $stmt->bind_param("ssss", $errorMsg, $manuscriptId, $senderEmail, $subject);
            $stmt->execute();
        }
        
        return ['status' => 'error', 'message' => 'Failed to send email: ' . $e->getMessage()];
    } catch (Exception $e) {
        error_log("General Exception: " . $e->getMessage());
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}