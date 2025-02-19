<?php

function isAdministrator($accountID){
    include __DIR__."/../db.php";
    $sessionID = $_SESSION["user_id"];
    $stmt = $con->prepare("SELECT * FROM `editors` WHERE `id` = ? AND (`editorial_level` = 'editor_in_chief' OR `editorial_level` = 'editorial_assistant')");
    if(!$stmt){
        echo json_encode(array("error" => $stmt->error));
        exit;
        // return false;
    }
    $stmt->bind_param("s", $sessionID);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        return $row;
    }else{
        return false;
    }
} 