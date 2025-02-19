<?php

include "../cors.php";
include "../db.php";

// $data = json_decode(file_get_contents("php://input"), true);

// $email = $data["encrypted"];
$email = $_GET["user"];
$userEMail = "";
$stmt = $con->prepare("SELECT * FROM authors_account WHERE md5(id) = ? OR md5(email) = ? OR email = ?", );
$stmt->bind_param("sss", $email, $email, $email);
$stmt->execute();
$results = $stmt->get_result();
if(mysqli_num_rows($results) > 0){
    $row = $results->fetch_assoc();
    $userEMail = $row["email"];


$stmt = $con->prepare("SELECT * FROM `reviews` WHERE `reviewer_email` = ? AND `review_status` = 'review_submitted' OR `review_status` = 'review_completed'");
$stmt->bind_param("s", $userEMail);
$stmt->execute();
$result = $stmt->get_result();
if(mysqli_num_rows($result) > 0){
    $toReviewList = array(); // Initialize an array to store all toReview
    // $ReviewArticleContent = array();

    while ($row = $result->fetch_assoc()) {
        $submissionId = $row["review_id"];
        $stmt = $con->prepare("SELECT * FROM `reviews` WHERE `review_id` =?");
        $stmt->bind_param("s", $submissionId);
        $stmt->execute();
        $result = $stmt->get_result();
        while($subRow = $result->fetch_assoc()){
  // Loop through each row in the result set and append it to the toReviewList array
  $toReviewList[] = $subRow;
  $reviewId = $subRow["review_id"];
  $Status = $subRow["review_status"];
  $ArticleId = $subRow["article_id"];
  $Title = $subRow["general_comment"];
  $StatusMain = "";
      if($Status === "accepted"){
        $StatusMain = "Submission Accepted";
        }else if($Status === 'rejected'){
        $StatusMain = "Submission was Rejected";
        }else if($Status === 'review_submitted' || $Status === "submitted_for_review" || $Status === "review_invite_accepted"){
        $StatusMain = "Under Review";
        }
  echo "<tr id='queue_0' name='queue_0' role='row' class='odd'>         
               <td data-label='status'>              
                        
                <form class='actionForm' onsubmit=return false>
                <input type='hidden' value='$reviewId' name='a/' readonly/>
                    <select name='' id='' class='form-control reviewAction' >
                    <option>Select an Action</option>
                        <option value='view_review'>View Submitted Review</option>
                        <option value='contact_journal'>Contact Journal</option>
                    </select>
                </form>   
                        
                    </td>
        
                    <td data-label='ID'>
                        29-May-2024
                    
                    </td>
                                          
                    
                    <td class='whitespace-nowrap' data-label='submitted'>
                        <span> $ArticleId</span> <br><br>
                        $Title
                    </td>
                    <td data-label='decisioned' class='whitespace-nowrap'>$StatusMain</td>
               </tr>";
}
      
    }
    // $response = array("status" => "success", "submissionsToReview" => $toReviewList);
    // echo json_encode($response);
}else{
    echo "<tr><td>You have no new review requests</td></tr>";
    // $response = array("status" => "success", "submissionsToReview" => []);
    // echo json_encode($response);
}

}else{
    echo "<tr><td>INvalid User ID</td></tr>";
    // $response = array("status" => "success", "submissionsToReview" => []);
    // echo json_encode($response);
}