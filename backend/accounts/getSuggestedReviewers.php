<?php
include "../cors.php";
include "../db.php";
session_start();

$data = json_decode(file_get_contents("php://input"), true);

if($data){
    $article_id = $data["article_id"];
    $stmt=$con->prepare("SELECT * FROM `suggested_reviewers` WHERE `article_id` = ?");
    $stmt->bind_param("s", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows >0){
        $suggestedReviewers = array();
        while($row=$result->fetch_assoc()){
            $suggestedReviewers[] = $row;

        }
        $response = array("success"=>"Reviewers Available", "suggestedReviewers"=>$suggestedReviewers);
        echo json_encode($response);
    }else{
        $response = array("success"=>"NO Reviewers", "suggestedReviewers"=>[]);
        echo json_encode($response);
    }
}