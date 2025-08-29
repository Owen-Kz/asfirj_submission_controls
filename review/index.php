<?php
include "../backend/cors.php";
include "../backend/db.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function UpdateSubmissionsTable($article_id, $reviewStatus){
    include "../backend/db.php";
    $stmt = $con->prepare("UPDATE `submissions` SET `status` = ? WHERE `revision_id` = ?");
    if(!$stmt){
        error_log("Prepare failed: " . $con->error);
        return false;
    }
    $stmt->bind_param("ss", $reviewStatus, $article_id);
    if($stmt->execute()){
        return true;
    }else{
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
}

function MoveFile($fileInputName, $targetDirectory, $newFilename) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Create directory if it doesn't exist
    if (!file_exists($targetDirectory)) {
        mkdir($targetDirectory, 0777, true);
    }
    
    $tempFile = $_FILES[$fileInputName]["tmp_name"];
    $targetFile = $targetDirectory . '/' . $newFilename;
    
    if (move_uploaded_file($tempFile, $targetFile)) {
        return $newFilename;
    } else {
        error_log("Failed to move uploaded file: " . $fileInputName);
        return false;
    }
}

// Check if invitation is valid
function isInvitationValid($article_id, $reviewer_email) {
    include "../backend/db.php";
    
    // Check if invitation exists and is still valid
    $stmt = $con->prepare("SELECT * FROM `submitted_for_review` WHERE `article_id` = ? AND `reviewer_email` = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $con->error);
        return array("valid" => false, "message" => "Database error");
    }
    
    $stmt->bind_param("ss", $article_id, $reviewer_email);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return array("valid" => false, "message" => "Database error");
    }
    
    $result = $stmt->get_result();
    
    if(mysqli_num_rows($result) === 0) {
        return array("valid" => false, "message" => "No invitation found for this article");
    }
    
    $invitation = $result->fetch_assoc();
    
    // Check if invitation status is correct
    if($invitation['status'] !== 'review_invitation_accepted') {
        return array("valid" => false, "message" => "Invitation not accepted or already processed");
    }
    
    // Check if invitation has expired (more than 14 days old)
    $invitationDate = new DateTime($invitation['date_submitted']);
    $currentDate = new DateTime();
    $daysDifference = $currentDate->diff($invitationDate)->days;
    
    if($daysDifference > 14) {
        return array("valid" => false, "message" => "Invitation has expired (more than 14 days old)");
    }
    
    return array("valid" => true, "invitation" => $invitation);
}

// Check if review already submitted
function isReviewAlreadySubmitted($article_id, $reviewer_email) {
    include "../backend/db.php";
    
    $stmt = $con->prepare("SELECT * FROM `reviews` WHERE `article_id` = ? AND `reviewer_email` = ? AND `review_status` = 'review_submitted'");
    if (!$stmt) {
        error_log("Prepare failed: " . $con->error);
        return false;
    }
    
    $stmt->bind_param("ss", $article_id, $reviewer_email);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result();
    
    return mysqli_num_rows($result) > 0;
}

// Log received data for debugging
// error_log("POST data: " . print_r($_POST, true));
// error_log("FILES data: " . print_r($_FILES, true));

// Check if required fields are present
if (!isset($_POST["article_id"]) || !isset($_POST["reviewed_by"]) || !isset($_POST["review_status"])) {
    $response = array("status" => "error", "message" => "Missing required fields");
    echo json_encode($response);
    exit();
}

$Article_id = $_POST["article_id"];
$Review_Id = "ASFIRJ_rev_".date("Y")."_".bin2hex(random_bytes(7));
$Reviewed_by = $_POST["reviewed_by"];
$reviewerEmail = $Reviewed_by;

// Validate invitation before processing
$invitationCheck = isInvitationValid($Article_id, $reviewerEmail);
if(!$invitationCheck['valid']) {
    $response = array("status" => "error", "message" => $invitationCheck['message']);
    echo json_encode($response);
    exit();
}

// Check if review already submitted
if(isReviewAlreadySubmitted($Article_id, $reviewerEmail)) {
    $response = array("status" => "error", "message" => "Review has already been submitted for this article");
    echo json_encode($response);
    exit();
}

// Process the review data with proper null checks
$one_paragraph_comment = isset($_POST["paragraph_summary"]) ? $_POST["paragraph_summary"] : "";
$one_paragraph_file = "";

