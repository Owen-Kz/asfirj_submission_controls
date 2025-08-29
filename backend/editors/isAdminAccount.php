<?php

function isAdminAccount($accountID){
    include "../db.php";
    $userID = $accountID;
    echo $userID;
    
    $stmt = $con->prepare("SELECT * FROM `editors` WHERE `id` = ? AND (`editorial_level` = 'editor_in_chief' OR `editorial_level` = 'editorial_assistant')");
    if(!$stmt){
        echo json_encode(array("error" => $stmt->error));
        exit;
        // return false;
    }
    $stmt->bind_param("s", $accountID);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        return $row;
    }else{
        return false;
    }
}