<?php

include "../cors.php";
include "../db.php";

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"];
$resetToken = $data["resetToken"];
$newPassword = $data["password"];

if($data){
    $encryptedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE md5(`id`) =? AND md5(`resetToken`) =?");
    $stmt->bind_param("ss", $email, $resetToken);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        // Check if the author is an editor 
        $row = $result->fetch_assoc();
        $is_editor = $row["is_editor"];
        // Updaet the Authors Password
        $stmt = $con->prepare("UPDATE `authors_account` SET `password` = ? WHERE md5(`id`) =?");
        $stmt->bind_param("ss", $encryptedPassword, $email);
        $stmt->execute();

        // Update the Editors Password if the person is an editor 
        if($is_editor === "yes"){
            $stmt = $con->prepare("UPDATE `editors` SET `password` =? WHERE md5(`id`) =?");
            $stmt->bind_param("ss", $encryptedPassword, $email);
            // $stmt->execute()
            if($stmt->execute()){
                // print("Editor Password Reset")
            }else{
                // print("Could Not Reset Editor Password")
            }
        }
        echo json_encode(array("success" => "Password has been reset succesfully Please proceed to Login"));
    }else{
        echo json_encode(array("error"=>"Data Does not Exist"));
    }
}else{
    echo json_encode(array("error"=>"InvalidParameters"));
}