<?php
function UpdateTheSubmission($type,$RevisionsId, $revisionsCount, $discipline, $title, $combinedFilename, $cover_letter_file, $abstract, $corresponding_author, $articleID, $submissionStatus, $tablesName, $figuresName, $abstractFileName, $supplementsFileName, $authorsPrefix, $authorEmail,$authors_firstname,$authors_lastname, $authors_other_name, $authors_orcid, $affiliation, $affiliation_country, $affiliation_city, $keywords, $suggested_reviewer_fullname, $suggested_reviewer_affiliation, $suggested_reviewer_country, $suggested_reviewer_city, $suggestedReviewerEmail,  $LoggedInauthorsPrefix,$LoggedInauthors_firstname, $LoggedInauthors_lastname, $LoggedInauthors_other_name, $LoggedInauthorEmail, $loggedIn_authors_ORCID, $LoggedInaffiliation, $LoggedInaffiliation_country, $LoggedInaffiliation_city, $trackedManuscriptFileName){
    include "../backend/db.php";
    include "../backend/addSubmissoinKeywords.php";
    include "../backend/addSuggestedReviewers.php";
    include "../backend/createCoAuthor.php";
    // Frist check if the submission exist and is ready has been saved earlier 
    $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `title` = ?  AND `status` = 'saved_for_later' AND `corresponding_authors_email` = ?");
    if(!$stmt){
        echo $stmt->error;
        exit;
    }
    $stmt->bind_param("ss", $title, $corresponding_author);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        // $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `article_id` = ? AND `status` = 'saved_for_later' AND `corresponding_authors_email` = ?");
        // $stmt->bind_param("ss", $articleID, $corresponding_author);
        // if(!$stmt){
        //     $response = array("status"=>"error", "message" => $stmt->error);
        //     echo json_encode($response);
        //     exit;
        // }
        // $stmt->execute();
        // $result = $stmt->get_result();
        // $count = mysqli_num_rows($result);
        // if($count > 0){
        //     $row = $result->fetch_assoc();
        //     $revisionsCount = $row["revisions_count"];
        //     $newRevisionsCount = (int) $revisionsCount + 1;
    
        //     $RevisionsId = $articleID;
        // }
          // UPdaet the Status 
          $stmt = $con->prepare("UPDATE `submissions` SET `article_type` = ?, `revision_id`=?, `discipline` = ?, `title` = ? , `manuscript_file` = ?,`cover_letter_file` = ?, `abstract` =?, `corresponding_authors_email` = ?, `tables`=?,`figures`=?,`graphic_abstract`=?,`supplementary_material`=?, `status` = ?, `tracked_manuscript_file` =? WHERE `article_id` = ?");
          if(!$stmt){
              echo json_encode(array("status" => "error", "message" => $stmt->error));
          }
          $stmt->bind_param("sssssssssssssss",$type, $RevisionsId, $discipline, $title, $combinedFilename, $cover_letter_file, $abstract, $corresponding_author, $tablesName, $figuresName, $abstractFileName, $supplementsFileName, $submissionStatus,$trackedManuscriptFileName, $articleID);
          $stmt->execute();
          $response = array("status"=>"success", "message"=>"Submission Successfully $submissionStatus");
          echo json_encode($response);
    }else{
        // Create a NEw Submission if the submission does not exist 
        $stmt = $con->prepare("INSERT INTO `submissions` (`article_type`, `discipline`, `title`, `manuscript_file`, `cover_letter_file`, `tables`, `figures`, `graphic_abstract`, `supplementary_material`, `abstract`, `corresponding_authors_email`, `article_id`, `revision_id`, `status`, `tracked_manuscript_file`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        if(!$stmt){
            echo $stmt->error;
            exit;
        }
        $stmt->bind_param("sssssssssssssss", $type, $discipline, $title, $combinedFilename, $cover_letter_file, $tablesName, $figuresName, $abstractFileName, $supplementsFileName, $abstract, $corresponding_author, $articleID, $RevisionsId, $submissionStatus, $trackedManuscriptFileName);
        $stmt->execute();
        $response = array("status"=>"success", "message"=>"Submission Successfully $submissionStatus");
          echo json_encode($response);
    }

          // For other Authors 
          if (count($authorEmail) > 0) {
      
            for ($i = 0; $i < count($authorEmail); $i++) {
            
                $authorsFullname = "$authorsPrefix[$i] $authors_firstname[$i] $authors_lastname[$i] $authors_other_name[$i] ";

                // print("Values: articleID=$articleID, authorsFullname=$authorsFullname, authorEmail[$i]=$authorEmail[$i], authors_orcid[$i]=$authors_orcid[$i], affiliation[$i]=$affiliation[$i], affiliation_country[$i]=$affiliation_country[$i], affiliation_city[$i]=$affiliation_city[$i]");

                try {
                    if($authorEmail[$i] != ""){

                    // Frist Check the the Author Exists 
                    $stmt = $con->prepare("SELECT * FROM `submission_authors` WHERE `authors_email` = ? AND `submission_id` = ?");
                    if (!$stmt) {
                        print("Error executing statement: " . $stmt->error);

                        throw new Exception("Failed to prepare Author Submission statement: " . $stmt->error);

                    }
                    $stmt->bind_param("ss", $authorEmail[$i], $articleID);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {

                    } else {
                        $stmt = $con->prepare("INSERT INTO `submission_authors` (`submission_id`, `authors_fullname`, `authors_email`,`orcid_id`, `affiliations`, `affiliation_country`, `affiliation_city`) VALUES(?, ?, ?, ?, ?, ?,?)");
                        if (!$stmt) {
                            print("Error executing statement: " . $stmt->error);

                            throw new Exception("Failed to prepare  Author Insert statement: " . $stmt->error);
                        }
                        $stmt->bind_param("sssssss", $articleID, $authorsFullname, $authorEmail[$i], $authors_orcid[$i], $affiliation[$i], $affiliation_country[$i], $affiliation_city[$i]);
                        if (!$stmt->execute()) {
                            print("Error executing statement: " . $stmt->error);

                            throw new Exception("Failed to execute statement Author: " . $stmt->error);
                        }
                    }
                    // Create the NEw Co Authors Account 

                    CreateCoAuthor($authorsPrefix[$i], $authors_firstname[$i],$authors_lastname[$i], $authors_other_name[$i], $authorEmail[$i], $authors_orcid[$i], $affiliation[$i], $affiliation_country[$i], $affiliation_city[$i]);
                }
                    // Check for Errors
                } catch (Exception $e) {
                    $response = array('status' => 'error', 'message' => 'Error Submission Author:' . $e->getMessage());
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



               // For Logged in Author
            
       
               $LoggedInauthorsFullname = "$LoggedInauthorsPrefix $LoggedInauthors_firstname $LoggedInauthors_lastname $LoggedInauthors_other_name";
               try {
                   // Frist Check the the Author Exists 
                   $stmt = $con->prepare("SELECT * FROM `submission_authors` WHERE `authors_email` = ? AND `submission_id` = ?");
                   if (!$stmt) {
                       throw new Exception("Failed to prepare statement: " . $stmt->error);
       
                   }
                   $stmt->bind_param("ss", $LoggedInauthorEmail, $RevisionsId);
                   $stmt->execute();
                   $result = $stmt->get_result();
                   if ($result->num_rows > 0) {
       
                   } else {
                       $stmt = $con->prepare("INSERT INTO `submission_authors` (`submission_id`, `authors_fullname`, `authors_email`, `orcid_id`, `affiliations`, `affiliation_country`, `affiliation_city`) VALUES(?, ?,?, ?, ?, ?, ?)");
                       if (!$stmt) {
                           throw new Exception("Failed to prepare statement: " . $stmt->error);
                       }
                       $stmt->bind_param("sssssss", $RevisionsId, $LoggedInauthorsFullname, $LoggedInauthorEmail, $loggedIn_authors_ORCID, $LoggedInaffiliation, $LoggedInaffiliation_country, $LoggedInaffiliation_city);
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