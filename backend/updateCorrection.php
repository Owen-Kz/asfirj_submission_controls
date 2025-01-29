<?php
function updateCorrectionCount($correctionCount, $articleID){
    include "../backend/db.php";
    $stmt= $con->prepare("UPDATE submissions SET corrections_count = ? WHERE article_id = ?");
    $stmt->bind_param("ss", $correctionCount, $articleID);

    $stmt->execute();
}
function UpdateCorrection(
    $type, $RevisionsId, $correctionCount, $discipline, $title, $combinedFilename, $combinedDocFile, 
    $cover_letter_file, $abstract, $corresponding_author, $articleID, $submissionStatus, 
    $tablesName, $figuresName, $abstractFileName, $supplementsFileName, $authorsPrefix, 
    $authorEmail, $authors_firstname, $authors_lastname, $authors_other_name, $authors_orcid, 
    $affiliation, $affiliation_country, $affiliation_city, $keywords, $suggested_reviewer_fullname, 
    $suggested_reviewer_affiliation, $suggested_reviewer_country, $suggested_reviewer_city, 
    $suggestedReviewerEmail, $LoggedInauthorsPrefix, $LoggedInauthors_firstname, 
    $LoggedInauthors_lastname, $LoggedInauthors_other_name, $LoggedInauthorEmail, 
    $loggedIn_authors_ORCID, $LoggedInaffiliation, $LoggedInaffiliation_country, 
    $LoggedInaffiliation_city, $trackedManuscriptFileName, $membership_id, $previousManuscriptID
) {
    include "../backend/db.php";
    include "../backend/addSubmissoinKeywords.php";
    include "../backend/addSuggestedReviewers.php";
    include "../backend/createCoAuthor.php";
    require_once "../backend/sendNewSubmissionEmail.php";
    require_once "../backend/sendEMailToHandler.php";


        $stmt = $con->prepare("SELECT * FROM submissions WHERE (status = 'correction_saved') AND corresponding_authors_email = ? AND `article_id` = ? ");
        if (!$stmt) {
            die(json_encode(["status" => "error", "message" => "SQL Error: " . $stmt->error]));
        }
        $stmt->bind_param("ss", $corresponding_author, $previousManuscriptID);

    // Get the main submission ID
    $mainSubmissionId = $articleID;
    // if ($submissionStatus === "submitted") {
    //     SendNewSubmissionEmail($corresponding_author, $title, $RevisionsId);
    //     SendEmailToHandler("submissions@asfirj.org", $title, $RevisionsId);
    // }
 
    $stmt->execute();
    $result = $stmt->get_result();
    // $mainSubmissionId = ($result->num_rows > 0) ? $result->fetch_assoc()["article_id"] : $articleID;
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
    $statusMain = $row["status"];
    $previousManuscriptID = $row["previous_manuscript_id"];
    $submissionStatusMain = ($submissionStatus === "correction_saved") ? "correction_saved" : "submitted";


    // if($statusMain === "correction_saved" && $previousManuscriptID === $articleID){
        $previousRevisionID = $row["revision_id"];

        $stmtIST = $con->prepare("UPDATE submissions SET article_type = ?, discipline = ?, title = ?, manuscript_file = ?, document_file = ?, cover_letter_file = ?, abstract = ?, corresponding_authors_email = ?, tables = ?, figures = ?, graphic_abstract = ?, supplementary_material = ?, status = ?, tracked_manuscript_file = ?, previous_manuscript_id = ? WHERE revision_id = ?");
        $stmtIST->bind_param("ssssssssssssssss", $type, $discipline, $title, $combinedFilename, $combinedDocFile, $cover_letter_file, $abstract, $corresponding_author, $tablesName, $figuresName, $abstractFileName, $supplementsFileName, $submissionStatusMain, $trackedManuscriptFileName, $previousManuscriptID, $previousRevisionID);
        $stmtIST->execute();
        // updateCorrectionCount($correctionCount, $articleID);

        echo json_encode(["status" => "success", "message" => "Submission Updated Successfully $submissionStatus"]);
    // }else{
   
    // }

   

    foreach ($authorEmail as $i => $email) {
        if (!empty($email)) {
            $authorsFullname = "$authorsPrefix[$i] $authors_firstname[$i] $authors_lastname[$i] $authors_other_name[$i]";
            
            $stmt = $con->prepare("SELECT 1 FROM submission_authors WHERE authors_email = ? AND submission_id = ?");
            $stmt->bind_param("ss", $email, $mainSubmissionId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $stmt = $con->prepare("INSERT INTO submission_authors (submission_id, authors_fullname, authors_email, orcid_id, affiliations, affiliation_country, affiliation_city, asfi_membership_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssss", $mainSubmissionId, $authorsFullname, $email, $authors_orcid[$i], $affiliation[$i], $affiliation_country[$i], $affiliation_city[$i], $membership_id[$i]);
                $stmt->execute();
            }
            if ($submissionStatus === "submitted") {
                SendNewSubmissionEmail($email, $title, $mainSubmissionId);
                CreateCoAuthor($authorsPrefix[$i], $authors_firstname[$i], $authors_lastname[$i], $authors_other_name[$i], $email, $authors_orcid[$i], $affiliation[$i], $affiliation_country[$i], $affiliation_city[$i]);
            }
        }
    }
    
    foreach ($keywords as $keyword) {
        if (!empty($keyword)) {
            AddSubmissionKeywords($mainSubmissionId, $keyword);
        }
    }

    foreach ($suggestedReviewerEmail as $i => $email) {
        if (!empty($email)) {
            AddSuggestedReviewers($mainSubmissionId, $suggested_reviewer_fullname[$i], $suggested_reviewer_affiliation[$i], $suggested_reviewer_country[$i], $suggested_reviewer_city[$i], $email);
        }
    }
}else{
    $stmt = $con->prepare("INSERT INTO submissions (article_type, discipline, title, manuscript_file, document_file, cover_letter_file, tables, figures, graphic_abstract, supplementary_material, abstract, corresponding_authors_email, article_id, revision_id, status, tracked_manuscript_file, previous_manuscript_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssssssssss", $type, $discipline, $title, $combinedFilename, $combinedDocFile, $cover_letter_file, $tablesName, $figuresName, $abstractFileName, $supplementsFileName, $abstract, $corresponding_author, $previousManuscriptID, $RevisionsId, $submissionStatus, $trackedManuscriptFileName, $previousManuscriptID);
    $stmt->execute();

    updateCorrectionCount($correctionCount, $articleID);
    echo json_encode(["status" => "success", "message" => "Submission Created Successfully $submissionStatus"]);

}
}
