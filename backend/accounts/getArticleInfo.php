<?php
include "../cors.php";
include "../db.php";
$data = json_decode(file_get_contents("php://input"), true);

if(isset($data["id"])){
    $articleId = $data["id"];
    // find all the article data realated to the author 
    $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `article_id` = ?");
    $stmt->bind_param("s", $articleId);
    if(!$stmt){
        echo json_encode(array("error" => $stmt->error));
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $response = array("success" => "Articles List", "articles" => $row);
        echo json_encode($response);
    }else{
        $response = array("notFound");
        echo json_encode($response);
    }
    $stmt->execute();
    $result = $stmt->get_result();
}else{
    $response = array("unAuhtorized");
    echo json_encode($response);
}