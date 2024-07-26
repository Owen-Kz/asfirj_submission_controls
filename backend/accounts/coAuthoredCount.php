<?php

include "../cors.php";
include "../db.php";

$userId = $_GET["u_id"];

if(isset($userId)){
    $stmt = $con->prepare("SELECT * FROM `submission_authors` WHERE md5(`authors_email`) = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $listOfManuscripts = array();
    if($result->num_rows > 0){
        // $listOfManuscripts = [];  // Initialize the array

        while ($row = $result->fetch_assoc()) {
            $articleId = $row["submission_id"];
            $stmt = $con->prepare("SELECT * FROM `submissions` WHERE md5(`corresponding_authors_email`) != ? AND `article_id` = ?");
            
            if (!$stmt) {
                $response = array("error" => $con->error, "articles" => []);
                echo json_encode($response);
                exit;  // Exit the script since there's an error in preparing the statement
            }
        
            $stmt->bind_param("ss", $userId, $articleId);
            
            if (!$stmt->execute()) {
                $response = array("error" => $stmt->error, "articles" => []);
                echo json_encode($response);
                exit;  // Exit the script since there's an error in executing the statement
            }
        
            $resultK = $stmt->get_result();
        
            if ($resultK) {
                while ($rowK = $resultK->fetch_assoc()) {
                    $listOfManuscripts[] = $rowK;  // Fetch associative array
                }
            } else {
                // If there are no results, continue the loop
                continue;
            }
        }
    }
    // $count = $row["count"];

    // $stmt = $con->prepare("SELECT COUNT(*) AS `count` FROM `submission_authors` WHERE md5(`authors_email`) = ?");
    // $stmt->bind_param("s", $userId);
    // $stmt->execute();
    // $result = $stmt->get_result();
    // $row = $result->fetch_assoc();
    // $count = $row["count"];

    $response = array("success"=>"CountSuccess", "count"=> count($listOfManuscripts));
    echo json_encode($response);

}else{
    echo json_encode(array("error"=>"Invalid Parameters"));
}