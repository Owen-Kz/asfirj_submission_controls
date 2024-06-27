<?php 

include "../cors.php";
include "../db.php";

$data = json_decode(file_get_contents("php://input", true));

$useremail = $data["user"];

if($useremail)
$stmt = $con->prepare("SELECT * FROM `aubmissions` WHERE `corresponding_authors_email` = ? ANd `atatus` = ''
");