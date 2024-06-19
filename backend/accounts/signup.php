<!-- GENERAl Signup for Authors  -->
<?php

include "../cors.php";
include "../db.php";
include "../sendWelcomeEmail.php";
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$prefix = $data["prefix"];
$firstname = $data["firstname"];
$lastname  = $data["lastname"];
$othername = $data["othername"];
$email = $data["email"];
$affiliations = $data["affiliations"];
$affiliations_country = $data["affliations_country"];
$affiliations_city = $data["affiliations_city"];
$available_for_review = $data["available_for_review"];
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
        $response = array("stauts"=>"error", "message"=>$stmt->error);
                echo json_encode($response);
    }else{
        $stmt->execute();
        $result = $stmt->get_result();

        $count = mysqli_num_rows($result);
        if($count > 0){
            echo "Account Already Exisits";
        }else{
            
            $stmt = $con->prepare("INSERT INTO `authors_account` (`prefix`, `email`, `firstname`, `lastname`, `othername`, `affiliations`, `affiliation_country`, `affiliation_city`, `password`), VALUES (?,?,?,?,?,?,?,?,?)");
            if(!$stmt){
                $response = array("stauts"=>"error", "message"=>$stmt->error);
                echo json_encode($response);
            }else{
            $stmt->bind_param("sssssssss", $prefix, $email, $firstname, $lastname, $othername, $affiliations, $affiliations_country, $affiliations_city, $pass);
            
            if($stmt->execute()){

                SendWelcomeEmail($email);

                $response = array("stauts"=>"success", "message"=>"Account Created Succesfully");
                echo json_encode($response);
            }else{
                $response = array("stauts"=>"error", "message"=>"Could Not Create Account");
                echo json_encode($response);
            }
        }


        }
    }
}else{
    $response = array("stauts"=>"error", "message"=>"Pleae fill all fields");
    echo json_encode($response);
}

    }else{
        $response = array("stauts"=>"error", "message"=>"Your not Eligible for this request.");
        echo json_encode($response);
    }
}else{
    $response = array("stauts"=>"error", "message"=>$stmt->error);
    echo json_encode($response);
}