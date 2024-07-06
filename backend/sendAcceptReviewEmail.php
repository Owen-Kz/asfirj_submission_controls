<?php

function SendAcceptReviewEmail($RecipientEmail, $manuscriptId){

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
    $currentYear = date("Y");
    $subject = "Invitation to review manuscript for ASFIRJ ($manuscriptId)";

    
    $message =  <<<EOT
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Email Content</title>
    </head>
    <body>
        <p>[Manuscript title] “ASFI Research Journal of the African Science Frontiers Initiatives (Manuscript ID)”</p>
        <p>[Date of sending invite]</p>
        <p>[Name of Invited Reviewer],</p>
        <p>Thank you for accepting to review this paper. The manuscript files are now available for you on your ASFIRJ Reviewer Dashboard, which you can access using the link: <a href="[Link to Reviewers Dashboard]">Reviewers Dashboard</a>.</p>
        <p>On the Reviewer Scoring Sheet, you will provide your reviewer report, evaluation, scoring, and rating of the different aspects of the manuscript. For detailed instructions to reviewers reviewing manuscripts for ASFIRJ, please visit this link: <a href="https://asfirj.org/reviewers.html">https://asfirj.org/reviewers.html</a>.</p>
        <p>We will appreciate it if you can return your reviewer report and scoring sheet on or before [Date of Return the Review Report, which should be 14 days from the day of acceptance of review invite].</p>
        <br/>
        <p>Sincerely,</p>
        <p>[Title and Name of Editor]<br>Editor</p>
        <p><a href="mailto:submissions@asfirj.org">submissions@asfirj.org</a><br>ASFI Research Journal<br>Excellence. Quality. Impact<br>"Raising the bar of scientific publishing in Africa"</p>
        <p><a href="https://asfirj.org/">https://asfirj.org/</a><br><a href="mailto:asfirj@asfirj.org">asfirj@asfirj.org</a></p>
        <p>LinkedIn: <a href="https://www.linkedin.com/in/asfi-research-journal-1b9929309">www.linkedin.com/in/asfi-research-journal-1b9929309</a><br>
        X (formerly Twitter): <a href="https://twitter.com/asfirj1">https://twitter.com/asfirj1</a><br>
        Instagram: <a href="https://www.instagram.com/asfirj1/">https://www.instagram.com/asfirj1/</a><br>
        WhatsApp: <a href="https://chat.whatsapp.com/L8o0N0pUieOGIUHJ1hjSG3">https://chat.whatsapp.com/L8o0N0pUieOGIUHJ1hjSG3</a></p>
    </body>
    </html>

    EOT;

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