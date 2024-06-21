<?php

include "../cors.php";
include "../db.php";

session_start();

$data = json_decode(file_get_contents("php://input"), true);

// Access the values
$email_post = $data['email'];
$pass = $data['pass'];

if(isset($pass) && isset($email_post)){

    $userID = mysqli_real_escape_string($con, $email_post);
    // $password = md5($pass);
    
    // $password = $_POST["password"];

    $stmt = $con->prepare("SELECT * FROM `editors` WHERE `email` = ? LIMIT 1");
    $stmt->bind_param("s", $userID);
    
    if($stmt->execute()){

    $result = $stmt->get_result();
    
    // $run_query = mysqli_query($con,$sql);
    $run_query = $result;    
    $count = mysqli_num_rows($run_query);
    
   
	//if user record is available in database then $count will be equal to 1

	if($count == 1){
        // Get and verify the users password if the account exists  
        $row = mysqli_fetch_array($run_query);
        $storedHashedPassword = $row["password"];
        if((password_verify($pass, $storedHashedPassword)) ){

        // $_SESSION["user_id"] = $row["id"];
    
		// $_SESSION["user_name"] = $row["username"];

        // $_SESSION["account_type"] = $row["acct_type"];

        // $_SESSION["user_email"] = $row["email"];

        $ip_add = getenv("REMOTE_ADDR"); 

        // $HOTEL =  $_SESSION["Hotel_name"];

        $response = array('status' => 'success', 'message' => 'Logged in successfully', 'user_data' => $row, 'ip'=> $ip_add);
        echo json_encode($response);
    }else{
        $response = array('status' => 'error', 'message' => 'Invalid Credentials', 'user_data' => "[]");
        echo json_encode($response);
    }
    
    }
    else {
        $response = array('status' => 'error', 'message' => 'User not found', 'user_data' => '$row');
        echo json_encode($response);
    }
    
}else{
    echo "Error: " . $stmt->error;
}
}else{
    $response = array('status' => 'error', 'message' => 'Fill all fields', 'user_data' => '$row');
    echo json_encode($response);
}



