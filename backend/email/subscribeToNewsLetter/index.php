<?php
include "../../cors.php";
include "../../db.php";

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $email = filter_var($data["email"], FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Save the email to a file or a database
        // $file = 'subscribers.txt';
        // file_put_contents($file, $email.PHP_EOL, FILE_APPEND | LOCK_EX);

        // Check if the user has already subsribed 
        $stmt = $con->prepare("SELECT * FROM `news_letter_subscribers` WHERE `email` = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            echo json_encode(array("status"=>"error", "message" => "You are already subscribed to Our News Letter"));
        }else{
            $stmt = $con->prepare("INSERT INTO `news_letter_subscribers` (`email`) VALUES(?)");
            $stmt->bind_param("s", $email);
            if($stmt->execute()){
            echo json_encode(array("status"=>"success", "message" => "Thank you for Subscribing to our news letter"));
                
            }else{
                echo json_encode(array("status"=>"error", "message" => "An Error Occured please try again later"));
            }
        }

    

    } else {
        echo json_encode(array("status"=>"error", "message" => "Invalid Email address"));

    }
} else {
    echo json_encode(array("status"=>"error", "message" => "Invalid request method."));
}
