<?php

include "../cors.php";
include "../db.php";

$data = json_decode(file_get_contents("php://input"), true);
$articleId = $data["a"];
$reviewerEmail = $data["r"];


if(isset($articleId)){
    $stmt = $con->prepare("SELECT * FROM `reviews` WHERE `article_id` = ? AND `reviewer_email` = ?");
    $stmt->bind_param("ss", $articleId, $reviewerEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_array($result);
        $response = array("status" => "success", "reviewContent" => $row);
        echo json_encode($response);
    }else{
        $response = array("status" => "error", "reviewContent" => "No related Content");
        echo json_encode($response);
    }
}else{
    $response = array("status" => "error", "reviewContent" => "Parameters are not set");
    echo json_encode($response);
}