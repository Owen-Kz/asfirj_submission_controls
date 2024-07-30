<?php
include "../cors.php";
include "../db.php";

$data = json_decode(file_get_contents("php://input"), true);

if($data){
    $article_id = $data["article_id"];
    $stmt=$con->prepare("SELECT * FROM `submission_keywords` WHERE `article_id` = ?");
    $stmt->bind_param("s", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows >0){
        $keywords = array();
        while($row=$result->fetch_assoc()){
            $keywords[] = $row;
        }
        
        $response = array("success"=>"Keywords Available", "keywords"=>$keywords);
        echo json_encode($response);
    }else{
        $response = array("success"=>"NO Keywords", "keywords"=>[]);
        echo json_encode($response);
    }
}