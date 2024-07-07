<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// require_once dirname(__DIR__) . "/backend/exportENV.php";

echo "MySpeace";
require_once __DIR__ .'/../vendor/autoload.php';// If you're using Composer (recommended)
// Comment out the above line if not using Composer
// require("<PATH TO>/sendgrid-php.php");
// If not using Composer, uncomment the above line and
// download sendgrid-php.zip from the latest release here,
// replacing <PATH TO> with the path to the sendgrid-php.php file,
// which is included in the download:
// https://github.com/sendgrid/sendgrid-php/releases
// Inmport Environment Variables
include './exportENV.php';

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