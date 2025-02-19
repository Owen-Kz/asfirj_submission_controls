<?php
include "../cors.php";
include "../db.php";

$data = json_decode(file_get_contents("php://input"), true);
$editor_id = $_SESSION["user_email"];

$stmt = $con->prepare("SELECT * FROM `editors` WHERE `email` = ?");
$stmt->bind_param("s", $editor_id);
$stmt->execute();
$result = $stmt->get_result();
if(mysqli_num_rows($result) > 0){

$stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `is_available_for_review` = 'yes'");

$stmt->execute();
$result = $stmt->get_result();
$count = mysqli_num_rows($result);

if($count > 0){
    $authorsList = array(); // Initialize an array to store all authors
 
    while ($row = $result->fetch_assoc()) {
        // Loop through each row in the result set and append it to the authorsList array
        $authorsList[] = $row;
    }
    $response = array("message"=>"reviewersList", "reviewers" => $authorsList);
    echo json_encode($response);
}else{
    $response = array("message"=>"NO Reviewer available", "reviewers" => []);
    echo json_encode($response);
}
}else{
    $response = array("message"=>"unauthorized", "reviewers" => []);
    echo json_encode($response);
}
