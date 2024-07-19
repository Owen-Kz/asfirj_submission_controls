<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

if(isset($_GET["u_id"])){
    $isAdmin = isAdminAccount($_GET['u_id']);
    if($isAdmin){
    $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE 1 ORDER BY `id` DESC");
    if(!$stmt){
        echo $stmt->error;
    }
    // $stmt->bind_param("s", $_GET["articleID"]);
    $stmt->execute();
    $resutl = $stmt->get_result();
    $authorsList = array();
    while($row = $resutl->fetch_assoc()){
        $authorsList[] = $row;
    }
    $response = array("status" => "success",  "authorsList" => $authorsList);
    echo json_encode($response);
}else{
    echo json_encode(array("status"=>"error", "message"=>'Unathorized Access'));
}
}else{
    $response = array("InvalidParameters");
    echo json_encode($response);
}