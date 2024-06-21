<?php

function ReviewConfirmationEmail($RecipientEmail, $reviewerEmail, $status){

require '../vendor/autoload.php'; // If you're using Composer (recommended)
// Comment out the above line if not using Composer
// require("<PATH TO>/sendgrid-php.php");
// If not using Composer, uncomment the above line and
// download sendgrid-php.zip from the latest release here,
// replacing <PATH TO> with the path to the sendgrid-php.php file,
// which is included in the download:
// https://github.com/sendgrid/sendgrid-php/releases
// Inmport Environment Variables
require "./exportENV.php";
include "./db.php";

$api = $_ENV['SENDGRID_API_KEY'];
$senderEmail = $_ENV["SENDGRID_EMAIL"];


if($RecipientEmail){

    $encryptedButton = md5($RecipientEmail);

$sendgrid = new \SendGrid($api);
try {
 
    // print $response->statusCode() . "\n";
    // print_r($response->headers());
    // print $response->body() . "\n";
    $subject = "Review Request $status by $reviewerEmail";
    $message = "<h2> Hi,<h2>
    <p> Your Request to Review an Article has been $status by the reviewer, Login to your dashboard to monitor the process</p>;
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
    
        $response = array('status' => 'success', 'message' => 'Email sent', 'email' => $encryptedButton);
        print $response;

    
} catch (Exception $e) {
    $response = array('status' => 'Internal Error', 'message' => 'Caught exception: '. $e->getMessage() ."\n");
            print $response;

}

}else{
    $response = array('status' => 'error', 'message' => 'Invalid Request');
            print $response;

}
}