if(isset($_FILES["paragraph_summary_file"]) && $_FILES["paragraph_summary_file"]["size"] > 0) {
    $one_paragraph_file = "oneparagraph_" . $Article_id . "_" . basename($_FILES["paragraph_summary_file"]["name"]);
    $moved = MoveFile("paragraph_summary_file", "../uploads/reviews", $one_paragraph_file);
    if (!$moved) {
        $one_paragraph_file = "";
    }
}

$general_comment = isset($_POST["general_comment"]) ? $_POST["general_comment"] : "";
$general_comment_file = "";

if(isset($_FILES["general_comment_file"]) && $_FILES["general_comment_file"]["size"] > 0) {
    $general_comment_file = "generalcomment_" . $Article_id . "_" . basename($_FILES["general_comment_file"]["name"]);
    $moved = MoveFile("general_comment_file", "../uploads/reviews", $general_comment_file);
    if (!$moved) {
        $general_comment_file = "";
    }
}

$specific_comment = isset($_POST["specific_comment"]) ? $_POST["specific_comment"] : "";
$specific_comment_file = "";

if(isset($_FILES["specific_comment_file"]) && $_FILES["specific_comment_file"]["size"] > 0) {
    $specific_comment_file = "specificcomment_" . $Article_id . "_" . basename($_FILES["specific_comment_file"]["name"]);
    $moved = MoveFile("specific_comment_file", "../uploads/reviews", $specific_comment_file);
    if (!$moved) {
        $specific_comment_file = "";
    }
}

// Get all the score values with proper null checks
$accurately_reflect_manuscript_subject_score = isset($_POST["title_accuracy"]) ? $_POST["title_accuracy"] : 0;
$clearly_summarize_content_score = isset($_POST["abstract_summarize"]) ? $_POST["abstract_summarize"] : 0;
$presents_what_is_known_score = isset($_POST["man_present"]) ? $_POST["man_present"] : 0;
$gives_accurate_summary_score = isset($_POST["accurate_summary"]) ? $_POST["accurate_summary"] : 0;
$purpose_clear_score = isset($_POST["paper_purpose"]) ? $_POST["paper_purpose"] : 0;
$method_section_clear_score = isset($_POST["clear_manuscript"]) ? $_POST["clear_manuscript"] : 0;
$study_materials_clearly_described_score = isset($_POST["clear_materials"]) ? $_POST["clear_materials"] : 0;
$research_method_valid_score = isset($_POST["best_practice"]) ? $_POST["best_practice"] : 0;
$ethical_standards_score = isset($_POST["ethical_standards"]) ? $_POST["ethical_standards"] : 0;
$study_find_clearly_described_score = isset($_POST["study_find"]) ? $_POST["study_find"] : 0;
$result_presented_logical_score = isset($_POST["result_present"]) ? $_POST["result_present"] : 0;
$graphics_complement_result_score = isset($_POST["complemet_result"]) ? $_POST["complemet_result"] : 0;
$table_follow_specified_standards_score = isset($_POST["specified_standard"]) ? $_POST["specified_standard"] : 0;
$tables_add_value_or_distract_score = isset($_POST["distract_content"]) ? $_POST["distract_content"] : 0;
$issues_with_title_score = isset($_POST["man_issues"]) ? $_POST["man_issues"] : 0;
$manuscript_present_summary_of_key_findings_score = isset($_POST["key_findings"]) ? $_POST["key_findings"] : 0;
$manuscript_highlight_strength_of_study_score = isset($_POST["study_strenghts"]) ? $_POST["study_strenghts"] : 0;
$manuscript_compare_findings_score = isset($_POST["compare_manu"]) ? $_POST["compare_manu"] : 0;
$manuscript_discuss_meaning_score = isset($_POST["discuss_manu"]) ? $_POST["discuss_manu"] : 0;
$manuscript_describes_overall_story_score = isset($_POST["describe_manu"]) ? $_POST["describe_manu"] : 0;
$conclusions_reflect_achievement_score = isset($_POST["study_achievement"]) ? $_POST["study_achievement"] : 0;
$manuscript_describe_gaps_score = isset($_POST["topic_gaps"]) ? $_POST["topic_gaps"] : 0;
$referencing_accurate_score = isset($_POST["topic_accuracy"]) ? $_POST["topic_accuracy"] : 0;
$novelty_score = isset($_POST["novelty"]) ? $_POST["novelty"] : 0;
$quality_score = isset($_POST["quality"]) ? $_POST["quality"] : 0;
$scientific_accuracy_score = isset($_POST["scientific_accuracy"]) ? $_POST["scientific_accuracy"] : 0;
$overall_merit_score = isset($_POST["overall_merit"]) ? $_POST["overall_merit"] : 0;
$english_level_score = isset($_POST["english_level"]) ? $_POST["english_level"] : 0;
$overall_recommendation = isset($_POST["recommendation"]) ? $_POST["recommendation"] : "";
$letter_to_editor = isset($_POST["letter_to_editor"]) ? $_POST["letter_to_editor"] : "";
$reviewStatus = $_POST["review_status"];

