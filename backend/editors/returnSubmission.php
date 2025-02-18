<?php 
include "../cors.php";
include "../db.php";
$data = json_decode(file_get_contents("php://input"), true);
$editor - $_SESSION["user_email"];
$article_id = $data["articleId"];

if(isset($editor)){
    $stmt = $con->prepare("SELECT * FROM `editors` WHERE `email` = ? AND `editorial_level` = ? OR `editorial_level` = ? OR `editorial_level` =?");

    $editorialLevelOne = "chief_editor";
    $editorialLevelTwo = "associate_editor";
    $editorialLevelThree = "editorial_assitant";

    $stmt->bind_param("ssss", $editor, $editorialLevelOne, $editorialLevelTwo, $editorialLevelThree);
    if(!$stmt){
        $response = array("status"=>"error", "message" => "$stmt->error");
        echo json_encode($response);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if(mysqli_num_rows($result) > 0){
        $stmt = $con->prepare("UPDATE `submissions` SET `status` = 'returned' WHERE `revision_id` = ?");        
        $stmt->bind_param("s", $article_id);
        if($stmt->execute()){
        $response = array("status" => "sucess", "message"=>"Article has been accepted");
        echo json_encode($response);
        }else{
            $response = array("status"=>"error", "message" => $stmt->error);
            echo json_encode($response);
        }
    }else{
        $response = array("status"=>"error", "message" => "Unauthorized account");
        echo json_encode($response);
    }
}