<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

if(isset($_SESSION["user_id"])){
    $isAdmin = isAdminAccount($_SESSION["user_id"]);
    if($isAdmin){
    $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE 1 ORDER BY `id` DESC");
    if(!$stmt){
        echo $stmt->error;
    } 
    // $stmt->bind_param("s", $_GET["articleID"]);
    $stmt->execute();
    $result = $stmt->get_result();


 while ($row = $result->fetch_assoc()) {
    foreach ($row as $key => $value) {
        $row[$key] = mb_convert_encoding($value ?? '', 'UTF-8', 'UTF-8'); // Use an empty string if null
    }
     $authorsList[] = $row;
}

    //     $authorsList[] = $row;
        
    // }
    // var_dump($authorsList);
    
     
    $json = json_encode(array("status" => "success",  "authorsList" => $authorsList));
 if ($json === false) {
    echo json_last_error_msg();
} else {
    echo $json;
}
}else{
    echo json_encode(array("status"=>"error", "message"=>'Unathorized Access'));
}
}else{
    $response = array("InvalidParameters");
    echo json_encode($response);
}