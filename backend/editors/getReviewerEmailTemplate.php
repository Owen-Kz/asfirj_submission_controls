<?php
include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

$data = json_decode(file_get_contents("php://input"), true);
$user = $data["id"];
$emailfor = $data["emailFor"];

if(isAdminAccount($user)){
    $stmt=$con->prepare("SELECT * FROM `emails_templates` WHERE `invitation_for` = ?");
    $stmt->bind_param("s", $emailfor);
    $stmt->execute();
    $result = $stmt->get_result(); 
    $row = $result->fetch_assoc();
    echo json_encode(array("success"=>"Email Exists", "emailContent" => $row));
}else{
    echo json_encode(array("error" => "Not Chief Editor"));
}