<?php
include "../cors.php";
include "../db.php";

if(isset($_GET["articleID"])){
    $stmt = $con->prepare("SELECT * FROM `suggested_reviewers` WHERE `article_id` = ? ORDER BY `id` ASC");
    if(!$stmt){
        echo $stmt->error;
    }
    $stmt->bind_param("s", $_GET["articleID"]);
    $stmt->execute();
    $resutl = $stmt->get_result();
    $authorsList = array();
    while($row = $resutl->fetch_assoc()){
        $authorsList[] = $row;
    }
    $response = array("status" => "success",  "suggestedReviewers" => $authorsList);
    echo json_encode($response);
}else{
    $response = array("InvalidParameters");
    echo json_encode($response);
}