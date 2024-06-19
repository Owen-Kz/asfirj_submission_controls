<?php

include "../cors.php";
include "../db.php";

$submission_id  = $_GET["a_id"];

if(isset($_GET["a_id"])){
    $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `article_id` = ?");
    $stmt->bind_param("s", $submission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_array($result);
        $response = array("status" => "success", "articleContent" => $row);
        echo json_encode($response);
    }else{
        $response = array("status" => "error", "articleContent" => "No related Content");
        echo json_encode($response);
    }
}else{
    $response = array("status" => "error", "articleContent" => "Parameters are not set");
    echo json_encode($response);
}