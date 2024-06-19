<?php


include "../backend/cors.php";
include "../backend/db.php";

function MoveFile($outputFile, $designatedDirectory, $newFilename){
    // Move the final merged PDF to the designated folder
$designatedFolder = $designatedDirectory;
if (!file_exists($designatedFolder)) {
    mkdir($designatedFolder, 0777, true);
}
// rename($outputFile, $designatedFolder . $outputFile); 
move_uploaded_file(basename($outputFile["tmp_name"]), $outputFile);
rename($designatedDirectory . basename($outputFile["name"]), $$designatedDirectory.$newFilename);

}
$Article_id = $_POST["article_id"];
$Review_Id ="";
$Reviewed_by = $_POST["reviewd_by"];
$one_paragraph_comment = $_POST[""]; 

$one_paragraph_file = "";
$one_paragraph_file_main = $_FILES[""];
if(isset($one_paragraph_file_main)){
    $one_paragraph_file = "oneparagraph".time() . '-' . basename($one_paragraph_file_main["name"]);
    MoveFile($one_paragraph_file_main, "/uploads/reviews", $one_paragraph_file);
}


$general_comment = $_POST[""];
$general_comment_file = "";
$general_comment_file_main = $_FILES[""];
if(isset($general_comment_file_main)){
    $general_comment_file = "generalcomment".time() . '-' . basename($general_comment_file_main["name"]);
    MoveFile($general_comment_file_main, "/uploads/reviews", $general_comment_file);
}


$specific_comment = $_POST[""];
$specific_comment_file = "";
$specific_comment_file_main = $_FILES[""];
if(isset($specific_comment_file_main)){
    $specific_comment_file = "specificcomment".time() . '-' . basename($specific_comment_file_main["name"]);
    MoveFile($specific_comment_file_main, "/uploads/reviews", $specific_comment_file);
}

$accurately_reflect_manuscript_subject_score = $_POST[""];

$clearly_summarize_content_score = $_POST[""];
$presents_what_is_known_score = $_POST[""];
$gives_accurate_summary_score = $_POST[""];
$purpose_clear_score = $_POST[""];
$method_section_clear_score = $_POST[""];
$study_materials_clearly_described_score = $_POST[""];
$research_method_valid_score = $_POST[""];
$ethical_standards_score = $_POST[""];
$study_find_clearly_described_score = $_POST[""];
$result_presented_logical_score = $_POST[""];
$graphics_complement_result_score = $_POST[""];
$table_follow_specified_standards_score = $_POST[""];
$tables_add_value_or_distract_score = $_POST[""];
$issues_with_title_score = $_POST[""];
$manuscript_present_summary_of_key_findings_score = $_POST[""];
$manuscript_highlight_strength_of_study_score = $_POST[""];
$manuscript_compare_findings_score = $_POST[""];
$manuscript_discuss_meaning_score = $_POST[""];
$manuscript_describes_overall_story_score = $_POST[""];
$conclusions_reflect_achievement_score = $_POST[""];
$manuscript_describe_gaps_score = $_POST[""];
$referencing_accurate_score = $_POST[""];
$novelty_score = $_POST[""];
$quality_score = $_POST[""];
$scientific_accuracy_score = $_POST[""];
$overall_merit_score = $_POST[""];
$english_level_score = $_POST[""];
$overall_recommendation = $_POST[""];
$letter_to_editor = $_POST[""];
$reviewStatus = $_POST["review_status"];


if(isset($Article_id) && isset($Review_Id)){
    // Add the Review to the database 
    $stmt = $con->prepare("INSERT INTO `reviews`(article_id`, `review_id`, `reviewer_email`, `one_paragraph_comment`, `one_paragraph_file`, `general_comment`, `general_comment_file`, `specific_comment`, `specific_comment_file`, `accurately_reflect_manuscript_subject_score`, `clearly_summarize_content_score`, `presents_what_is_known_score`, `gives_accurate_summary_score`, `purpose_clear_score`, `method_section_clear_score`, `study_materials_clearly_described_score`, `research_method_valid_score`, `ethical_standards_score`, `study_find_clearly_described_score`, `result_presented_logical_score`, `graphics_complement_result_score`, `table_follow_specified_standards_score`, `tables_add_value_or_distract_score`, `issues_with_title_score`, `manuscript_present_summary_of_key_findings_score`, `manuscript_highlight_strength_of_study_score`, `manuscript_compare_findings_score`, `manuscript_discuss_meaning_score`, `manuscript_describes_overall_story_score`, `conclusions_reflect_achievement_score`, `manuscript_describe_gaps_score`, `referencing_accurate_score`, `novelty_score`, `quality_score`, `scientific_accuracy_score`, `overall_merit_score`, `english_level_score`, `overall_recommendation`, `letter_to_editor`, `review_status`) VALUES (? ,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
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
        $stmt = $con->prepare("UPDATE `submitted_for_review` SET `review_status` = 'review_submitted' WHERE `article_id` =?");
        $stmt->bind_param("s", $Article_id);
        $stmt->execute();
    $response = array("status" => "success", "message" => "Review Submitted successfully");
    echo json_encode($response);
    }else{
    $response = array("status" => "error", "message" => $stmt->error);
    echo json_encode($response);
    }
}else{
    $response = array("status" => "error", "message" => "incomplete Fields");
    echo json_encode($response);
}

