<?php

include "../cors.php";
include "../db.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = $data["encrypted"];

$stmt = $con->prepare("SELECT * FROM `submitted_for_review` WHERE md5(`reviewer_email`) = ? AND `status` != 'review_submitted' AND `status` != 'review_completed'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if(mysqli_num_rows($result) > 0){
    $toReviewList = array(); // Initialize an array to store all toReview
    // $ReviewArticleContent = array();

    while ($row = $result->fetch_assoc()) {
        $submissionId = $row["article_id"];
        $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `article_id` =?");
        $stmt->bind_param("s", $submissionId);
        $stmt->execute();
        $result = $stmt->get_result();
        while($subRow = $result->fetch_assoc()){
  // Loop through each row in the result set and append it to the toReviewList array
  $toReviewList[] = $subRow;
        }
      
    }
    $response = array("status" => "success", "submissionsToReview" => $toReviewList);
    echo json_encode($response);
}else{
    $response = array("status" => "success", "submissionsToReview" => []);
    echo json_encode($response);
}