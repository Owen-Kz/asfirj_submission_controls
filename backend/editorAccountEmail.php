<?php

function EditorAccountEmail($RecipientEmail, $acceptInvitationLink, $rejectInvitationLink){

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

    $encryptedButton = md5($RecipientEmail);

$sendgrid = new \SendGrid($api);
try {
 
    // print $response->statusCode() . "\n";
    // print_r($response->headers());
    // print $response->body() . "\n";
    $subject = "ASFIRJ Review Request";
    $message = "<h2> Hi,<h2>
    <p> You have been invited to review a submission on ASFIRJ.</p>
    <p>please <a href=$acceptInvitationLink>Accept INvite</a> </p>
    <p><a href=$rejectInvitationLink>$rejectInvitationLink</a> Reject invitaion</p>
    <p><center><h6> ".date("Y")." African Science Research Journal</h6></center></p>";

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