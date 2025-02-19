<?php
include "../cors.php";
include "../db.php";
session_start();

$data = json_decode(file_get_contents("php://input"), true);

if($data){
    $artilce_id = $data["id"];
    $stmt = $con->prepare("SELECT * FROm `reviews` WHERE `article_id` = ? AND `review_status` = 'review_submitted' ORDER BY `id` DESC");
    $stmt->bind_param("s", $artilce_id);
    if(!$stmt){
        echo json_encode(array("status" => "error", "message" => $stmt->error, "reviews" => []));
    }

    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $reviews = array();
        while($row = $result->fetch_assoc()){
            $reviews[] = $row;
        }
        $response = array("status" => "success", "reviews" => $reviews);
        echo json_encode($response);

    }else{
        echo json_encode(array("status" => "success", "message" => "Not Reviews Available for this Submission",  "reviews" => []));
    }
}