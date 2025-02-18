<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

$data = json_decode(file_get_contents("php://input"), true);

$authorEmail = $data["id"];
$admin = $_SESSION["user_id"];

if(isAdminAccount($admin) && $authorEmail != ''){
    // Find profile Details 
    $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` =  ?");
    $stmt->bind_param("s", $authorEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        if($row["account_status"] !== "verified"){
            $response = array("error"=>"Account is not Verified.");
            echo json_encode($response);
            exit(0);
        }
        $password = $row["password"];
        $email = $row["email"];
        $fullname = $row["prefix"]. " " .$row["firstname"]. " ". $row["lastname"]." ".$row["othername"];
        $editorial_level = "sectional_editor";

        $stmt = $con->prepare("SELECT * FROM `editors` WHERE `email` =?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            $response = array("error" => "This User is already an Editor");
        }else{
            $stmt = $con->prepare("INSERT INTO `editors`(`email`,`fullname`, `password`, `editorial_level`) VALUES(?,?,?,?)");
            if(!$stmt){
             $response = array("error"=>"Could Not prepare statement $stmt->error");
             exit ;
            }
            $stmt->bind_param("ssss", $email, $fullname, $password, $editorial_level);
            if($stmt->execute()){
                $yes = "yes";
                
                $stmt = $con->prepare("UPDATE `authors_account` SET `is_editor` = ?, `is_available_for_review` =?, `is_reviewer` = ? WHERE `email` = ?");
                if(!$stmt){
                    $response = array("error"=>"Could Not prepare statement $stmt->error");
                    exit ;
                   }
                $stmt->bind_param("ssss", $yes, $yes, $yes, $email);
                if($stmt->execute()){
             $response = array("success"=>"Account Migration Successful");
                }else{
             $response = array("error"=>"Could Not Update Account");

                }
                
            }else{
             $response = array("error"=>"Could Not Create editor");

            }
        }
    }else{
        $response = array("error"=>"Account Does Not Exist");

    }
}else{
    $response = array("error"=>"Could Not Migrate Account, You are not an Admin $data");
}

echo json_encode($response);
