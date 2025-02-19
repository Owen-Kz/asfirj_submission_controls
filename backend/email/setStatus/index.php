<?php
include "../../cors.php";
include "../../db.php";

$data  = json_decode(file_get_contents("php://input"), true);

$emailID = $_GET["e_id"];
if(isset($emailID)){
    $stmt = $con->prepare("SELECT * FROM `sent_emails` WHERE `id` = ?");
    if(!$stmt){
        echo json_encode(array("message"=>$stmt->error));
    }
    $stmt->bind_param("s", $emailID);
    $stmt->execute();
    $result=$stmt->get_result();
    if($result->num_rows > 0){
        if(!$stmt){
            echo json_encode(array("message"=>$stmt->error));
        }
        $stmt = $con->prepare("UPDATE `sent_emails` SET `status` = 'Read' WHERE `id`=?");
        $stmt->bind_param("s", $emailID);
        if($stmt->execute()){
            echo json_encode(array("success"=>"Email Read Succesfuly"));
        }

    }else{   echo json_encode(array("error"=>"No Email Available"));
    }
    
}else{
    echo json_encode(array("error"=>"Invalid Parameters"));
} 