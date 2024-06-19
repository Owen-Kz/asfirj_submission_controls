<?php

function CheckAccountExists($authorsPrefix, $authors_firstname, $authors_lastname, $authors_other_name, $authorsEmail, $affiliation, $affiliation_country,$affiliation_city){
include "./db.php";
include "./sendNewAccountEmail.php;";

$password = bin2hex(random_bytes(6)); 
$pass = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` = ?");

    if(!$stmt){
        print_r($con->errorInfo());
    }else{
        $stmt->bind_param("s", $authorsEmail);
        
        if($stmt->execute()){
            $result = $stmt->get_result();
            $count = mysqli_num_rows($result);
            if($count > 0){
                echo 'Account Already Exists';
                
            }else{
                // Create the Account if the account does not exist 
                $stmt = $con->prepare("INSERT INTO `authors_account` (`prefix`, `email`, `firstname`, `lastname`, `othername`, `affiliations`, `affiliation_country`, `affiliation_city`, `password`) VALUES(?,?,?,?,?,?,?,?,?) ");
                if(!$stmt){
                    echo "Could Not prepare insett $stmt->error";
                }else{
                    $stmt->bind_param("sssssssss", $prefix, $authorsEmail,$authors_firstname, $authors_lastname, $authors_other_name, $affiliation, $affiliation_country,$affiliation_city, $pass);
                    if($stmt->execute()){
                        SendAccountEmail($authorsEmail, $password);
                        echo "AccountCreatedSuccessfully";
                    }else{
                    echo "$stmt->error";
                    }
                }
            }
        }else{
            echo "Could Not Execute statment $stmt->error";
        }
    }

}