<?php
    // if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Allow from any origin
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    // }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        }
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }
        exit(0);
    }
// include "../../db.php";
include "../db.php";
// $data = json_decode(file_get_contents("php://input"), true);

$action = $_GET["action"];
$invitationFor = $_GET["invite_for"];

if($invitationFor === "review"){

if (isset($_GET["a_id"]) && isset($_GET["u_id"])) {
    $article_id = $_GET["a_id"];
    $userEmail = $_GET["u_id"];
    // $currentTimeStamp = $_GET["today"];
    $currentTime = time();
    $Today = date('Y-m-d', $currentTime);


    $stmt = $con->prepare("SELECT * FROM `invitations` WHERE `invitation_link` = ? AND `invited_user` = ?");
    $stmt->bind_param("ss", $article_id, $userEmail);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if (mysqli_num_rows($result) > 0) {
            
            $row = $result->fetch_assoc();
            
            $invitation_status = $row["invitation_status"];
            $invitedUserEmail  = $row["invited_user"];
            $invitationId = $row["invitation_link"];
            $expiryDate = $row["invitation_expiry_date"];
            $invitationLink = $row["invitation_link"];

            if($expiryDate == $Today){
                // set the link to Expired
                $stmt = $con->prepare("UPDATE `invitations` SET `invitation_status` = 'expired' WHERE `invitation_link` AND `invited_user` = ?");
                $stmt->bind_param("ss", $invitationId, $invitedUserEmail);
                $stmt->execute();
                $response = array("status" => "error", "message" => "Opps, This invitation link has expired");
                echo json_encode($response);
                exit;
            }else if ($invitation_status == "expired" || $expiryDate == $Today) {
                $response = array("status" => "error", "message" => "Opps, This invitation link has expired");
                echo json_encode($response);
                exit;
            } else if ($invitation_status == "expired") {
                $response = array("status" => "error", "message" => "This Invite has already been accepted");
                echo json_encode($response);
                exit;
            } else {

 
                if (isset($action)) {
                    $stmt = $con->prepare("UPDATE `invitations` SET `invitation_status` = 'review_invitation_accepted' WHERE `invitation_link` =? AND `invited_user` =? ");
                    $stmt->bind_param("ss", $article_id, $userEmail);
                    if ($stmt->execute()) {
                        $response = array("status" => "success", "message" => "Invitation accepted successfully, redirecting to create account for ". $invitedUserEmail, "email" => $invitedUserEmail);

                        // Update the edit process 
                        $stmt = $con->prepare("UPDATE `submitted_for_review` SET `status` = 'review_invitation_accepted' WHERE `article_id`=? AND `reviewer_email` =?");
                        $stmt->bind_param("ss",$invitationId, $invitedUserEmail );
                        $stmt->execute();
                        echo json_encode($response);

                    } else {
                        print_r($stmt->error);
                    }

                } else if (isset($action)) {
                    $stmt = $con->prepare("UPDATE `invitations` SET `invitation_status` = 'invitation_rejected' WHERE `invitation_link` =? AND `invited_user` =? ");
                    $stmt->bind_param("ss", $article_id, $userEmail);
                    if ($stmt->execute()) {
                        $response = array("status" => "success", "message" => "You have rejected this Invitation and will be redirected to the home page");


                        // Update the edit process 
                        $stmt = $con->prepare("UPDATE `submitted_for_review` SET `status` = 'invitation_rejected' WHERE `article_id`=? AND `reviewer_email` =?");
                        $stmt->bind_param("ss",$invitationId, $invitedUserEmail );
                        $stmt->execute();

                        echo json_encode($response);
                    } else {
                        print_r($stmt->error);
                    }
                }
            }
        } else {
            $response = array("status" => "error", "message" => "Data Does not Exist");
            echo json_encode($response);
        }
    } else {
        print_r($stmt->error);
    }


    // Check if both accept and reject are set 
    // if (isset($action) && $action)) {
    //     $response = array("status" => "error", "message" => "Invalid Request");
    //     echo json_encode($response);
    // }
} else {
    $response = array("status" => "error", "message" => "Invalid Request");
    echo json_encode($response);
}
}else{
          $response = array("status" => "error", "message" => "Invalid Request");
        echo json_encode($response);
}