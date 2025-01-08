<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

$data = json_decode(file_get_contents("php://input"), true);
$adminId = $data["admin_id"];
$revisionID = $data["revision_id"];
$mainId = $revisionID;

if (($pos = strpos($revisionID, '.R')) !== false) {
    $revisionID = substr($revisionID, 0, $pos);
}

if(isset($adminId)){
    $isAdminAccount = isAdminAccount($adminId);
    if($isAdminAccount){
        $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `status` != 'saved_for_later' AND `status` != 'revision_saved' AND 'status' != 'returned' AND `article_id` = ? AND `revision_id` != ? ORDER BY `id` DESC");
        if(!$stmt){
        echo json_encode(array("error" => $stmt->error));
        }
        $stmt->bind_param("ss", $revisionID, $mainId);
        $stmt->execute();
        $result = $stmt->get_result();
        $submissions = array();

        while($row = $result->fetch_assoc()){
            $submissions[] = $row;
        }
    echo json_encode(array("success" => "Admin Account", "submissions" => $submissions));

    }else{
        // Check if user has been invited for any submission 
        $stmt = $con->prepare("SELECT * FROM `submitted_for_edit` WHERE md5(`editor_email`) = ? ORDER BY `id` DESC");
        if(!$stmt){
            echo json_encode(array("error" => $stmt->error));
                }
        $stmt->bind_param("s", $adminId);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            $submissions = array();

            while($row = $result->fetch_assoc()){
                $submissionId = $row["article_id"];
        $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `status` != 'saved_for_later' AND `status` != 'revision_saved' AND `revision_id` = ?");

        if(!$stmt){
    echo json_encode(array("error" => $stmt->error));
        }
        $stmt->bind_param("s", $submissionId);
        $stmt->execute();
        $resultMain = $stmt->get_result();
        $rowMain = $resultMain->fetch_assoc();
     
            $submissions[] = $rowMain;
        
    }
    echo json_encode(array("success" => "Admin Account", "submissions" => $submissions));
}else{
    echo json_encode(array("error" => "No Invites Available"));
            
        }
    }
}else{
    echo json_encode(array("error" => "Invalid Parameters"));
}