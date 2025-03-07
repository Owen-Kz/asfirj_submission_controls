<?php

include "../cors.php";
include "../db.php";

if(isset($_GET["a_id"])){
    $articleID = $_GET["a_id"];
    $stmt = $con->prepare("SELECT COUNT(*) AS `countInvitations` FROM `invitations` WHERE `invitation_status` = 'edit_invitation_accepted' AND `invitation_link` = ?");
    $stmt->bind_param("s", $articleID);
    $stmt->execute();
    $restult = $stmt->get_result();
    $row = $restult->fetch_assoc();
 
    $count = $row["countInvitations"];
    echo json_encode(array("success"=>"counted", "count"=>$count));
}else{
    echo json_encode(array("error"=>"couldNotCount", "count"=>0));

}