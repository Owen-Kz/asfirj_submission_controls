<?php

function UpdateRevision($type,$RevisionsId, $revisionsCount, $discipline, $title, $combinedFilename,$combinedDocFile, $cover_letter_file, $abstract, $corresponding_author, $articleID, $revisionStatus, $tablesName, $figuresName, $abstractFileName, $supplementsFileName, $authorsPrefix, $authorEmail,$authors_firstname,$authors_lastname, $authors_other_name, $authors_orcid, $affiliation, $affiliation_country, $affiliation_city, $keywords, $suggested_reviewer_fullname, $suggested_reviewer_affiliation, $suggested_reviewer_country, $suggested_reviewer_city, $suggestedReviewerEmail, $LoggedInauthorsPrefix,$LoggedInauthors_firstname, $LoggedInauthors_lastname, $LoggedInauthors_other_name, $LoggedInauthorEmail, $loggedIn_authors_ORCID, $LoggedInaffiliation, $LoggedInaffiliation_country, $LoggedInaffiliation_city, $trackedManuscriptFileName, $previousManuscriptID){
    include "../backend/db.php";
    require_once "../backend/sendNewSubmissionEmail.php";
    include "../backend/addSubmissoinKeywords.php";
    include "../backend/addSuggestedReviewers.php";
    include "../backend/createCoAuthor.php";
    
    // First check if the submission already exists 
    $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `revision_id` = ? ");
    if(!$stmt){
        echo json_encode(array("status" => "error", "message" => $stmt->error));
    }
    $stmt->bind_param("s", $RevisionsId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt = $con->prepare("UPDATE `submissions` SET `article_type`= ?,`discipline`=?,`title`=?,`manuscript_file`=?,`document_file`=?,`tracked_manuscript_file`=?,`cover_letter_file`=?,`tables`=?,`figures`=?,`graphic_abstract`=?,`supplementary_material`=?,`abstract`=?,`corresponding_authors_email`=?,`article_id`=?,`revision_id`=?,`revisions_count`=?,`previous_manuscript_id`=?,`status`=? WHERE `revision_id` = ?");
        $stmt->bind_param("sssssssssssssssisss", $type, $discipline, $title, $combinedFilename, $combinedDocFile, $trackedManuscriptFileName, $cover_letter_file, $tablesName, $figuresName, $abstractFileName, $supplementsFileName, $abstract, $corresponding_author, $articleID, $RevisionsId, $revisionsCount, $previousManuscriptID, $revisionStatus, $RevisionsId);
        $stmt->execute();


        // Update the status of the main submitted manuscript 
        // UPdaet the Status 
        $stmt = $con->prepare("UPDATE `submissions` SET `status` = ? WHERE `article_id` = ?");
        if(!$stmt){
            echo json_encode(array("status" => "error", "message" => $stmt->error));
        }
        $stmt->bind_param("ss",$revisionStatus, $articleID);
        $stmt->execute();

        // Send final response 
        $response = array("status"=>"success", "message"=>"Submission Successful $revisionStatus");
        echo json_encode($response);
    }else{
        // Create a new revion if the old one does not exist 
    $stmt = $con->prepare("INSERT INTO `submissions` (`article_type`,`revision_id`,`revisions_count`, `discipline`, `title`, `manuscript_file`, `document_file`, `cover_letter_file`, `tables`, `figures`, `graphic_abstract`, `supplementary_material`, `abstract`, `corresponding_authors_email`, `article_id`, `tracked_manuscript_file`, `previous_manuscript_id`) VALUES(?,?,?, ?, ?, ?, ?, ?, ?,?, ?,?,?,?, ?, ?, ?)");
    $stmt->bind_param("ssissssssssssssss", $type,$RevisionsId, $revisionsCount, $discipline, $title, $combinedFilename,$combinedDocFile, $cover_letter_file,  $tablesName, $figuresName, $abstractFileName, $supplementsFileName, $abstract, $corresponding_author, $articleID, $trackedManuscriptFileName, $previousManuscriptID);
    if($stmt->execute()){
        // UPdaet the Status 
        $stmt = $con->prepare("UPDATE `submissions` SET `status` = ? WHERE `article_id` = ?");
        if(!$stmt){
            echo json_encode(array("status" => "error", "message" => $stmt->error));
        }
        $stmt->bind_param("ss",$revisionStatus, $articleID);
        $stmt->execute();
        $response = array("status"=>"success", "message"=>"Submission Successful $revisionStatus");
        echo json_encode($response);
    } else {
        $response = array("status"=>"error", "message"=>"Could Not Complete Submission");
        echo json_encode($response);
    }

       // For other Authors 
       if (count($authorEmail) > 0) {
      
        for ($i = 0; $i < count($authorEmail); $i++) {
            $authorsFullname = "$authorsPrefix[$i] $authorEmail[$i]['authors_firstname'] $authorEmail[$i]['authors_lastname'] $authorEmail[$i]['authors_other_name']";
            try {
                if($authorEmail[$i] != ""){
                // Frist Check the the Author Exists 
                $stmt = $con->prepare("SELECT * FROM `submission_authors` WHERE `authors_email` = ? AND `submission_id` = ?");
                if (!$stmt) {
                    throw new Exception("Failed to prepare statement: " . $stmt->error);

                }
                $stmt->bind_param("ss", $authorEmail[$i], $articleID);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {

                } else {
                    $stmt = $con->prepare("INSERT INTO `submission_authors` (`submission_id`, `authors_fullname`, `authors_email`,`orcid_id`, `affiliations`, `affiliation_country`, `affiliation_city`) VALUES(?, ?, ?, ?, ?, ?,?)");
                    if (!$stmt) {
                        throw new Exception("Failed to prepare statement: " . $stmt->error);
                    }
                    $stmt->bind_param("sssssss", $articleID, $authorsFullname, $authorEmail[$i], $authors_orcid[$i], $affiliation[$i], $affiliation_country[$i], $affiliation_city[$i]);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to execute statement Author: " . $stmt->error);
                    }
                }
                // Create the NEw Co Authors Account 
                SendNewSubmissionEmail($authorEmail[$i], $title,  $mainSubmissionId );
                CreateCoAuthor($authorsPrefix[$i], $authors_firstname[$i],$authors_lastname[$i], $authors_other_name[$i], $authorEmail[$i], $authors_orcid[$i], $affiliation[$i], $affiliation_country[$i], $affiliation_city[$i]);
            }
                // Check for Errors
            } catch (Exception $e) {
                $response = array('status' => 'error', 'message' => 'ErrorAuthor:' . $e->getMessage());
                echo json_encode($response);
                exit;
            }
        }
    }
    // ADD KEYWORDs 
    if(isset($keywords) && $keywords != ""){
        for($i = 0; $i<count($keywords); $i++){
            try {
                if($keywords[$i] != ""){
           if(AddSubmissionKeywords($articleID, $keywords[$i])){
            
           }else{
            throw new Exception("Could Not Add keyword: " . $keywords[$i]);

           }
        }
        } catch (Exception $e) {
            $response = array('status' => 'error', 'message' => 'ErrorKeywords:' . $e->getMessage());
            echo json_encode($response);
            exit;
        }
            
        }
    }else{
        $response = array('status' => 'error', 'message' => 'ErrorKeywords:Keywords Not Set');
        echo json_encode($response);
        exit;
    }
    // Add Suggested REviewers
    if(isset($suggestedReviewerEmail) && $suggestedReviewerEmail !=""){
    
        for($i =0; $i < count($suggestedReviewerEmail); $i++){
            try{
                if($suggestedReviewerEmail[$i] != ""){
               if(AddSuggestedReviewers($articleID, $suggested_reviewer_fullname[$i], $suggested_reviewer_affiliation[$i], $suggested_reviewer_country[$i], $suggested_reviewer_city[$i], $suggestedReviewerEmail[$i])){

               }else{
                throw new Exception("Could Not Add Suggested Reviewer: " . $suggestedReviewerEmail[$i]);

               }
            }
            } catch (Exception $e) {
                $response = array('status' => 'error', 'message' => 'ErrorSuggestedReviewer:' . $e->getMessage());
                echo json_encode($response);
                exit;
            }
        }
    }


    // For logged in Author 
    $LoggedInauthorsFullname = "$LoggedInauthorsPrefix $LoggedInauthors_firstname $LoggedInauthors_lastname $LoggedInauthors_other_name";
    try {
        // Frist Check the the Author Exists 
        $stmt = $con->prepare("SELECT * FROM `submission_authors` WHERE `authors_email` = ? AND `submission_id` = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $stmt->error);

        }
        $stmt->bind_param("ss", $LoggedInauthorEmail, $articleID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {

        } else {
            $stmt = $con->prepare("INSERT INTO `submission_authors` (`submission_id`, `authors_fullname`, `authors_email`, `orcid_id`, `affiliations`, `affiliation_country`, `affiliation_city`) VALUES(?, ?,?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $stmt->error);
            }
            $stmt->bind_param("sssssss", $articleID, $LoggedInauthorsFullname, $LoggedInauthorEmail, $loggedIn_authors_ORCID, $LoggedInaffiliation, $LoggedInaffiliation_country, $LoggedInaffiliation_city);
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement Author: " . $stmt->error);
            }
        }
    } catch (Exception $e) {
        $response = array('status' => 'error', 'message' => 'ErrorAuthor:' . $e->getMessage());
        echo json_encode($response);
        exit;
    }
}
}