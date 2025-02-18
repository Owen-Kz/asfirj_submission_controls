<?php
include "../cors.php";
include "../db.php";

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"];
$resetToken = $data["token"];

if($email && $resetToken){
    $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE md5(`email`) = ? AND `resetToken` = ?");
    $stmt->bind_param("ss", $email, $resetToken);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        echo json_encode(array("success"=>"Token Accepted", "resetEmail" => $email, "resetTokenVerify" => md5($resetToken)));
    }else{
        echo json_encode(array("error"=>"Invalid Token Provided"));
    }
}else{
    echo json_encode(array("error"=>"InvalidParameters"));
}