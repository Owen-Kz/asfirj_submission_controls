<?php

function AddSuggestedReviewers($article_id, $fullname, $affiliation, $affiliation_country, $affiliation_city, $email){
    include "../backend/db.php";
    $stmt = $con->prepare("SELECT * FROM `suggested_reviewers` WHERE `email` = ? AND `article_id` = ?");

    if(!$stmt){
        return false;
    }
    $stmt->bind_param("ss", $email, $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $stmt = $con->prepare("UPDATE `suggested_reviewers` SET `fullname` = ? ,`affiliation` = ?, `affiliation_country` =?, `affiliation_city` =?");
        if(!$stmt){
            return false;
        }
        $stmt->bind_param("ssss", $fullname, $affiliation, $affiliation_country, $affiliation_city);
        $stmt->execute();
        return true;
    }else{
        $stmt = $con->prepare("INSERT INTO `suggested_reviewers` (`article_id`, `fullname`, `email`, `affiliation`, `affiliation_country`, `affiliation_city`) VALUES(?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $article_id, $fullname, $email, $affiliation, $affiliation_country, $affiliation_city);
        if(!$stmt){
            return false;
        }
        $stmt->execute();
        return true;
    }
}