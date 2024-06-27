<?php

include "../cors.php";
include "../db.php";

// $data = json_decode(file_get_contents("php://input"), true);

// $email = $data["email"];
$email = $_GET["e"];


$stmt = $con->prepare("SELECT * FROM `authors_account` WHERE md5(`email`) = ?");
if(!$stmt){
    echo $stmt->error;
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if(mysqli_num_rows($result) > 0){
    $row = mysqli_fetch_array($result);
    $account_status = $row["account_status"];
    if($account_status == "verified"){
        $response = array("status" => "accountVerified", "message" => "Account Already verified, Redirect to Login");
        echo json_encode($response);
    }
    else{
        $stmt = $con->prepare("UPDATE `authors_account` SET `account_status` = 'verified' WHERE md5(`email`) = ?");
        if(!$stmt){
            echo $stmt->error;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $response = array("status" => "success", "message" => "Account Verified Succesfully");
        echo json_encode($response);
    }
}else{
    $response = array("status" => "error", "message" => "User does not exist");
        echo json_encode($response);
}