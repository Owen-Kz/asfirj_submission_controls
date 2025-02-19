<?php
include "../cors.php";
include "../db.php";

$data = json_decode(file_get_contents("php://input"), true);

$ArticleId = $data["article_id"];

if($ArticleId){
    $stmt = $con->prepare("SELECT * FROM `submitted_for_review` WHERE `article_id` = ? ORDER BY `id` DESC");
    $stmt->bind_param("s", $ArticleId);
    $stmt->execute();
    $result  = $stmt->get_result(); 
    if($result->num_rows > 0){
        $ReviewsList = array();

        while($row = $result->fetch_assoc()){
            $ReviewsList[] = $row;
        }
        echo json_encode(array("success" => "Review Availabled", "reviews" => $ReviewsList));
    }else{
        echo json_encode(array("error" => "No Review invitations have been sent"));
    }
}else{
    echo json_encode(array("error" => "Invalid Parameters"));
}