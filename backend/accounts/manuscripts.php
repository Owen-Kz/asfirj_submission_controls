<?php 

include "../cors.php";
include "../db.php";
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$useremail = $data["user"];

if($useremail){
    // Check if the user has any submissions under thier email 
    $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `corresponding_authors_email` = ? AND `title` != '' AND `title` != 'Draft Submission' ORDER BY `id` DESC");
    if(!$stmt){
        echo json_encode(array("error" => $stmt->error));
    }
    $stmt->bind_param("s", $useremail);
    $stmt->execute();

    $result = $stmt->get_result();
    $count = $result->num_rows;
    if($count > 0){
        $articles = array();

        while($row = $result->fetch_assoc()){
        
                $articles[] = $row;
            
        }
        $response = array("success" => "Articles List", "articles" => $articles);
        echo json_encode($response);
    }else{
        $response = array("success" => "No Articles", "articles" => []);
        echo json_encode($response);
    }

}else{
    $response = array("error" => "Incomplete Query");
    echo json_encode($response);
}
