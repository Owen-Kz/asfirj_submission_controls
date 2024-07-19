<?php

function AddSubmissionKeywords($articleId, $keyword){
    include "../backend/db.php";
    // Check if the Keyword already Exists 
    $stmt = $con->prepare("SELECT * FROM `submission_keywords` WHERE `keyword` = ? AND `article_id` = ?");
    if(!$stmt){
        return false;
    }
    $stmt->bind_param("ss", $keyword, $articleId);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        return true;
    }else{
        $stmt = $con->prepare("INSERT INTO `submission_keywords` (`article_id`, `keyword`) VALUES(?,?)");
        $stmt->bind_param("ss", $articleId, $keyword);
        if(!$stmt){
            return false;
        }else{
        $stmt->execute();
        return true;
        }
    }
}