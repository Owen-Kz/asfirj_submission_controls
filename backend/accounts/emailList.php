<?php

include "../cors.php";
include "../db.php";

// session_start();
if(isset($_GET["u_id"])){

        $stmt = $con->prepare('SELECT * FROM `sent_emails` WHERE `recipient` = ? ORDER BY `id` DESC');
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