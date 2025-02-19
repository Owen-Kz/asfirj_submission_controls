<?php

include "../cors.php";
include "../db.php";
$data = json_decode(file_get_contents("php://input"), true);
$accountID = $_SESSION["user_id"];


if($accountID){
$stmt = $con->prepare("SELECT * FROM `editors` WHERE `id` = ?");
if(!$stmt){
    echo json_encode(array("error" => $stmt->error));
    exit;
}
$stmt->bind_param("s", $accountID);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows > 0){
    $row = $result->fetch_assoc();
    $response = array("success" => "Account Exists", "account" => $row);
    echo json_encode($response);
}else{
    $response = array("error" => "Account Does Not Exist", "account" => []);
    echo json_encode($response);
}
}else{
    $response = array("error" => "Invalid Parameters", "account" => []);
    echo json_encode($response);
}
