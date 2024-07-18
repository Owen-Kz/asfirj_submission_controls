<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

// session_start();
if(isset($_GET["encrypted"])){
    // $isAdmin = isAdminAccount($_GET['u_id']);
    // if($isAdmin){

$email = $_GET["encrypted"];

$stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email`= ?");
$stmt->bind_param("s", $email);
if(!$stmt){
    print_r($con->error);
}else{
    $stmt->execute();
    $result = $stmt->get_result();

    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_array($result);
        $response = array("status" => "success", "accountData" => $row);
        echo json_encode($response);
    }else{
        $response = array("status" => "error");
        echo json_encode($response);
    }
}

// }else{
//     echo json_encode(array("status"=>"error", "message"=>'Unathorized Access'));
// }
}else{
    $response = array("InvalidParameters");
    echo json_encode($response);
}