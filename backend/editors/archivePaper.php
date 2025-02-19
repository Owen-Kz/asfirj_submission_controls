<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

$data = json_decode(file_get_contents("php://input"), true);
$submissinoId = $data["submissionId"];

if(isset($submissinoId)){ 
    $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `revision_id` = ?");
    $stmt->bind_param("s", $submissinoId);
    $stmt->execute();
    $result = $stmt->get_result();
if($result->num_rows > 0){
    $mainArticleId = $result->fetch_assoc()["article_id"];
    $stmt = $con->prepare("INSERT INTO archived_submissions
     SELECT * FROM submissions WHERE article_id = ?");
     $stmt->bind_param("s", $mainArticleId);
        if($stmt->execute()){
        $stmt = $con->prepare("DELETE FROM submissions WHERE article_id = ?");
        $stmt->bind_param("s", $mainArticleId);
        $stmt->execute();
        }else{
            echo json_encode(array("error" => "Submission not Archived"));
        }
        echo json_encode(array("success" => "Submission Archived"));
}else{
    echo json_encode(array("error" => "Submission ID not found"));
}

  
} else {
    echo json_encode(array("error" => "Submission ID is not SET"));
}