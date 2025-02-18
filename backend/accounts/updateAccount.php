<?php
include "../cors.php";
include "../db.php";


if(isset($_SESSION["user_id"])){
$userEmail = $_SESSION["user_id"];
$prefix = $_POST["prefix"];
$firstname = $_POST["first_name"];
$lastname = $_POST["last_name"];
$othername = $_POST["other_name"];
$orcid = $_POST["orcid"];
$discipline = $_POST["discipline"];
$affiliation = $_POST["affiliation"];
$affiliationCountry = $_POST["affiliation_country"];
$affiliationCity = $_POST["affiliation_city"];
$ireviewAvailable = $_POST["is_available_for_review"];
$asfi_membership_id = $_POST["asfi_membership_id"];

// Find the user 

$stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `id` = ? AND `account_status` != 'unverified'");
if (!$stmt) {
    $response = array("error" => $con->error, "articles" => []);
    echo json_encode($response);
    exit;  // Exit the script since there's an error in preparing the statement
}
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows > 0){
    // Update the Account Details 
    $stmt= $con->prepare("UPDATE `authors_account` SET `prefix`=?,`firstname`=?,`lastname`=?,`othername`=?,`orcid_id`=?,`discipline`=?,`affiliations`=?,`affiliation_country`=?,`affiliation_city`=?,`is_available_for_review`=?,`asfi_membership_id`=? WHERE `id` = ?");
    if (!$stmt) {
        $response = array("error" => $con->error, "articles" => []);
        echo json_encode($response);
        exit;  // Exit the script since there's an error in preparing the statement
    }
    $stmt->bind_param("ssssssssssss", $prefix, $firstname, $lastname, $othername, $orcid, $discipline, $affiliation, $affiliationCountry, $affiliationCity, $ireviewAvailable, $asfi_membership_id, $userEmail);
    if($stmt->execute()){
        echo json_encode(array("status"=>"success", "message"=>"Account Updated Succesfully"));
    }

}else{
    echo json_encode(array("status"=>"error", "message"=>"User Not Found"));
}
}else{
    echo json_encode(array("status"=>"error", "message"=>"Invalid Parameters"));
}