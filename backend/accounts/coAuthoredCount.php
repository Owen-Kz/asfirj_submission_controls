<?php

include "../cors.php";
include "../db.php";

$userId = $_GET["u_id"];

if(isset($userId)){
    $stmt = $con->prepare("SELECT COUNT(*) AS `count` FROM `submission_authors` WHERE md5(`authors_email`) = ?");
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