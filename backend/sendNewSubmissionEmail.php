<?php

function SendNewSubmissionEmail($RecipientEmail, $manuscriptTitle, $manuscriptId) {
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
            return ['status' => 'error', 'message' => 'Author not found in our records'];
        }

        $row = $result->fetch_assoc();
        $prefix = htmlspecialchars($row["prefix"]);
        $RecipientName = htmlspecialchars($row["firstname"]);
        $cleanTitle = htmlspecialchars($manuscriptTitle);
        $date = date('F j, Y');

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
        $email->setSubject("Submission Confirmed: $cleanTitle (ID: $manuscriptId)");

        // HTML content with proper styling
        $htmlContent = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Confirmation - $cleanTitle</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .highlight { background-color: #f8f9fa; padding: 10px; border-left: 4px solid #3498db; margin: 15px 0; }
        .button { background-color: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .footer { font-size: 0.8em; color: #7f8c8d; border-top: 1px solid #eee; padding-top: 15px; margin-top: 30px; }
        .manuscript-id { font-size: 1.2em; font-weight: bold; color: #e74c3c; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Submission Confirmation</h2>
        <p>$date</p>
    </div>
    
    <p>Dear $prefix $RecipientName,</p>
    
    <p>Your manuscript <strong>"$cleanTitle"</strong> has been successfully submitted to ASFI Research Journal (ASFIRJ) and is now under consideration for publication.</p>
    
    <div class="highlight">
        <p>Manuscript ID: <span class="manuscript-id">$manuscriptId</span></p>
        <p>Please reference this ID in all future correspondence.</p>
    </div>
    
    <h3>Next Steps:</h3>
    <ol>
        <li>Editorial Office review for completeness</li>
        <li>Assignment to an Editor for initial assessment</li>
        <li>If suitable, external peer review</li>
    </ol>
    
    <p>You can track your submission status at any time:</p>
    <p style="margin: 20px 0;">
        <a href="https://asfirj.org/portal/login/" class="button">View Submission Status</a>
    </p>
    
    <h3>Important Notes:</h3>
    <ul>
        <li>All correspondence will be with the designated corresponding author</li>
        <li>Co-authors should contact us immediately if they dispute authorship</li>
    </ul>
    
    <p><em>ASFIRJ</em> is the official journal of the African Science Frontiers Initiatives (ASFI), publishing peer-reviewed, open access research across all disciplines.</p>
    
    <div class="footer">
        <p><strong>ASFIRJ Editorial Office</strong><br>
        <a href="mailto:submissions@asfirj.org">submissions@asfirj.org</a></p>
        
        <p>ASFI Research Journal<br>
        Excellence. Quality. Impact<br>
        "Raising the bar of scientific publishing in Africa"</p>
        
        <p style="margin-top: 20px;">
            <a href="https://asfirj.org/">Website</a> | 
            <a href="https://asfirj.org/unsubscribe?email=$RecipientEmail">Unsubscribe</a> | 
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
        return ['status' => 'success', 'message' => 'Submission confirmation email sent successfully'];

    } catch (\Brevo\Client\ApiException $e) {
        return ['status' => 'error', 'message' => 'Email sending failed: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}