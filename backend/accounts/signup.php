<?php

include "../cors.php";
include "../db.php";
include "../sendWelcomeEmail.php";
include "../sendNewSignUpEmailToHandler.php";
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$prefix = $data["prefix"];
$firstname = $data["firstname"];
$lastname  = $data["lastname"];
$othername = $data["othername"];
$email = $data["email"];
$discipline = $data["discipline"];
$affiliations = $data["affiliations"];
$orcidID = $data["orcid"];
$affiliations_country = $data["affiliations_country"];
$affiliations_city = $data["affiliations_city"];
$available_for_review = $data["availableForReview"];
$password = $data["password"];


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
            
            $response = array("status"=>"error", "message"=>"Account Already Exists please login");
            echo json_encode($response);
        }else{
            
            $stmt = $con->prepare("INSERT INTO `authors_account` (`prefix`, `email`, `firstname`, `lastname`, `othername`, `orcid_id`, `discipline`, `affiliations`, `affiliation_country`, `affiliation_city`, `password`, `is_available_for_review`) VALUES (?,?,?,?,?,?,?,?,?, ?,?,?)");
            if(!$stmt){
                $response = array("status"=>"error", "message"=>$con->error);
                echo json_encode($response);
            }else{
            $stmt->bind_param("ssssssssssss", $prefix, $email, $firstname, $lastname, $othername,$orcidID, $discipline, $affiliations, $affiliations_country, $affiliations_city, $pass, $available_for_review);
            
            if($stmt->execute()){
                $userFullname = "$firstname $lastname";
                $RecipientEmail = "submissions@asfirj.org";

                SendWelcomeEmail($email);
                NewSignupEmailToHandler($RecipientEmail, $email, $userFullname);

                $response = array("status"=>"success", "message"=>"Account Created Successfully, A verification email has been sent to $email");
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

