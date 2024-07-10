<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";
$userId = $_GET["u_id"];

if(isset($userId)){
    if(isAdminAccount($userId)){
        $stmt = $con->prepare("SELECT COUNT(*) AS `count` FROM `submissions` WHERE `status` != 'saved_for_later' AND `status` != 'revision_saved' AND `status` != 'returned'");
        // $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row["count"];
        $response = array("success"=>"CountSuccess", "count"=> $count);
        echo json_encode($response);
    }else{
        $stmt = $con->prepare("SELECT COUNT(*) AS `count` FROM `submitted_for_edit` WHERE md5(`editor_email`) = ? AND `status` = 'edit_invitation_accepted'");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row["count"];
        $response = array("success"=>"CountSuccess", "count"=> $count);
        echo json_encode($response);
    }
   

}else{
    echo json_encode(array("error"=>"Invalid Parameters"));
}