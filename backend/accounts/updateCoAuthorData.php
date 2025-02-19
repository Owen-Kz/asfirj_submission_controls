<?php
include "../cors.php";
include "../db.php";
session_start();


$data = json_decode(file_get_contents("php://input"), true);
$password = $data["password"];
$email = $_SESSION["user_id"];
$isAvailableForReview = $data["isAvailableForReview"];

if(isset($data)){
$stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `id` =?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result=$stmt->get_result();
if($result->num_rows > 0){
    $encryptedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $con->prepare("UPDATE `authors_account` SET `is_available_for_review` = ?, `password` =? WHERE `id` =?");
    $stmt->bind_param("sss", $isAvailableForReview, $encryptedPassword, $email);
    if($stmt->execute()){
        $response = array("success"=>"Account Updated Succesfully, Redirecting to login");
        echo json_encode($response);
    }
}else{
    $response = array("error"=>"Account Does not Exist");
    echo json_encode($response);
}
}