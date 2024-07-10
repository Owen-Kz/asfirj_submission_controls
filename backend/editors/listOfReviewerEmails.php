<?php
include "../cors.php";
include "../db.php";

$data = json_decode(file_get_contents("php://input"), true);

$editorId = $data["editorId"];

if($editorId){
    $stmt = $con->prepare("SELECT * FROM `editors` WHERE md5(`email`) = ?");
    if(!$stmt){
        echo $stmt->error;
    }
    $stmt->bind_param("s", $editorId);
    $stmt->execute();
    $result=$stmt->get_result();

    if($result->num_rows > 0){
        $stmt= $con->prepare("SELECT `email` FROM `authors_account` WHERE `is_available_for_review` = 'yes'");
        $stmt->execute();
        $result = $stmt->get_result();
        $listOfEmails = array();
        while($row = $result->fetch_assoc()){
            $listOfEmails[] = $row;
        }
        $response = array("success" => "List of Emails", "emails" => $listOfEmails);
        echo json_encode($response);
    }
}