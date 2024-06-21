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
$affiliations_country = $data["affliations_country"];
$affiliations_city = $data["affiliations_city"];
$available_for_review = "yes";
$is_editor = "yes";
$password = $data["password"];



if($email && $affiliations){
    // Function to create editor accounts 
    function CreateEditorAccount($email, $prefix, $firstname, $lastname, $othername, $affiliation, $affiliation_country, $affiliation_city, $password){
        include "../db.php";
        $pass = password_hash($password, PASSWORD_DEFAULT);
        $editorialLevel = "Sectional Editor";
        $editorFullname = $prefix." ". $firstname." ". $lastname. " ". $othername;
        // Check if the editor Exists 
        $stmt = $con->prepare("SELECT * FROM `editors` WHERE `email` = ?) VALUES (?)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            $response = array("status"=>"error", "message" => "Account Already Exists please login");
            echo json_encode($response);
        }else{
        $stmt = $con->prepare("INSERT * INTO `editors` (`email`, `fullname`, `password`, `editorial_level`) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $email, $editorFullname, $pass, $editorialLevel);
        if($stmt->execute()){
            $response = array("status"=>"success", "message"=>"Account Created succesfully");
            echo json_encode($respone);
        }else{
            $response = array("status"=>"error", "message"=>"Could not create editor Account");
            echo json_encode($respone);
        }
        }
        

    }
// Check if the Account Arleady exists and is an editore 
$stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` =? ");
$stmt->bind_param("s", $email);
if($stmt->execute()){
$result = $stmt->get_result();
if($result->num_rows > 0){
    // If the person is an editor send error reponse 
    $row = $result->fetch_assoc();
    $isEditor = $row["is_editor"];
    if($isEditor == "yes"){
        $response = array("status"=> "error", "message" => "Account Already Exists Please login");
        echo json_encode($response);
    }else{
        // if teh user exists and is not an editor check if they were invited as editors before creating their editor account
    // check if the persona has been invited or if their invitaion has expired or hasn't been accepted 
    $stmt = $con->prepare("SELECT * FROM `invitations` WHERE `invited_user` = ? AND `invitation_status` != 'invitaion_rejected' OR `invitation_status` != 'expired'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        // Create the editor account if the user has been invited
        // Update the account to edito if they have been invited 
        $stmt = $con->prepare("UPDATE `authors_account` SET `is_editor` = ? AND `is_reviewer` = ? WHERE `email` = ?");
        $stmt->bind_param("sss", $is_editor,$is_editor,$email);
        $stmt->execute();
        CreateEditorAccount($email, $prefix, $firstname, $lastname, $othername, $affiliation, $affiliation_country, $affiliation_city, $password);
    }else{

    $response = array("status"=> "error", "message" => "This account has not been invited as an editor");
    echo json_encode($response);

    }
    }

}else{
    // Check if the user has been invited then create an editor and author account for them 

    $stmt = $con->prepare("SELECT * FROM `invitations` WHERE `invited_user` = ? AND `invitation_status` != 'invitaion_rejected' OR `invitation_status` != 'expired'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $invitaionStatus = $row["invitation_status"];
        $accountStatus = "verified";
        // Create the editor account if the user has been invited
        // Update the account to edito if they have been invited 
        $stmt = $con->prepare("INSERT INTO `authors_account`( `prefix`, `email`, `firstname`, `lastname`, `othername`, `affiliations`, `affiliation_country`, `affiliation_city`, `is_available_for_review`, `is_editor`, `is_reviewer`, `password`, `editor_invite_status`, `reviewer_invite_status`, `account_status`) VALUES (?,?,?,?,?,?,'?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssssssssssss",$prefix,$email, $firstname, $lastname, $othername, $affiliation, $affiliation_country, $affiliation_city, $is_editor, $is_editor, $is_editor, $password, $invitaionStatus, $invitaionStatus, $accountStatus);
        $stmt->execute();
        CreateEditorAccount($email, $prefix, $firstname, $lastname, $othername, $affiliation, $affiliation_country, $affiliation_city, $password);
    }else{

    $response = array("status"=> "error", "message" => "This account has not been invited as an editor");
    echo json_encode($response);

    }
}
}

}