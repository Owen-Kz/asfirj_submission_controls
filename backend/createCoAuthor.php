<?php

function CreateCoAuthor($prefix, $firstname, $lastname, $othername, $email, $orcid, $affiliations, $affiliationCountry, $affiliationCity){
    include "../backend/db.php";
    require_once "../backend/sendCoAuthorEmail.php";
    $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` = ?");
    if (!$stmt) {
        print("Error executing statement: " . $stmt->error);

        // throw new Exception("Failed to prepare Author Submission statement: " . $stmt->error);

    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows >0){
        return true;
    }else{
        $generatedPassword = bin2hex(random_bytes(8));
        $encryptedPassword = password_hash($generatedPassword, PASSWORD_DEFAULT);
        $stmt = $con->prepare("INSERT INTO `authors_account`(`prefix`, `firstname`, `lastname`, `othername`, `email`, `orcid_id`,`affiliations`, `affiliation_country`, `affiliation_city`, `password`) VALUES(?,?,?,?,?,?,?,?,?,?)");
        if (!$stmt) {
            print("Error executing statement: " . $stmt->error);

            // throw new Exception("Failed to prepare Author Submission statement: " . $stmt->error);

        }
        $stmt->bind_param("ssssssssss", $prefix, $firstname, $lastname, $othername, $email, $orcid, $affiliations, $affiliationCountry, $affiliationCity, $encryptedPassword);
        if($stmt->execute()){
            SendCoAuthorEmail($email, $generatedPassword);
        }
    }
}