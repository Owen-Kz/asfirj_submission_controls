<?php 
include "../cors.php";
include "../db.php";
function getEmailBCC($emailId, $con)
{
    if (!empty($emailId)) {
        // Prepare the SQL query to retrieve BCC entries
        $stmt = $con->prepare("SELECT bcc_email FROM email_bcc WHERE email_id = ?");
        
        // Bind the email ID parameter
        $stmt->bind_param("i", $emailId);

        // Execute the query
        if ($stmt->execute()) {
            // Fetch the results
            $result = $stmt->get_result();

            $bccEmails = [];

            // Loop through the results and add to the array
            while ($row = $result->fetch_assoc()) {
                $bccEmails[] = $row['bcc_email'];
            }

            // Close the statement
            $stmt->close();

            // Return the BCC emails as a JSON response
            header('Content-Type: application/json');
            echo json_encode(["status" => "success", "bcc" => $bccEmails]);
        } else {
            // Handle query execution errors
            header('Content-Type: application/json');
            echo json_encode(["status" => "error", "message" => "Failed to retrieve BCC emails."]);
        }
    } else {
        // Handle empty email ID
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => "Invalid email ID."]);
    }
}


$emailId = $_GET["e"] ?? null;
getEmailBCC($emailId, $con);
