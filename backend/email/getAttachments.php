<?php 
include "../cors.php";
include "../db.php";
function getEmailAttachments($emailId, $con)
{
    if (!empty($emailId)) {
        // Prepare the SQL query to retrieve Attachments entries
        $stmt = $con->prepare("SELECT * FROM email_attachments WHERE email_id = ?");
        
        // Bind the email ID parameter
        $stmt->bind_param("i", $emailId);

        // Execute the query
        if ($stmt->execute()) {
            // Fetch the results
            $result = $stmt->get_result();

            $attachmentsEmails = [];

            // Loop through the results and add to the array
            while ($row = $result->fetch_assoc()) {
                $attachmentsEmails[] = $row;
            }

            // Close the statement
            $stmt->close();
 
            // Return the Attachments emails as a JSON response
            header('Content-Type: application/json');
            echo json_encode(["status" => "success", "attachments" => $attachmentsEmails]);
        } else {
            // Handle query execution errors
            header('Content-Type: application/json');
            echo json_encode(["status" => "error", "message" => "Failed to retrieve attachments emails."]);
        }
    } else {
        // Handle empty email ID
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => "Invalid email ID."]);
    }
}


$emailId = $_GET["e"] ?? null;
getEmailAttachments($emailId, $con);
