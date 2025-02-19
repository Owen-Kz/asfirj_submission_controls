<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

// session_start();
if(isset($_SESSION["user_email"])){
    $isAdmin = isAdminAccount($_SESSION["user_email"]);
    if($isAdmin){
        $stmt = $con->prepare("SELECT * FROM `sent_emails` WHERE `sender` = ? AND `email_for` = 'To Edit' ORDER BY `id` DESC");
        $stmt->bind_param("s", $_SESSION["user_email"]);
        $stmt->execute();
        $result = $stmt->get_result(); 
        if($result->num_rows > 0){
            $emails = array();
            while($row = $result->fetch_assoc()){
                $emails[] = $row;
            }
            $response = array("emails"=> $emails);
            echo json_encode($response);
        }else{
            $response = array("noEmail"=> []);
            echo json_encode($response);
        }
    }
}