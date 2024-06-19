<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once dirname(__DIR__) . "/backend/exportENV.php";


// $server__DB_rays = $DB_HOST_RAYSON;
// $user_DB_rays = $DB_USER_RAYSON;
// $pass_DB_rays = $DB_PASS_RAYSON;
// $db_DB_rays = $DB_NAME_RAYSON;
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// require_once "exportENV.php";

$server__DB_rays = $_ENV['DB_HOST'];
$user_DB_rays = $_ENV['DB_USER'];
$pass_DB_rays = $_ENV['DB_PASS'];
$db_DB_rays = $_ENV["DB_NAME"];



// Create connection

$con = mysqli_connect($server__DB_rays, $user_DB_rays, $pass_DB_rays, $db_DB_rays);

// Check connection
if (!$con) {
    $response = array('status' => 'error', 'error' => mysqli_connect_error());
    echo json_encode($response);
    die("Connection failed: " . mysqli_connect_error());

}


?>