<?php 
include "../cors.php";
include "../db.php";
require_once "../sendBulkEmail.php";

// $_POST = json_decode(file_get_contents("php://input"), true);
$editor = $_POST["editor"];
$article_id = "Bulk Email";
$subject = $_POST["subject"];
$message = $_POST["message"];

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
        $newLetterSubscribers  = "New Letter Subscribers";

        // Save the Email TO The database 
        $stmt = $con->prepare("INSERT INTO `sent_emails` (`article_id`,`subject`, `recipient`,`sender`, `body`) VALUES (?,?,?,?,?)");
        $stmt->bind_param('sssss',$article_id, $subject, $newLetterSubscribers, $editor_email, $message);
        $stmt->execute();

        // Find all the new letter subscribers 
        $stmt=$con->prepare("SELECT * FROM `news_letter_subscribers` WHERE 1");
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $recipientEmail = $row["email"];
                SendBulkEmail($recipientEmail, $subject, $message, $editor_email, $article_id);
            }

        }
     
        // Send the email response
     

            $response = array("status"=>"success", "message" => "Email Has been Sent");
            echo json_encode($response);
        // }
        
    

    }else{
        $response = array("status"=>"error", "message" => "Unauthorized account");
        echo json_encode($response);
    }
}