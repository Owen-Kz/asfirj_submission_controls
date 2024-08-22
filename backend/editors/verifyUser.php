<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

$data = json_decode(file_get_contents("php://input"), true);

$authorEmail = $data["id"];
$admin = $data["admin"];

if(isAdminAccount($admin) && $authorEmail != ''){
    // Find profile Details 
    $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` =  ?");
    $stmt->bind_param("s", $authorEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        if($row["account_status"] !== "verified"){
            $email = $row["email"];
                $stmt = $con->prepare("UPDATE `authors_account` SET `account_status` ='verified' WHERE `email` = ?");
                if(!$stmt){
                    $response = array("error"=>"Could Not prepare statement $stmt->error");
                    exit ;
                   }
                $stmt->bind_param("s", $email);
                if($stmt->execute()){
             $response = array("success"=>"Account Verification Successful For $authorEmail");
                }else{
             $response = array("error"=>"Could Not Update Account");

                }
    
        }else {
             $response = array("error"=>"Account Already Verified.");
        exit(0);
    }
    }else{
        $response = array("error"=>"Account Does Not Exist");

    }
}else{
    $response = array("error"=>"Could Not Verify Account, You are not an Admin $data");
}

echo json_encode($response);
