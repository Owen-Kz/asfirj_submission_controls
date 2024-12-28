<?php

function SendNewSubmissionEmail($RecipientEmail, $manuscriptTitle, $manuscriptId) {
    require_once __DIR__ . '/../vendor/autoload.php';

    // Import Environment Variables
    include __DIR__ . '/exportENV.php';
    include __DIR__ . '/db.php';

    $apiKey = $_ENV['BREVO_API_KEY'];
    $senderEmail = $_ENV["BREVO_EMAIL"];

    if ($RecipientEmail) {
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
            
            // Set recipient
            $email->setTo([['email' => $RecipientEmail, 'name' => $RecipientName]]);
            $email->setSubject(" $manuscriptTitle ($manuscriptId)");
            $date = date('D-M-Y');
            $htmlContent = <<<EOT
                      <!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>submission Confirmation - $manuscriptTitle</title>
                        </head>
                        <body>
                            <p>$date</p>

                                        <p>Dear $prefix $RecipientName,</p>

                                        <p>Your manuscript referenced above has been successfully submitted online and is presently being given full consideration for publication in ASFI Research Journal (ASFIRJ).</p>

                                        <p>Your paper will now be checked by the Editorial Office to ensure it is ready to go to an Editor. If there are any corrections required, your manuscript will be returned to you and you will receive instructions on what changes to make.</p>

                <p>If there are no changes required, your manuscript will be assigned to an Editor for initial assessment. If your submission passes these stages, it will be sent for external peer review.</p>

                <p>Your manuscript ID is <strong>[$manuscriptId]</strong>.</p>

                <p>Please mention the above manuscript ID in all future correspondence with the journal. You can view the status of your manuscript at any time by logging into the submission site at <a href="https://asfirj.org/portal/login/">https://asfirj.org/portal/login/</a>.</p>

                <p>It is the policy of the journal to correspond exclusively with one designated corresponding author. It is the responsibility of the corresponding author to communicate all correspondences from the journal with the co-authors.</p>

                <p><strong>Co-authors</strong> should contact the Editorial Office as soon as possible if they disagree with being listed as co-authors in submitted manuscript. Otherwise, no further action is required on their part.</p>

                <p><em>ASFIRJ</em> is the official journal of the African Science Frontiers Initiatives (ASFI). It is a peer-reviewed international, open access, multidisciplinary journal, publishing original papers, expert reviews, systematic reviews and meta-analyses, position papers, guidelines, protocols, data, editorials, news and commentaries, research letters from any research field.</p>

                <p>Thank you for submitting your manuscript to ASFIRJ.</p>

                <p>Sincerely,</p>

                <p>ASFIRJ Editorial Office<br>
                <a href="mailto:submissions@asfirj.org">submissions@asfirj.org</a></p>

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
        $response = array('status' => 'error', 'message' => 'Invalid Request');
    }

    // print_r($response);
}
