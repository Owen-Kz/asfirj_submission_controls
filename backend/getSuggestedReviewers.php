<?php
include "./cors.php";
include "./db.php";

if(isset($_GET["a_id"])){
$article_id = $_GET["a_id"];
    $stmt = $con->prepare("SELECT * FROM `suggested_reviewers` WHERE `article_id` = ? AND `email` != ''");
    $stmt->bind_param("s", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $SuggestedReviewers = array();
        while($row = $result->fetch_assoc()){
            $SuggestedReviewers[] = $row;
        }
     $response  = array("success"=>"Keywords Exists", "SuggestedReviewers"=>$SuggestedReviewers);
     echo json_encode($response);
    }
}