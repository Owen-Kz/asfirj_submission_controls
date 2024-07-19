<?php
include "./cors.php";
include "./db.php";

if(isset($_GET["a_id"])){
$article_id = $_GET["a_id"];
    $stmt = $con->prepare("SELECT * FROM `submission_keywords` WHERE `article_id` = ? AND `keyword` != ''");
    $stmt->bind_param("s", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $keywords = array();
        while($row = $result->fetch_assoc()){
            $keywords[] = $row;
        }
     $response  = array("success"=>"Keywords Exists", "keywords"=>$keywords);
     echo json_encode($response);
    }
}