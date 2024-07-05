<?php

function UpdateRevision($type,$RevisionsId, $revisionsCount, $discipline, $title, $combinedFilename, $cover_letter_file, $abstract, $corresponding_author, $articleID, $revisionStatus, $tablesName, $figuresName, $abstractFileName, $supplementsFileName){
    include "../backend/db.php";
    $stmt = $con->prepare("INSERT INTO `submissions` (`article_type`,`revision_id`,`revisions_count`, `discipline`, `title`, `manuscript_file`,`cover_letter_file`, `tables`, `figures`, `graphic_abstract`, `supplementary_material`, `abstract`, `corresponding_authors_email`, `article_id`) VALUES(?,?,?, ?, ?, ?, ?, ?, ?,?, ?,?,?,?)");
    $stmt->bind_param("ssisssssssssss", $type,$RevisionsId, $revisionsCount, $discipline, $title, $combinedFilename, $cover_letter_file,  $tablesName, $figuresName, $abstractFileName, $supplementsFileName, $abstract, $corresponding_author, $articleID);
    if($stmt->execute()){
        // UPdaet the Status 
        $stmt = $con->prepare("UPDATE `submissions` SET `status` = ? WHERE `article_id` = ?");
        if(!$stmt){
            echo json_encode(array("status" => "error", "message" => $stmt->error));
        }
        $stmt->bind_param("ss",$revisionStatus, $articleID);
        $stmt->execute();
        $response = array("status"=>"success", "message"=>"Submission Successful");
        echo json_encode($response);
    } else {
        $response = array("status"=>"error", "message"=>"Could Not Complete Submission");
        echo json_encode($response);
    }
}