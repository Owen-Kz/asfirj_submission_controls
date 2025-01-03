<?php 
include "../cors.php";
include "../db.php";
include "../reviewerAccountEmail.php";

// $_POST = json_decode(file_get_contents("php://input"), true);
$editor = $_POST["editor"];
$article_id = $_POST["articleId"];
$reviewerEmail = $_POST["reviewerEmail"];
$subject = $_POST["subject"];
$message = $_POST["message"];
    // Convert comma-separated CC and BCC to arrays
    $ccEmails = isset($_POST['ccEmail']) ? explode(',', $_POST['ccEmail']) : [];
    $bccEmails = isset($_POST['bccEmail']) ? explode(',', $_POST['bccEmail']) : [];


if(isset($editor)){
    $stmt = $con->prepare("SELECT * FROM `editors` WHERE md5(`email`) = ? AND (`editorial_level` = ? OR `editorial_level` = ? OR `editorial_level` =?)");

    $editorialLevelOne = "editor_in_chief";
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

        $stmt = $con->prepare("UPDATE `submissions` SET `status` = 'rejected' WHERE `revision_id` = ?");        
        $stmt->bind_param("s", $article_id);
        if($stmt->execute()){
        $response = array("status" => "success", "message"=>"Review Process Initiated");
        // print_r($response);

        // Save the Email TO The database 
        $stmt = $con->prepare("INSERT INTO `sent_emails` (`article_id`,`subject`, `recipient`,`sender`, `body`) VALUES (?,?,?,?,?)");
        $stmt->bind_param('sssss',$article_id, $subject, $reviewerEmail, $editor_email, $message);
        $stmt->execute();

        // Send the email notification to reviewer
       if(ReviewerAccountEmail($reviewerEmail, $subject, $message, $editor_email, $article_id, $ccEmails, $bccEmails)){

        // Create the review process entry 
        // $stmt = $con->prepare("INSERT INTO `submitted_for_review` (`article_id`, `reviewer_email`, `submitted_by`) VALUES (?,?,?)");
        // $stmt->bind_param("sss", $article_id, $reviewerEmail, $editor_email);
        // if($stmt->execute()){
            $currentTime = time();
            $oneWeekLater = strtotime('+1 week', $currentTime);

            // $expiryDate = date('Y-m-d H:i:s', $oneWeekLater);
            $expiryDate = date('Y-m-d', $oneWeekLater);
           
            // Add teh Entry to the invitations list 
            // $invitedFor = "Submission Review";
            // $stmt = $con->prepare("INSERT INTO `invitations`(`invited_user`, `invitation_link`, `invitation_expiry_date`, `invited_for`, `invited_user_name`) VALUES (?,?,?,?,?)");
            // $stmt->bind_param("sssss", $reviewerEmail, $article_id, $expiryDate, $invitedFor, $editor_email);
            // $stmt->execute();
            $response = array("status"=>"success", "message" => "Email Has been Sent");
            echo json_encode($response);
        // }
        
        }else{
            $response = array("status"=>"error", "message" => $stmt->error);
            print_r($response);
        }
    }else{
        $response = array("status"=>"error", "message" => "Could Not Sent Mail");
        echo json_encode($response);
    }
    }else{
        $response = array("status"=>"error", "message" => "Unauthorized account");
        echo json_encode($response);
    }
}