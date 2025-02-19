<?php 
include "../cors.php";
include "../db.php";
function getEmailCC($emailId, $con)
{
    if (!empty($emailId)) {
        // Prepare the SQL query to retrieve CC entries
        $stmt = $con->prepare("SELECT cc_email FROM email_cc WHERE email_id = ?");
        
        // Bind the email ID parameter
        $stmt->bind_param("i", $emailId);

        // Execute the query
        if ($stmt->execute()) {
            // Fetch the results
            $result = $stmt->get_result();

            $ccEmails = [];

            // Loop through the results and add to the array
            while ($row = $result->fetch_assoc()) {
                $ccEmails[] = $row['cc_email'];
            }
 
            // Close the statement
            $stmt->close();

            // Return the cc emails as a JSON response
            header('Content-Type: application/json');
            echo json_encode(["status" => "success", "cc" => $ccEmails]);
        } else {
            // Handle query execution errors
            header('Content-Type: application/json');
            echo json_encode(["status" => "error", "message" => "Failed to retrieve cc emails."]);
        }
    } else {
        // Handle empty email ID
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => "Invalid email ID."]);
    }
}


$emailId = $_GET["e"] ?? null;
getEmailCC($emailId, $con);