// Check if the review Already Exists
$stmt = $con->prepare("SELECT * FROM `reviews` WHERE `article_id` = ? AND `reviewer_email` = ?");
if(!$stmt){
    $response = array("status" => "error", "message" => "Database error: " . $con->error);
    echo json_encode($response);
    exit();
}

$stmt->bind_param("ss", $Article_id, $reviewerEmail);
if(!$stmt->execute()){
    $response = array("status" => "error", "message" => "Database error: " . $stmt->error);
    echo json_encode($response);
    exit();
}

$result = $stmt->get_result();
$count = mysqli_num_rows($result);

if($count > 0){
    // Update the Existing Review
    $stmt = $con->prepare("UPDATE `reviews` SET `one_paragraph_comment`=?,`one_paragraph_file`=?,`general_comment`=?,`general_comment_file`=?,`specific_comment`=?,`specific_comment_file`=?,`accurately_reflect_manuscript_subject_score`=?,`clearly_summarize_content_score`=?,`presents_what_is_known_score`=?,`gives_accurate_summary_score`=?,`purpose_clear_score`=?,`method_section_clear_score`=?,`study_materials_clearly_described_score`=?,`research_method_valid_score`=?,`ethical_standards_score`=?,`study_find_clearly_described_score`=?,`result_presented_logical_score`=?,`graphics_complement_result_score`=?,`table_follow_specified_standards_score`=?,`tables_add_value_or_distract_score`=?,`issues_with_title_score`=?,`manuscript_present_summary_of_key_findings_score`=?,`manuscript_highlight_strength_of_study_score`=?,`manuscript_compare_findings_score`=?,`manuscript_discuss_meaning_score`=?,`manuscript_describes_overall_story_score`=?,`conclusions_reflect_achievement_score`=?,`manuscript_describe_gaps_score`=?,`referencing_accurate_score`=?,`novelty_score`=?,`quality_score`=?,`scientific_accuracy_score`=?,`overall_merit_score`=?,`english_level_score`=?,`overall_recommendation`=?,`letter_to_editor`=?,`review_status`=?,`date_created`=NOW() WHERE `article_id` = ? AND `reviewer_email` = ?");
    
    if(!$stmt){
        $response = array("status" => "error", "message" => "Database error: " . $con->error);
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_param("sssssssssssssssssssssssssssssssssssssss",  
        $one_paragraph_comment,
        $one_paragraph_file,
        $general_comment,
        $general_comment_file,
        $specific_comment,
        $specific_comment_file,
        $accurately_reflect_manuscript_subject_score,
        $clearly_summarize_content_score,
        $presents_what_is_known_score,
        $gives_accurate_summary_score,
        $purpose_clear_score,
        $method_section_clear_score,
        $study_materials_clearly_described_score,
        $research_method_valid_score,
        $ethical_standards_score,
        $study_find_clearly_described_score,
        $result_presented_logical_score,
        $graphics_complement_result_score,
        $table_follow_specified_standards_score,
        $tables_add_value_or_distract_score,
        $issues_with_title_score,
        $manuscript_present_summary_of_key_findings_score,
        $manuscript_highlight_strength_of_study_score,
        $manuscript_compare_findings_score,
        $manuscript_discuss_meaning_score,
        $manuscript_describes_overall_story_score,
        $conclusions_reflect_achievement_score,
        $manuscript_describe_gaps_score,
        $referencing_accurate_score,
        $novelty_score,
        $quality_score,
        $scientific_accuracy_score,
        $overall_merit_score,
        $english_level_score,
        $overall_recommendation,
        $letter_to_editor,
        $reviewStatus,
        $Article_id,
        $Reviewed_by     
    );
    
    if($stmt->execute()){
        $stmt = $con->prepare("UPDATE `submitted_for_review` SET `status` = ? WHERE `article_id` =? AND `reviewer_email` = ?");
        if($stmt){
            $stmt->bind_param("sss", $reviewStatus, $Article_id, $Reviewed_by);
            $stmt->execute();
        }
        
        if($reviewStatus === "review_submitted"){
            UpdateSubmissionsTable($Article_id, $reviewStatus);
        }
        
        $response = array("status" => "success", "message" => "Review Updated successfully");
        echo json_encode($response);
    }else{
        $response = array("status" => "error", "message" => "Database error: " . $stmt->error);
        echo json_encode($response);
    }
}else{
    // If this is the first time the review is initiated
    $stmt = $con->prepare("INSERT INTO `reviews` (`article_id`, `review_id`, `reviewer_email`, `one_paragraph_comment`, `one_paragraph_file`, `general_comment`, `general_comment_file`, `specific_comment`, `specific_comment_file`, `accurately_reflect_manuscript_subject_score`, `clearly_summarize_content_score`, `presents_what_is_known_score`, `gives_accurate_summary_score`, `purpose_clear_score`, `method_section_clear_score`, `study_materials_clearly_described_score`, `research_method_valid_score`, `ethical_standards_score`, `study_find_clearly_described_score`, `result_presented_logical_score`, `graphics_complement_result_score`, `table_follow_specified_standards_score`, `tables_add_value_or_distract_score`, `issues_with_title_score`, `manuscript_present_summary_of_key_findings_score`, `manuscript_highlight_strength_of_study_score`, `manuscript_compare_findings_score`, `manuscript_discuss_meaning_score`, `manuscript_describes_overall_story_score`, `conclusions_reflect_achievement_score`, `manuscript_describe_gaps_score`, `referencing_accurate_score`, `novelty_score`, `quality_score`, `scientific_accuracy_score`, `overall_merit_score`, `english_level_score`, `overall_recommendation`, `letter_to_editor`, `review_status`, `date_created`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
    
    if(!$stmt){
        $response = array("status" => "error", "message" => "Database error: " . $con->error);
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_param("ssssssssssssssssssssssssssssssssssssssss", 
        $Article_id,
        $Review_Id,
        $Reviewed_by,
        $one_paragraph_comment,
        $one_paragraph_file,
        $general_comment,
        $general_comment_file,
        $specific_comment,
        $specific_comment_file,
        $accurately_reflect_manuscript_subject_score,
        $clearly_summarize_content_score,
        $presents_what_is_known_score,
        $gives_accurate_summary_score,
        $purpose_clear_score,
        $method_section_clear_score,
        $study_materials_clearly_described_score,
        $research_method_valid_score,
        $ethical_standards_score,
        $study_find_clearly_described_score,
        $result_presented_logical_score,
        $graphics_complement_result_score,
        $table_follow_specified_standards_score,
        $tables_add_value_or_distract_score,
        $issues_with_title_score,
        $manuscript_present_summary_of_key_findings_score,
        $manuscript_highlight_strength_of_study_score,
        $manuscript_compare_findings_score,
        $manuscript_discuss_meaning_score,
        $manuscript_describes_overall_story_score,
        $conclusions_reflect_achievement_score,
        $manuscript_describe_gaps_score,
        $referencing_accurate_score,
        $novelty_score,
        $quality_score,
        $scientific_accuracy_score,
        $overall_merit_score,
        $english_level_score,
        $overall_recommendation,
        $letter_to_editor,
        $reviewStatus
    );

    if($stmt->execute()){
        $stmt = $con->prepare("UPDATE `submitted_for_review` SET `status` = ? WHERE `article_id` =? AND `reviewer_email` = ?");
        if($stmt){
            $stmt->bind_param("sss", $reviewStatus, $Article_id, $Reviewed_by);
            $stmt->execute();
        }
        
        if($reviewStatus === "review_submitted"){
            $stmt = $con->prepare("UPDATE `invitations` SET `invitation_status` = ? WHERE `invitation_link` =? AND `invited_user` = ?");
            if($stmt){
                $stmt->bind_param("sss", $reviewStatus, $Article_id, $reviewerEmail);
                $stmt->execute();
            }
            UpdateSubmissionsTable($Article_id, $reviewStatus);
        }
        
        $response = array("status" => "success", "message" => "Review Submitted successfully");
        echo json_encode($response);
    }else{
        $response = array("status" => "error", "message" => "Database error: " . $stmt->error);
        echo json_encode($response);
    }
}
?>