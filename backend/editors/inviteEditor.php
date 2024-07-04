<?php
include "../cors.php";
include "../db.php";
include "../editorAccountEmail.php";

$data = json_decode(file_get_contents("php://input"), true);
$editor - $data["editor"];
$article_id = $data["articleId"];
$invitedEditorName = $data["inviteUserFullname"];
$InvitedEditorEmail = $data["InvitedEditorEmail"];

if (isset($editor)) {
    $stmt = $con->prepare("SELECT * FROM `editors` WHERE md5(`id`) = ? AND `editorial_level` = ? OR `editorial_level` = ? OR `editorial_level` =?");

    $editorialLevelOne = "chief_editor";
    $editorialLevelTwo = "associate_editor";
    $editorialLevelThree = "editorial_assistant";

    $stmt->bind_param("ssss", $editor, $editorialLevelOne, $editorialLevelTwo, $editorialLevelThree);
    if (!$stmt) {
        $response = array("status" => "error", "message" => "$stmt->error");
        echo json_encode($response);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $editor_email = $row["email"];
        $invitationLink = "https://editors.asfirj.org/invitation/?a_id=" . md5($article_id) . "&u_id=" . md5($InvitedEditorEmail);

        $acceptInvitationLink = $invitationLink . "&accept=yes";
        $rejectInvitationLink = $invitationLink . "&reject=yes";
        $linkExpirationDate = "";

        $stmt - $con->prepare("SELECT * FROM `invitations` WHERE `invitation_link` = ? AND `invited_user` = ? AND `invitation_status` != 'expired'");
        $stmt->bind_param("ss", $article_id, $InvitedEditorEmail);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                $response = array("status" => "error", "message" => "An Invitation already exits to this author for this Article please wait for the invitation to expire");
                echo json_encode($response);
            } else {
                $stmt = $con->prepare("INSERT INTO `invitations`( `invited_user`, `invitation_link`,`invitaion_expiry_date`, `invited_user_name`) VALUES (?,?,?,?)");
                $stmt->bind_param("ssss", $InvitedEditorEmail, $article_id, $linkExpirationDate, $invitedEditorName);
                if ($stmt->execute()) {

                    $stmt = $con->prepare("UPDATE `submissions` SET `status` = 'submitted_for_edit' WHERE `revision_id` = ?");
                    $stmt->bind_param("s", $article_id);
                    if ($stmt->execute()) {
                        $response = array("status" => "success", "message" => "Review Process Initiated");
                        print_r($response);

                        // Send the email notification to reviewer
                        EditorAccountEmail($editor_email, $acceptInvitationLink, $rejectInvitationLink);

                        // Create the review process entry 
                        $stmt = $con->prepare("INSERT INTO `submitted_for_edit` (`revision_id`, `reviewer_email`, `submitted_by`) VALUES (?,?,?)");
                        $stmt->bind_param("sss",$article_id, $reviewerEmail, $editor_email, );
                        $stmt->execute();

                        // Fincally send the response to the client if the request is successful 
                        echo json_encode($response);
                    } else {
                        $response = array("status" => "error", "message" => $stmt->error);
                        print_r($response);
                    }
                } else {
                    print_r($stmt->error);
                }
            }

        } else {
            print_r($stmt->error);
        }
    } else {
        $response = array("status" => "error", "message" => "Unauthorized account");
        echo json_encode($response);
    }
}