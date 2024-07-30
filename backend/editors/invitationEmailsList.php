<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

// session_start();
if(isset($_GET["u_id"])){
    $isAdmin = isAdminAccount($_GET['u_id']);
    if($isAdmin){
        $stmt = $con->prepare("SELECT * FROM `sent_emails` WHERE md5(`sender`) = ? AND `email_for` = 'To Edit' ORDER BY `id` DESC");
        $stmt->bind_param("s", $_GET["u_id"]);
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