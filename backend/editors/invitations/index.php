<?php
include "../../cors.php";
include "../../db.php";


if (isset($_GET["a_id"]) && isset($_GET["u_id"])) {
    $article_id = $_GET["a_id"];
    $userEmail = $_GET["u_id"];

    $stmt = $con->prepare("SELECT * `invitations` WHERE md5(`invitation_link`) = ? AND md5(`invited_user`) = ?");
    $stmt->bind_param("ss", $article_id, $userEmail);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if (mysqli_num_rows($result) > 0) {
            $row = $result->fetch_assoc();
            $invitation_status = $row["invitation_status"];
            $invitedUserEmail  = $row["invited_user"];
            $invitationId = $row["invitation_link"];
            if ($invitation_status == "expired") {
                $response = array("status" => "error", "message" => "Opps, This invitation link has expired");
                echo json_encode($response);
            } else if ($invitation_status == "expired") {
                $response = array("status" => "error", "message" => "This Invite has already been accepted");
                echo json_encode($response);
            } else {


                if (isset($_GET["accept"])) {
                    $stmt = $con->prepare("UPDATE `invitations` SET `invitation_status` = 'invitation_accepted' WHERE md5(`invitation_link`) =? AND md5(`invited_user`) =? ");
                    $stmt->bind_param("ss", $article_id, $userEmail);
                    if ($stmt->execute()) {
                        $response = array("status" => "success", "message" => "Invitation accepted succesfully, redirecting to create account for ". $invitedUserEmail);

                        // Update the edit process 
                        $stmt = $con->prepare("UPDATE `submitted_for_edit` SET `status` = 'invitation_accepted' WHERE `article_id`=? AND `editor_email` =?");
                        $stmt->bind_param("ss",$invitationId, $invitedUserEmail );
                        $stmt->execute();
                        echo json_encode($response);

                    } else {
                        print_r($stmt->error);
                    }

                } else if (isset($_GET["reject"])) {
                    $stmt = $con->prepare("UPDATE `invitations` SET `invitation_status` = 'invitation_rejected' WHERE md5(`invitation_link`) =? AND md5(`invited_user`) =? ");
                    $stmt->bind_param("ss", $article_id, $userEmail);
                    if ($stmt->execute()) {
                        $response = array("status" => "success", "message" => "You have rejected this Invitation and will be redirected to the home page");


                        // Update the edit process 
                        $stmt = $con->prepare("UPDATE `submitted_for_edit` SET `status` = 'invitation_rejected' WHERE `article_id`=? AND `editor_email` =?");
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
    if (isset($_GET["accept"]) && isset($_GET["reject"])) {
        $response = array("status" => "error", "message" => "Invalid Request");
        echo json_encode($response);
    }
} else {
    $response = array("status" => "error", "message" => "Invalid Request");
    echo json_encode($response);
}
