<?php
function UpdateTheSubmission($type,$RevisionsId, $revisionsCount, $discipline, $title, $combinedFilename, $cover_letter_file, $abstract, $corresponding_author, $articleID, $submissionStatus, $tablesName, $figuresName, $abstractFileName, $supplementsFileName){
    include "../backend/db.php";
    // Frist check if the submission exist and is ready has been saved earlier 
    $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `article_id` = ? OR `revision_id` = ? AND `status` = 'saved_for_later' AND `corresponding_authors_email` = ?");
    if(!$stmt){
        echo $stmt->error;
        exit;
    }
    $stmt->bind_param("sss", $articleID, $articleID, $corresponding_author);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
    
          // UPdaet the Status 
          $stmt = $con->prepare("UPDATE `submissions` SET `article_type` = ?, `revision_id`=?, `discipline` = ?, `title` = ? , `manuscript_file` = ?,`cover_letter_file` = ?, `abstract` =?, `corresponding_authors_email` = ?, `tables`=?,`figures`=?,`graphic_abstract`=?,`supplementary_material`=?, `status` = ? WHERE `article_id` = ?");
          if(!$stmt){
              echo json_encode(array("status" => "error", "message" => $stmt->error));
          }
          $stmt->bind_param("ssssssssssssss",$type, $RevisionsId, $discipline, $title, $combinedFilename, $cover_letter_file, $abstract, $corresponding_author, $tablesName, $figuresName, $abstractFileName, $supplementsFileName,$submissionStatus, $articleID);
          $stmt->execute();
          $response = array("status"=>"success", "message"=>"Submission Successful");
          echo json_encode($response);
    }else{
        // Create a NEw Submission if the submission does not exist 
        $stmt = $con->prepare("INSERT INTO `submissions` ( `article_type`, `discipline`, `title`, `manuscript_file`, `cover_letter_file`, `tables`, `figures`, `graphic_abstract`, `supplementary_material`, `abstract`, `corresponding_authors_email`, `article_id`, `revision_id`, `status`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?");
        if(!$stmt){
            echo $stmt->error;
            exit;
        }
        $stmt->bind_param("ssssssssssssss", $type, $discipline, $title, $combinedFilename, $cover_letter_file, $tablesName, $figuresName, $abstractFileName, $supplementsFileName, $abstract, $corresponding_author, $articleID, $RevisionsId, $submissionStatus);
        $stmt->execute();
        $response = array("status"=>"success", "message"=>"Submission Successful");
          echo json_encode($response);
    }

}