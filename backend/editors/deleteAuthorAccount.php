<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

$data = json_decode(file_get_contents("php://input"), true);

$authorEmail = $data["id"];
$admin = $data["admin"];

if(isAdminAccount($admin) && $authorEmail != ''){
    $stmt = $con->prepare("DELETE FROM `authors_account` WHERE `email` = ?");
    $stmt->bind_param("s", $authorEmail);
    $stmt->execute();
    $response = array("success"=>"AccountDeletedSuccesfully");
    echo json_encode($response);
}else{
    $response = array("error"=>"Could Not Delete Account, You are not an Admin $data");
    echo json_encode($response);
}