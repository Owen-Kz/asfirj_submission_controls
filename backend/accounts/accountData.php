<?php

include "../cors.php";
include "../db.php";
// session_start();

$data = json_decode(file_get_contents("php://input"), true);



$email = $data["encrypted"];

$stmt = $con->prepare("SELECT * FROM `authors_account` WHERE md5(`id`) = ?");
if(!$stmt){
    print_r($con->error);
}else{
$stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_array($result);
        $response = array("status" => "success", "accountData" => $row);
        echo json_encode($response);
    }else{
        $response = array("status" => "error", "accountData" => "NotFound");
        echo json_encode($response);
    }
}