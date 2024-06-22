<?php

function SendWelcomeEmail($RecipientEmail){

    require_once __DIR__ .'/../vendor/autoload.php';// If you're using Composer (recommended)
    // Comment out the above line if not using Composer
    // require("<PATH TO>/sendgrid-php.php");
    // If not using Composer, uncomment the above line and
    // download sendgrid-php.zip from the latest release here,
    // replacing <PATH TO> with the path to the sendgrid-php.php file,
    // which is included in the download:
    // https://github.com/sendgrid/sendgrid-php/releases
    // Inmport Environment Variables
    include __DIR__ .'/exportENV.php';
    include __DIR__ .'/db.php';

$api = $_ENV['SENDGRID_API_KEY'];
$senderEmail = $_ENV["SENDGRID_EMAIL"];



if($RecipientEmail){

$stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` = ?");
$stmt->bind_param("s", $RecipientEmail);
$stmt->execute();
$result = $stmt->get_result();
$run_query = $result; 
$count = mysqli_num_rows($run_query);


//if user record is available in database then $count will be equal to 1
if($count > 0){
    $row = mysqli_fetch_array($run_query);
    $email = $row["email"];
    $prefix = $row["prefix"];
    $RecipientName = $row["firstname"];

    $encryptedButton = md5($RecipientEmail);

$sendgrid = new \SendGrid($api);
try {
 
    // print $response->statusCode() . "\n";
    // print_r($response->headers());
    // print $response->body() . "\n";
    $subject = "ASFIRJ Account Created";
    $message = "<h2> Hi, $prefix $RecipientName<h2>
    <p> Welcome to ASFIRJ</p>
    <p><a href=https://authors.asfirj.org/verify?a=$encryptedButton>click here</a> to verify your account. </p>
    <p>Or paste this <a href=https://authors.asfirj.org/verify?a=$encryptedButton>https://authors.asfirj.org/verify?a=$encryptedButton</a> link in your browser</p>
    <p><center><h6>African Science Research Journal</h6></p>
    ";

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($senderEmail, "ASFIRJ");
        $email->setSubject($subject);
        $email->addTo($RecipientEmail, $RecipientName);
        $email->addContent(
            "text/html",$message
        );
     
        $response = $sendgrid->send($email);
    
    //      print $response->statusCode() . "\n";
    // print_r($response->headers());
    // print $response->body() . "\n";

    
} catch (Exception $e) {
    // $response = array('status' => 'Internal Error', 'message' => 'Caught exception: '. $e->getMessage() ."\n");
         print $e;

}
}else{
    $response = array('status'=> 'error', 'message' => 'User does not exist on Our servers');
     print $response;

}
}else{
    $response = array('status' => 'error', 'message' => 'Invalid Request');
            // print $response;

}
}