<?php

function SendAcceptReviewEmail($RecipientEmail, $manuscriptId, $ccEmail, $bccEmail) {

    require_once __DIR__ . '/../vendor/autoload.php'; // If you're using Composer (recommended)
    // Import Environment Variables
    include __DIR__ . '/exportENV.php';
    include __DIR__ . '/db.php';

    $apiKey = $_ENV['BREVO_API_KEY'];
    $senderEmail = $_ENV['BREVO_EMAIL'];

    if ($RecipientEmail) {

        $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` = ?");
        $stmt->bind_param("s", $RecipientEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = mysqli_num_rows($result);

        // if user record is available in database then $count will be equal to 1
        if ($count > 0) {
            $row = mysqli_fetch_array($result);
            $email = $row["email"];
            $prefix = $row["prefix"];
            $RecipientName = $row["firstname"];

            $encryptedButton = md5($RecipientEmail);

            $config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
            $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(
                new GuzzleHttp\Client(), $config
            );

            try {
                $currentYear = date("Y");
                $subject = "Invitation to review manuscript for ASFI Research Journal ($manuscriptId)";
                $htmlContent = <<<EOT
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <title>Email Content</title>
                </head>
                <body>
                    <p>[Manuscript title] “ASFI Research Journal of the African Science Frontiers Initiatives (Manuscript ID)”</p>
                    <p>[Date of sending invite]</p>
                    <p>[Name of Invited Reviewer],</p>
                    <p>Thank you for accepting to review this paper. The manuscript files are now available for you on your ASFIRJ Reviewer Dashboard, which you can access using the link: <a href="[Link to Reviewers Dashboard]">Reviewers Dashboard</a>.</p>
                    <p>On the Reviewer Scoring Sheet, you will provide your reviewer report, evaluation, scoring, and rating of the different aspects of the manuscript. For detailed instructions to reviewers reviewing manuscripts for ASFIRJ, please visit this link: <a href="https://asfirj.org/reviewers.html">https://asfirj.org/reviewers.html</a>.</p>
                    <p>We will appreciate it if you can return your reviewer report and scoring sheet on or before [Date of Return the Review Report, which should be 14 days from the day of acceptance of review invite].</p>
                    <br/>
                    <p>Sincerely,</p>
                    <p>[Title and Name of Editor]<br>Editor</p>
                    <p><a href="mailto:submissions@asfirj.org">submissions@asfirj.org</a><br>ASFI Research Journal<br>Excellence. Quality. Impact<br>"Raising the bar of scientific publishing in Africa"</p>
                    <p><a href="https://asfirj.org/">https://asfirj.org/</a><br><a href="mailto:asfirj@asfirj.org">asfirj@asfirj.org</a></p>
                    <p>LinkedIn: <a href="https://www.linkedin.com/in/asfi-research-journal-1b9929309">www.linkedin.com/in/asfi-research-journal-1b9929309</a><br>
                    X (formerly Twitter): <a href="https://twitter.com/asfirj1">https://twitter.com/asfirj1</a><br>
                    Instagram: <a href="https://www.instagram.com/asfirj1/">https://www.instagram.com/asfirj1/</a><br>
                    WhatsApp: <a href="https://chat.whatsapp.com/L8o0N0pUieOGIUHJ1hjSG3">https://chat.whatsapp.com/L8o0N0pUieOGIUHJ1hjSG3</a></p>
                </body>
                </html>
                EOT;

      $email = new \Brevo\Client\Model\SendSmtpEmail();
            $sender = new \Brevo\Client\Model\SendSmtpEmailSender();
            $sender->setEmail($senderEmail);
            $sender->setName("ASFIRJ");

            $recipient = new \Brevo\Client\Model\SendSmtpEmailTo();
            $recipient->setEmail($RecipientEmail);
            $recipient->setName($RecipientName);

            $email->setSender($sender);
            $email->setTo([$recipient]);
            $email->setSubject($subject);
            $email->setHtmlContent($emailContent);
              // Set CC recipients if provided
  if (!empty($ccEmails)) {
    $ccRecipients = [];
    foreach ($ccEmails as $ccEmail) {
        $ccRecipient = new \Brevo\Client\Model\SendSmtpEmailCc();
        $ccRecipient->setEmail($ccEmail);
        $ccRecipients[] = $ccRecipient;
    }
    $email->setCc($ccRecipients);
}

// Set BCC recipients if provided
if (!empty($bccEmails)) {
    $bccRecipients = [];
    foreach ($bccEmails as $bccEmail) {
        $bccRecipient = new \Brevo\Client\Model\SendSmtpEmailBcc();
        $bccRecipient->setEmail($bccEmail);
        $bccRecipients[] = $bccRecipient;
    }
    $email->setBcc($bccRecipients);
}
            
                $result = $apiInstance->sendTransacEmail($email);

                // Update database status
                $stmt = $con->prepare("UPDATE `sent_emails` SET `status` = 'Delivered' WHERE `article_id` = ? AND `sender` = ? AND `subject` = ?");
                $stmt->bind_param("sss", $manuscriptId, $email['sender']['email'], $subject);
                $stmt->execute();

                return true;
            } catch (\Brevo\Client\ApiException $e) {
                $response = array('status' => 'Internal Error', 'message' => 'Caught exception: ' . $e->getMessage() . "\n");
                return false;
            }
        } else {
            $response = array('status' => 'error', 'message' => 'User does not exist on Our servers');
            return false;
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Invalid Request');
        return false;
    }
}
?>
