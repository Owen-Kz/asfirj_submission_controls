<?php
include "../backend/cors.php";
include "../backend/db.php";

function UpdateSubmissionsTable($article_id, $reviewStatus){
    include "../backend/db.php";
    $stmt = $con->prepare("UPDATE `submissions` SET `status` = ? WHERE `revision_id` = ?");
    if(!!$stmt){
        // print_r($stmt->error);
    }
    $stmt->bind_param("ss", $reviewStatus, $article_id);
    if($stmt->execute()){
        // print_r("Submission Updated Successfully");
    }else{
        // print_r("Could Not Execite Statement");
    }
}
function MoveFile($outputFile, $designatedDirectory, $newFilename){
    // Move the final merged PDF to the designated folder
$manuscriptFile = basename($_FILES[$outputFile]["name"]);
$targetFile = "../uploads/reviews/". $manuscriptFile;
if (!is_writable("../uploads/reviews/")) {
    die("Target directory is not writable.");
}
if (!file_exists("../uploads/reviews/")) {
    mkdir("../uploads/reviews/", 0777, true);
}
if (move_uploaded_file($_FILES[$outputFile]["tmp_name"], $targetFile)) {
// move_uploaded_file($outputFile["tmp_name"], $targetFile);
rename("../uploads/reviews/". $_FILES[$outputFile]["name"], "../uploads/reviews/".$newFilename);
// print_r("File Uploaded");
}else{
   echo "Could Not Upload File ".json_encode($_FILES[$outputFile]);
}


}
$Article_id = $_POST["article_id"];
$Review_Id = "ASFIRJ_rev_".date("Y")."_".bin2hex(random_bytes(7));
$Reviewed_by = $_POST["reviewed_by"];
$reviewerEmail = $Reviewed_by;
$one_paragraph_comment = $_POST["paragraph_summary"]; 

$one_paragraph_file = "";
$one_paragraph_file_main = $_FILES["paragraph_summary_file"];
if(isset($one_paragraph_file_main) && $one_paragraph_file_main["size"] > 0 && isset($_FILES["paragraph_summary_file"]["tmp_name"])){
    $one_paragraph_file = "oneparagraph". '-' . basename($one_paragraph_file_main["name"]);
    MoveFile("paragraph_summary_file", __DIR__."/uploads/reviews", $one_paragraph_file);
}


$general_comment = $_POST["general_comment"];
$general_comment_file = "";
$general_comment_file_main = $_FILES["general_comment_file"];

if(isset($general_comment_file_main) && $general_comment_file_main["size"] > 0 && isset($_FILES["general_comment_file"]["tmp_name"])){
    $general_comment_file = "generalcomment". '-' . basename($general_comment_file_main["name"]);

    MoveFile("general_comment_file",  __DIR__."/uploads/reviews", $general_comment_file);
}


$specific_comment = $_POST["specific_comment"];
$specific_comment_file = "";
$specific_comment_file_main = $_FILES["specific_comment_file"];
if(isset($specific_comment_file_main) && $specific_comment_file_main["size"] > 0 && isset($_FILES["specific_comment_file"]["tmp_name"])){

    $specific_comment_file = "specificcomment". '-' . basename($specific_comment_file_main["name"]);

    MoveFile("specific_comment_file",  __DIR__."/uploads/reviews", $specific_comment_file);
}

$accurately_reflect_manuscript_subject_score = $_POST["title_accuracy"];

$clearly_summarize_content_score = $_POST["abstract_summarize"];
$presents_what_is_known_score = $_POST["man_present"];
$gives_accurate_summary_score = $_POST["accurate_summary"];
$purpose_clear_score = $_POST["paper_purpose"];
$method_section_clear_score = $_POST["clear_manuscript"];
$study_materials_clearly_described_score = $_POST["clear_materials"];
$research_method_valid_score = $_POST["best_practice"];
$ethical_standards_score = $_POST["ethical_standards"];
$study_find_clearly_described_score = $_POST["study_find"];
$result_presented_logical_score = $_POST["result_present"];
$graphics_complement_result_score = $_POST["complemet_result"];
$table_follow_specified_standards_score = $_POST["specified_standard"];
$tables_add_value_or_distract_score = $_POST["distract_content"];
$issues_with_title_score = $_POST["man_issues"];
$manuscript_present_summary_of_key_findings_score = $_POST["key_findings"];
$manuscript_highlight_strength_of_study_score = $_POST["study_strenghts"];
$manuscript_compare_findings_score = $_POST["compare_manu"];
$manuscript_discuss_meaning_score = $_POST["discuss_manu"];
$manuscript_describes_overall_story_score = $_POST["describe_manu"];
$conclusions_reflect_achievement_score = $_POST["study_achievement"];
$manuscript_describe_gaps_score = $_POST["topic_gaps"];
$referencing_accurate_score = $_POST["topic_accuracy"];
$novelty_score = $_POST["novelty"];
$quality_score = $_POST["quality"];
$scientific_accuracy_score = $_POST["scientific_accuracy"];
$overall_merit_score = $_POST["overall_merit"];
$english_level_score = $_POST["english_level"];
$overall_recommendation = $_POST["recommendation"];
$letter_to_editor = $_POST["letter_to_editor"];
$reviewStatus = $_POST["review_status"];


