<?php 
include "../cors.php";
include "../db.php";
include "../reviewerAccountEmail.php";

$data = json_decode(file_get_contents("php://input"), true);
$editor - $data["editor"];
$article_id = $data["articleId"];
$reviewerEmail = $data["reviewerEmail"];

if(isset($editor)){
    $stmt = $con->prepare("SELECT * FROM `editors` WHERE md5(`id`) = ? AND `editorial_level` = ? OR `editorial_level` = ? OR `editorial_level` =?");

    $editorialLevelOne = "chief_editor";
    $editorialLevelTwo = "associate_editor";
    $editorialLevelThree = "editorial_assistant";

    $stmt->bind_param("ssss", $editor, $editorialLevelOne, $editorialLevelTwo, $editorialLevelThree);
    if(!$stmt){
        $response = array("status"=>"error", "message" => "$stmt->error");
        echo json_encode($response);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_array($result);
        $editor_email = $row["email"];

        $stmt = $con->prepare("UPDATE `submissions` SET `status` = 'submitted_for_review' WHERE `article_id` = ?");        
        $stmt->bind_param("s", $article_id);
        if($stmt->execute()){
        $response = array("status" => "sucess", "message"=>"Review Process Initiated");
        print_r($response);

        // Send the email notification to reviewer
        ReviewerAccountEmail($reviewerEmail);

        // Create the review process entry 
        $stmt = $con->prepare("INSERT INTO `submitted_for_review` (`article_id`, `reviewer_email`, `submitted_by`) VALUES (?,?,?,?)");
        $stmt->bind_param($article_id, $reviewerEmail, $editor_email);
        $stmt->execute();
        }else{
            $response = array("status"=>"error", "message" => $stmt->error);
            print_r($response);
        }
    }else{
        $response = array("status"=>"error", "message" => "Unauthorized account");
        echo json_encode($response);
    }
}