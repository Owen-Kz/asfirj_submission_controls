<?php

function isAdminAccount($accountID){
    include "../db.php";
    $stmt = $con->prepare("SELECT * FROM `editors` WHERE md5(`id`) = ? OR `email` =? AND (`editorial_level` = 'editor_in_chief' OR `editorial_level` = 'editorial_assistant')");
    if(!$stmt){
        echo json_encode(array("error" => $stmt->error));
        exit;
        // return false;
    }
    $stmt->bind_param("ss", $accountID, $accountID);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        return $row;
    }else{
        return false;
    }
}