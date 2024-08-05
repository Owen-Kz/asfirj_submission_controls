<?php 
include __DIR__."../../cors.php";
include __DIR__."../../db.php";
include __DIR__."../isAdministrator.php";

$adminId = $_GET["a_id"];
if(isset($adminId)){
    if(isAdministrator($adminId)){
        $stmt = $con->prepare("SELECT * FROM `news_letter_subscribers` WHERE 1 ORDER BY `id` DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            $emailList = array();
            while($row = $result->fetch_assoc()){
                $emailList[] = $row;
            }
            echo json_encode(array("success"=>"emailListFound", "emailList" => $emailList));
        }else{
            echo json_encode(array("error"=>"NoEmailListFound", "emailList" => []));

        }
    }else{
        echo json_encode(array("error"=>"unAuthorizedAccess", "emailList" => []));
        
    }
}else{
    echo json_encode(array("error"=>"invalidParameters", "emailList" => []));
    
}