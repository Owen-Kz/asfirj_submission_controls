<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

$data = json_decode(file_get_contents("php://input"), true);
$adminId = $data["admin_id"];
if(isset($adminId)){
    $isAdminAccount = isAdminAccount($adminId);
    if($isAdminAccount){
        $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `status` = 'submitted' OR `status` = 'correction_submitted' OR `status` = 'submitted_for_review' OR `status` = 'review_submitted' OR `status` = 'accepted'  OR `status` = 'revision_submitted' ORDER BY `id` DESC");
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