<?php
include "../cors.php";
include "../db.php";
include "../sendPasswordREsetEmail.php";

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"];
function generateRandom6DigitNumber() {
    return mt_rand(100000, 999999);
}

// Usage
if($email){
    $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $id = $row["id"];
   
        $resetToken = generateRandom6DigitNumber();
        // Update the REset Token for Author Account
        $stmt = $con->prepare("UPDATE `authors_account`SET `resetToken` = ?");
        $stmt->bind_param("s", $resetToken);
        if($stmt->execute()){
            SendPasswordResetEmail($email, $resetToken);
            echo json_encode(array("success" => "passwordResetEmailSent", "userEmail" => md5($id)));
        }

    }else{
        echo json_encode(array("error"=>"userNotFound"));
    }
}
