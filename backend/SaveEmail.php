<?php

function saveEmailDetails($con, $RecipientEmail, $subject, $message, $senderEmail, $article_id, $ccEmails, $bccEmails, $attachments, $invitedFor) {
    // Save the main email details
    $stmt = $con->prepare("INSERT INTO sent_emails (`recipient`, `subject`, `body`, `sender`, `article_id`, `email_for`) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $RecipientEmail, $subject, $message, $senderEmail, $article_id, $invitedFor);
    $stmt->execute();
    $emailId = $stmt->insert_id; // Get the last inserted ID
    $stmt->close();

    // Save CC emails
    if (!empty($ccEmails)) {
        $stmt = $con->prepare("INSERT INTO email_cc (email_id, cc_email) VALUES (?, ?)");
        foreach ($ccEmails as $ccEmail) {
            $stmt->bind_param("is", $emailId, $ccEmail);
            $stmt->execute();
        }
        $stmt->close();
    }

    // Save BCC emails
    if (!empty($bccEmails)) {
        $stmt = $con->prepare("INSERT INTO email_bcc (email_id, bcc_email) VALUES (?, ?)");
        foreach ($bccEmails as $bccEmail) {
            $stmt->bind_param("is", $emailId, $bccEmail);
            $stmt->execute();
        }
        $stmt->close();
    }

    // Save attachments
    if (!empty($attachments)) {
        $stmt = $con->prepare("INSERT INTO email_attachments (email_id, file_name, file_path) VALUES (?, ?, ?)");
        foreach ($attachments as $attachment) {
            $stmt->bind_param("iss", $emailId, $attachment['name'], $attachment['url']);
            $stmt->execute();
        }
        $stmt->close();
    }
}
