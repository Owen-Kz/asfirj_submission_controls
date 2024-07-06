<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

// session_start();
if(isset($_GET["u_id"])){
    $isAdmin = isAdminAccount($_GET['u_id']);
    if($isAdmin){
        $stmt = $con->prepare('SELECT * FROM `sent_emails` WHERE `id` = ?');
        $stmt->bind_param("s", $_GET["emailId"]);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            // $emails = array();
            $row = $result->fetch_assoc();
            // while($row = $result->fetch_assoc()){
            //     $emails[] = $row;
            // }
            $response = array("emails"=> $row);
            echo json_encode($response);
        }else{
            $response = array("noEmail"=> []);
            echo json_encode($response);
        }
    }
}