<?php
include "../cors.php";
include "../db.php";

$data = json_decode(file_get_contents("php://input"), true);

$editorId = $_SESSION["user_email"];

if ($editorId) {
    // Find the Authors for the article 
    $stmt = $con->prepare("SELECT * FROM `editors` WHERE `email` = ?");
    if (!$stmt) {
        echo $stmt->error;
        exit;
    }
    $stmt->bind_param("s", $editorId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $listOfEmails = array();
    
        $stmtKl = $con->prepare("SELECT `email` FROM `editors`  WHERE `email` != ?");
        if (!$stmtKl) {
            echo $stmtKl->error;
            exit;
        }
        $stmtKl->bind_param("s", $editorId);
        $stmtKl->execute();
        $resutlKl = $stmtKl->get_result();
        // $rowKl = $resutlKl->fetch_assoc();
        while ($rowKl = $resutlKl->fetch_assoc()) {
        $listOfEmails[] = $rowKl["email"];
        }

 

        $response = array("success" => "List of Emails", "emails" => $listOfEmails);
        echo json_encode($response);
    } else {
        echo json_encode(array("error" => "Could Not Find Authors"));
    }
} else {
    echo json_encode(array("error" => "Invalid Editor ID"));
}
?>
