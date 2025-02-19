<?php 

include "../cors.php";
include "../db.php";
session_start();

$data = json_decode(file_get_contents("php://input", true));

$useremail = $_GET["u_id"];

if($useremail)
$stmt = $con->prepare("SELECT * FROM `submissions` WHERE `corresponding_authors_email` = ? ANd `atatus` = ''
");