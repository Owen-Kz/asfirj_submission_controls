<?php 
include "../cors.php";
include "../db.php";
include "../reviewerAccountEmail.php";
include "./uploadAttachments.php";
include "../SaveEmail.php";


// $_POST = json_decode(file_get_contents("php://input"), true);
$editor = $_SESSION["user_email"];
$article_id = $_POST["articleId"];
$reviewerEmail = $_POST["reviewerEmail"];
$subject = $_POST["subject"];
$message = $_POST["message"];
$invitedFor = "Submission Review";

    // Collect file attachments
    $attachments = [];
    if (!empty($_FILES['attachments']['name'][0])) {
        foreach ($_FILES['attachments']['name'] as $key => $fileName) {
            $fileTmpPath = $_FILES['attachments']['tmp_name'][$key];
            $fileSize = $_FILES['attachments']['size'][$key];
            $fileError = $_FILES['attachments']['error'][$key];
    
            if ($fileError === UPLOAD_ERR_OK) {
                // Validate file size and type (optional)
                if ($fileSize > 0) {
                    try {
                        // Upload to Cloudinary and get the URL
                        $cloudinaryUrl = uploadToCloudinary($fileTmpPath, $fileName);
    
                        // Add the attachment details
                        $attachments[] = [
                            'content' => base64_encode(file_get_contents($fileTmpPath)), // This is for email attachment
                            'name' => $fileName,
                            'url' => $cloudinaryUrl, // Add Cloudinary URL for later use
                        ];
                    } catch (Exception $e) {
                        echo "Error uploading file to Cloudinary: " . $e->getMessage();
                    }
                }
            } else {
                echo "Error uploading file: " . $fileName;
            }
        }
    }

    // Convert comma-separated CC and BCC to arrays
    $ccEmails = isset($_POST['ccEmail']) && isset($_POST['ccEmail']) != '' ? explode(',', $_POST['ccEmail']) : [];
    $bccEmails = isset($_POST['bccEmail']) && isset($_POST['bccEmail']) != ''? explode(',', $_POST['bccEmail']) : [];

if(isset($editor)){
    $stmt = $con->prepare("SELECT * FROM `editors` WHERE md5(`id`) = ? AND (`editorial_level` = ? OR `editorial_level` = ? OR `editorial_level` =?)");

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

        // Check if the invited Reviewer is an author on the papaer 
        $stmt = $con->prepare("SELECT * FROM `submission_authors` WHERE `authors_email` = ? AND `submission_id` = ?");
        $stmt->bind_param("ss", $reviewerEmail,$article_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            $response = array("status"=>"error", "message" => "The Reviewer You tried to invite is an author on this article");
        echo json_encode($response);
        exit;
        }
        // check if the review already exists
        $stmt = $con->prepare("SELECT * FROM `submitted_for_review` WHERE `article_id` =? AND `reviewer_email` = ? AND (`status` = 'submitted_for_review' OR `status` = 'review_invitation_accepted' OR `status` = 'review_submitted')");
        $stmt->bind_param("ss",$article_id, $reviewerEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            $response = array("status"=>"success", "message" => "An Invitation was previously sent to $reviewerEmail");
            echo json_encode($response);
        }else{

        $stmt = $con->prepare("UPDATE `submissions` SET `status` = 'submitted_for_review' WHERE `revision_id` = ?");        
        $stmt->bind_param("s", $article_id);
        if($stmt->execute()){
        $response = array("status" => "success", "message"=>"Review Process Initiated");
        // print_r($response);

        // Save the Email TO The database 
        // $stmt = $con->prepare("INSERT INTO `sent_emails` (`article_id`,`subject`, `recipient`,`sender`, `body`) VALUES (?,?,?,?,?)");
        // $stmt->bind_param('sssss',$article_id, $subject, $reviewerEmail, $editor_email, $message);
        // $stmt->execute();
        saveEmailDetails($con, $reviewerEmail, $subject, $message, $editor_email, $article_id, $ccEmails, $bccEmails, $attachments, $invitedFor);
        // Send the email notification to reviewer
       if(ReviewerAccountEmail($reviewerEmail, $subject, $message, $editor_email, $article_id, $ccEmails, $bccEmails,  $attachments)){

        // Create the review process entry 
        $stmt = $con->prepare("INSERT INTO `submitted_for_review` (`article_id`, `reviewer_email`, `submitted_by`) VALUES (?,?,?)");
        $stmt->bind_param("sss", $article_id, $reviewerEmail, $editor_email);
        if($stmt->execute()){
            $currentTime = time();
            $oneWeekLater = strtotime('+1 week', $currentTime);

            // $expiryDate = date('Y-m-d H:i:s', $oneWeekLater);
            $expiryDate = date('Y-m-d', $oneWeekLater);
           
            // Add teh Entry to the invitations list 
            $stmt = $con->prepare("INSERT INTO `invitations`(`invited_user`, `invitation_link`, `invitation_expiry_date`, `invited_for`, `invited_user_name`) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss", $reviewerEmail, $article_id, $expiryDate, $invitedFor, $editor_email);
            $stmt->execute();
            $response = array("status"=>"success", "message" => "Email Has been Sent");
            echo json_encode($response);
        }
    
        
        }else{
            $response = array("status"=>"error", "message" => "Could Not Send Email at the moment. Please Try Again or Check Your Connection");
            echo json_encode($response);
        }
    
    }else{
        $response = array("status"=>"error", "message" => "Could Not Sent Mail");
        echo json_encode($response);
    }
}
    }else{
        $response = array("status"=>"error", "message" => "Unauthorized account");
        echo json_encode($response);
    }
}