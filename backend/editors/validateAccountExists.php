<?php

// This code will check if the user exists and is a reviewer after they click the ink in their email
// if the user does exist and is not a reviewer the code makes them a reviewer else sends a response which would redirect them to create a reviewers account 

include "../cors.php";
include "../db.php";
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$email = $data["encrypted"];

$stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` = ?");
$stmt->bind_param("s", $email);
if(!$stmt){
    print_r($con->error);
}else{
    $stmt->execute();
    $result = $stmt->get_result();
 
    if(mysqli_num_rows($result) > 0){
        $row = $result->fetch_assoc();
        $currentEmail = $row["email"];
        $currenPassword = $row["password"];
        $fullname = $row['prefix']." ".$row["firstname"]." ".$row["othername"]." ".$row["lastname"];
        $discipline = $row["discipline"];
        $editorialLevel = "Sectional Editor";

        // Set the account to a reviewer account 
        $stmt = $con->prepare("UPDATE `authors_account` SET `is_reviewer` = 'yes', `is_available_for_review` = 'yes', `is_editor` = 'yes', `editor_invite_status` = 'invitation_accepted' WHERE `email` = ?");
        $stmt->bind_param("s", $email);
        if($stmt->execute()){
            // Copy the Data to the editors table if not exists
            $stmt = $con->prepare("SELECT * FROM `editors` WHERE `email` = ?");
            $stmt->bind_param("s", $currentEmail);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0){

            }else{
                $stmt = $con->prepare("INSERT INTO `editors`( `email`, `fullname`, `password`, `editorial_level`, `editorial_section`) VALUES (?,?,?,?,?)");
                $stmt->bind_param("sssss",$currentEmail, $fullname, $currenPassword, $editorialLevel, $discipline);
                $stmt->execute();
            }
        }
    $response = array("status" => "accountExists", "message"=>"redirect to login");
    echo json_encode($response);
    }else{
        $response = array("status" => "NewReviewer", "message"=>"redirect to signup as reviewer");
    echo json_encode($response);
    }
}