<?php

function SendEmailToHandler($RecipientEmail, $manuscriptTitle, $manuscriptId) {
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
        $email->setTo([['email' => $RecipientEmail, 'name' => 'Editorial Office']]);
        
        // Set unsubscribe headers (still important for internal notifications)
        $headers = new \stdClass();
        $headers->{'List-Unsubscribe'} = '<https://asfirj.org/unsubscribe?email='.urlencode($RecipientEmail).'>';
        $headers->{'List-Unsubscribe-Post'} = 'List-Unsubscribe=One-Click';
        $email->setHeaders($headers);

        // Set subject
        $cleanTitle = htmlspecialchars($manuscriptTitle);
        $email->setSubject("New Submission: $cleanTitle (ID: $manuscriptId)");
        
        // Format date nicely
        $date = date('F j, Y');
        
        // Create admin link
        $adminLink = "https://asfirj.org/portal/admin/submissions/view/$manuscriptId";

        // HTML content with proper styling
        $htmlContent = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Submission Notification - {$cleanTitle}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .submission-info { background-color: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0; }
        .button { background-color: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .footer { font-size: 0.8em; color: #7f8c8d; border-top: 1px solid #eee; padding-top: 15px; margin-top: 30px; }
        .manuscript-id { font-weight: bold; color: #e74c3c; }
    </style>
</head>
<body>
    <div class="header">
        <h2>New Manuscript Submission</h2>
        <p>{$date}</p>
    </div>
    
    <p>Dear Editorial Team,</p>
    
    <div class="submission-info">
        <p><strong>Title:</strong> {$cleanTitle}</p>
        <p><strong>Manuscript ID:</strong> <span class="manuscript-id">{$manuscriptId}</span></p>
    </div>
    
    <p>A new submission has been received and requires your attention.</p>
    
    <p style="margin: 20px 0;">
        <a href="{$adminLink}" class="button">View Submission in Admin Panel</a>
    </p>
    
    <p>Please process this submission according to the journal's editorial workflow.</p>
    
    <div class="footer">
        <p><strong>ASFIRJ Editorial Office</strong><br>
        <a href="mailto:submissions@asfirj.org">submissions@asfirj.org</a></p>
        
        <p>ASFI Research Journal<br>
        Excellence. Quality. Impact<br>
        "Raising the bar of scientific publishing in Africa"</p>
        
        <p style="margin-top: 20px;">
            <a href="https://asfirj.org/">Website</a> | 
            <a href="https://asfirj.org/contact">Contact Us</a>
        </p>
        
        <div style="font-size: 0.7em; margin-top: 15px;">
            <p>Connect with us:</p>
            <a href="https://www.linkedin.com/in/asfi-research-journal-1b9929309">LinkedIn</a> | 
            <a href="https://twitter.com/asfirj1">Twitter</a> | 
            <a href="https://www.instagram.com/asfirj1/">Instagram</a> | 
            <a href="https://chat.whatsapp.com/L8o0N0pUieOGIUHJ1hjSG3">WhatsApp</a>
        </div>
    </div>
</body>
</html>
EOT;

        $email->setHtmlContent($htmlContent);

        $response = $apiInstance->sendTransacEmail($email);
        return ['status' => 'success', 'message' => 'Notification email sent successfully'];

    } catch (\Brevo\Client\ApiException $e) {
        return ['status' => 'error', 'message' => 'Failed to send email: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}