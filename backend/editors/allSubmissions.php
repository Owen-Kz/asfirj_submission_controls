<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

$data = json_decode(file_get_contents("php://input"), true);
$adminId = $data["admin_id"];
if(isset($adminId)){
    $isAdminAccount = isAdminAccount($adminId);
    if($isAdminAccount){
        $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `status` != 'saved_for_later' AND `status` != 'revision_saved' AND `status` != 'returned'");
        if(!$stmt){
    echo json_encode(array("error" => $stmt->error));
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $submissions = array();

        while($row = $result->fetch_assoc()){
            $submissions[] = $row;
        }
    echo json_encode(array("success" => "Admin Account", "submissions" => $submissions));

    }else{
    echo json_encode(array("error" => "Not Admin Account"));
    }
}else{
    echo json_encode(array("error" => "Invalid Parameters"));
}