if(isset($Article_id) && isset($Review_Id)){
    // Check if the reveiw Already Exists
    $stmt = $con->prepare("SELECT * FROM `reviews` WHERE `article_id` = ? AND `reviewer_email` = ?");
    if(!$stmt){
        echo $con->error;
    }
    $stmt->bind_param("ss", $Article_id, $reviewerEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = mysqli_num_rows($result);
    if($count>0){
        // "Update the Exisiting REview instread"
        $stmt = $con->prepare("UPDATE `reviews` SET `one_paragraph_comment`=?,`one_paragraph_file`=?,`general_comment`=?,`general_comment_file`=?,`specific_comment`=?,`specific_comment_file`=?,`accurately_reflect_manuscript_subject_score`=?,`clearly_summarize_content_score`=?,`presents_what_is_known_score`=?,`gives_accurate_summary_score`=?,`purpose_clear_score`=?,`method_section_clear_score`=?,`study_materials_clearly_described_score`=?,`research_method_valid_score`=?,`ethical_standards_score`=?,`study_find_clearly_described_score`=?,`result_presented_logical_score`=?,`graphics_complement_result_score`=?,`table_follow_specified_standards_score`=?,`tables_add_value_or_distract_score`=?,`issues_with_title_score`=?,`manuscript_present_summary_of_key_findings_score`=?,`manuscript_highlight_strength_of_study_score`=?,`manuscript_compare_findings_score`=?,`manuscript_discuss_meaning_score`=?,`manuscript_describes_overall_story_score`=?,`conclusions_reflect_achievement_score`='?,`manuscript_describe_gaps_score`=?,`referencing_accurate_score`=?,`novelty_score`=?,`quality_score`=?,`scientific_accuracy_score`=?,`overall_merit_score`=?,`english_level_score`=?,`overall_recommendation`=?,`letter_to_editor`=?,`review_status`=?,`date_created`=? WHERE `article_id` = ? AND `reviewer_email` = ?");
        $stmt->bind_param("sssssssssssssssssssssssssssssssssssssss",  $one_paragraph_comment,
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
        $stmt->bind_param("sss", $reviewStatus, $Article_id,$Reviewed_by);
        $stmt->execute();
        if($reviewStatus === "review_submitted"){
          UpdateSubmissionsTable($Article_id, $reviewStatus);
        }
        $response = array("status" => "success", "message" => "Review Updated successfully");
        echo json_encode($response);
        }else{
                  // update the submission status in the table 
  

        $response = array("status" => "error", "message" => $stmt->error);
        echo json_encode($response);
        }
  
    }else{
        // If this is the firsttime the review qa initiated
    // Add the Review to the database 
    $stmt = $con->prepare("INSERT INTO `reviews` (`article_id`, `review_id`, `reviewer_email`, `one_paragraph_comment`, `one_paragraph_file`, `general_comment`, `general_comment_file`, `specific_comment`, `specific_comment_file`, `accurately_reflect_manuscript_subject_score`, `clearly_summarize_content_score`, `presents_what_is_known_score`, `gives_accurate_summary_score`, `purpose_clear_score`, `method_section_clear_score`, `study_materials_clearly_described_score`, `research_method_valid_score`, `ethical_standards_score`, `study_find_clearly_described_score`, `result_presented_logical_score`, `graphics_complement_result_score`, `table_follow_specified_standards_score`, `tables_add_value_or_distract_score`, `issues_with_title_score`, `manuscript_present_summary_of_key_findings_score`, `manuscript_highlight_strength_of_study_score`, `manuscript_compare_findings_score`, `manuscript_discuss_meaning_score`, `manuscript_describes_overall_story_score`, `conclusions_reflect_achievement_score`, `manuscript_describe_gaps_score`, `referencing_accurate_score`, `novelty_score`, `quality_score`, `scientific_accuracy_score`, `overall_merit_score`, `english_level_score`, `overall_recommendation`, `letter_to_editor`, `review_status`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssssssssssssssssssssssssssssssssssss", $Article_id,
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
        $stmt = $con->prepare("UPDATE `submitted_for_review` SET `status` = ? WHERE `article_id` =?");
        $stmt->bind_param("ss", $reviewStatus, $Article_id);
        $stmt->execute();
            // update the submission status in the table 
            if($reviewStatus === "review_submitted"){
                $stmt = $con->prepare("UPDATE `invitations` SET `invitation_status` = ? WHERE `invitation_link` =? AND `invited_user` = ?");
                $stmt->bind_param("sss", $reviewStatus, $Article_id, $reviewerEmail);
                $stmt->execute();
               UpdateSubmissionsTable($Article_id, $reviewStatus);
            }
    $response = array("status" => "success", "message" => "Review Submitted successfully");
    echo json_encode($response);
    }else{
    $response = array("status" => "error", "message" => $stmt->error);
    echo json_encode($response);
    }
}
}else{
    $response = array("status" => "error", "message" => "incomplete Fields");
    echo json_encode($response);
}

