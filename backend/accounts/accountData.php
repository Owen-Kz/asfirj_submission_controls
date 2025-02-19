<?php

include "../cors.php";
include "../db.php";
session_start();
$data = json_decode(file_get_contents("php://input"), true);
$email = $data["encrypted"];
// Check if user is logged in
if (!isset($email)) {
    // header("Location: /login.php"); // Redirect to the login page
    $response = array("status" => "error", "accountData" => "Not Logged In");
    
    exit(); // Stop script execution
}

// $email = $email;

// Prepare SQL query
$stmt = $con->prepare("SELECT * FROM `authors_account` WHERE md5(`id`) = ?");
if (!$stmt) {
    $response = array("status" => "error", "message" => "Database error: " . $con->error);
    echo json_encode($response);
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $response = array("status" => "success", "accountData" => $row);
} else {
    $response = array("status" => "error", "accountData" => "NotFound");
}

// Send JSON response
echo json_encode($response);
exit();
