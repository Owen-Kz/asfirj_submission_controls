<?php 

include "../cors.php";
include "../db.php";

$data = json_decode(file_get_contents("php://input"), true);

$useremail = $_SESSION["user_email"];

if($useremail){
    // Check if the user has any submissions under thier email 
    $stmt = $con->prepare("SELECT * FROM `submission_authors` WHERE `authors_email` = ?");
    if(!$stmt){
        echo json_encode(array("error" => $stmt->error));
    }
    $stmt->bind_param("s", $useremail);
    $stmt->execute();

    $result = $stmt->get_result();
    $count = $result->num_rows;

    $listOfManuscripts = array();
    if($result->num_rows > 0){
        // $listOfManuscripts = [];  // Initialize the array

        while ($row = $result->fetch_assoc()) {
            $articleId = $row["submission_id"];
            $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `corresponding_authors_email` != ? AND `article_id` = ? AND `status` != 'saved_for_later' ORDER BY `id` DESC");
            
            if (!$stmt) {
                $response = array("error" => $con->error, "articles" => []);
                echo json_encode($response);
                exit;  // Exit the script since there's an error in preparing the statement
            }
        
            $stmt->bind_param("ss", $useremail, $articleId);
            
            if (!$stmt->execute()) {
                $response = array("error" => $stmt->error, "articles" => []);
                echo json_encode($response);
                exit;  // Exit the script since there's an error in executing the statement
            }
        
            $resultK = $stmt->get_result();
        
            if ($resultK) {
                while ($rowK = $resultK->fetch_assoc()) {
                    $listOfManuscripts[] = $rowK;  // Fetch associative array
                }
            } else {
                // If there are no results, continue the loop
                continue;
            }
        }
        
        // $response = array("error" => null, "articles" => $listOfManuscripts);
        // echo json_encode($response);
        
    }

    $response = array("success" => "Articles List", "articles" => $listOfManuscripts);
    echo json_encode($response);
    // if($count > 0){
    //     $articles = array();

    //     while($row = $result->fetch_assoc()){
            
     
    //         // $ArticlesRow = $result->fetch_assoc();
    //             $articles[] = $row;
    //     }
    //     $response = array("success" => "Articles List", "articles" => $articles);
    //     echo json_encode($response);
    // }else{
    //     $response = array("success" => "No Articles", "articles" => []);
    //     echo json_encode($response);
    // }

}else{
    $response = array("error" => "Incomplete Query");
    echo json_encode($response);
}
