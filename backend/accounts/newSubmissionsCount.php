<?php

include "../cors.php";
include "../db.php";

$userId = $_SESSION["user_email"];

if(isset($userId)){
    $stmt = $con->prepare("SELECT COUNT(*) AS `count` FROM `submissions` WHERE `corresponding_authors_email` = ? AND `title` != ''");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = $row["count"];
    $response = array("success"=>"CountSuccess", "count"=> $count);
    echo json_encode($response);

}else{
    echo json_encode(array("error"=>"Invalid Parameters"));
}