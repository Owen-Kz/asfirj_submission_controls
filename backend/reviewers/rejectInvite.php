<?php

include "../cors.php";
include "../db.php";
include "../sendReviewConfirmationToEditor.php";



$data = json_decode(file_get_contents("php://input"), true);

$revision_id = $data["articleId"];
$reviewerEmail = $data["reviewerEmail"];

// Find Details of the Article 
$stmt = $con->prepare("SELECT * FROM `submitted_for_review` WHERE `article_id` = ? AND `reviewer_email` =? AND `invitation_status` != 'review_submitted'");
$stmt->bind_param("ss", $revision_id, $reviewerEmail);
if($stmt->execute()){
    $result = $stmt->get_result();
    $count = mysqli_num_rows($result);
    if($count > 0){
        $row = mysqli_fetch_array($result);
        $editor_email = $row["submitted_by"];
        $stmt = $con->prepare("UPDATE `submissions` SET `status` = 'review_request_rejected'  WHERE `revision_id` = ?");
        $stmt->bind_param("s",$revision_id);
        if($stmt->execute()){
            $stmt = $con->prepare("UPDATE `submitted_for_review` SET `status` = 'review_request_rejected'  WHERE `article_id` = ? AND `reviewer_email` = ?");
            $stmt->bind_param("ss",$revision_id, $reviewerEmail);
            $stmt->execute();
            ReviewConfirmationEmail($editor_email, $reviewerEmail, "rejected");
            $response = array("status"=>"success", "message"=>"Review Request has been rejected");
            echo json_encode($response);
        }
       
    }else{
        $response = array("status"=>"error", "message"=>"Invalid Action: Invitation accepted or the review has been submitted");
        echo json_encode($response);
    }
}else{
    $response = array("status"=>"error", "message"=>$stmt->error);
    echo json_encode($response);
}