<?php

include "../cors.php";
include "../db.php";
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$prefix = $data["prefix"];
$firstname = $data["firstname"];
$lastname  = $data["lastname"];
$othername = $data["othername"];
$email = $data["email"];
$affiliations = $data["affiliations"];
$affiliations_country = $data["affiliations_country"];
$affiliations_city = $data["affiliations_city"];
$available_for_review = "yes";
$discipline = $data["discipline"];
$orcidID = $data["orcid"];
$password = $data["password"];


$stmt = $con->prepare("SELECT * FROM `submitted_for_review` WHERE `reviewer_email` =?");
$stmt->bind_param("s", $email);
if($stmt->execute()){
    $result = $stmt->get_result();
    $count = mysqli_num_rows($result);
    if($count > 0){

if(isset($email) && isset($password)){
    $pass = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` = ?");
    $stmt->bind_param("s", $email);
    if(!$stmt){
        $response = array("status"=>"error", "message"=>$stmt->error);
                echo json_encode($response);
    }else{
        $stmt->execute();
        $result = $stmt->get_result();

        $count = mysqli_num_rows($result);
        if($count > 0){
            echo "Account Already Exisits";
        }else{
            
            $stmt = $con->prepare("INSERT INTO `authors_account` (`prefix`, `email`, `orcid_id`, `discipline`, `firstname`, `lastname`, `othername`, `affiliations`, `affiliation_country`,  `affiliation_city`, `is_available_for_review`, `is_reviewer`, `reviewer_invite_status`, `account_status`, `password`) VALUES (?,?,?,?,?,?,?,?,?,?, ?, ?,?,?,?)");
            if(!$stmt){
                $response = array("status"=>"error", "message"=>$stmt->error);
                echo json_encode($response);
            }else{
                $accountStatus = "verified";
                $reviewInviteStatus = "accepted";
            $stmt->bind_param("sssssssssssssss", $prefix, $email, $orcidID, $discipline, $firstname, $lastname, $othername, $affiliations, $affiliations_country, $affiliations_city, $available_for_review, $available_for_review,$reviewInviteStatus, $accountStatus, $pass);
            
            if($stmt->execute()){
                $response = array("status"=>"success", "message"=>"Account Created Succesfully");
                echo json_encode($response);
            }else{
                $response = array("status"=>"error", "message"=>"Could Not Create Account");
                echo json_encode($response);
            }
        }


        }
    }
}else{
    $response = array("status"=>"error", "message"=>"Pleae fill all fields");
    echo json_encode($response);
}

    }else{
        $response = array("status"=>"error", "message"=>"Your not Eligible for this request.");
        echo json_encode($response);
    }
}else{
    $response = array("status"=>"error", "message"=>$stmt->error);
    echo json_encode($response);
}