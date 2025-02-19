<?php

include "../cors.php";
include "../db.php";

// $data = json_decode(file_get_contents("php://input"), true);

// $email = $data["encrypted"];
$email = $_GET["user"];

$stmt = $con->prepare("SELECT * FROM authors_account WHERE md5(id) = ? OR md5(email) = ? OR email = ?", );
$stmt->bind_param("sss", $email, $email, $email);
$stmt->execute();
$results = $stmt->get_result();
if(mysqli_num_rows($results) > 0){
    $row = $results->fetch_assoc();
    $userEMail = $row["email"];
    


$stmt = $con->prepare("SELECT * FROM `submitted_for_review` WHERE `reviewer_email` = ? AND `status` = 'review_invitation_accepted' ORDER BY `id` DESC");
$stmt->bind_param("s", $userEMail);
$stmt->execute();
$result = $stmt->get_result();
if(mysqli_num_rows($result) > 0){
    echo $result;
    $toReviewList = array(); // Initialize an array to store all toReview
    // $ReviewArticleContent = array();

    while ($row = $result->fetch_assoc()) {
        $submissionId = $row["article_id"];
        $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `revision_id` =?");
        $stmt->bind_param("s", $submissionId);
        $stmt->execute();
        $result = $stmt->get_result();
        while($subRow = $result->fetch_assoc()){
  // Loop through each row in the result set and append it to the toReviewList array
  $toReviewList[] = $subRow;

  $articleType = $subRow["article_type"];
  $Status = $subRow["status"];
  $ArticleId = $subRow["article_id"];
  $Title = $subRow["title"];
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
           
           <form action='' onsubmit='return false' class='actionForm'>
           <input type='hidden' value=$ArticleId readonly name=a/>
               <select name='do' id='' class='form-control reviewAction'>
               <option>Choose an Action </option>
                   <option value='view'>View</option>
                   <option value='score'>Review & Score</option>
               </select>
           </form>  
           
       </td>

       <td data-label='ID'>
           29-May-2024
       
       </td>
                             
       <td data-label='title' style='white-space:pre-wrap'>$articleType
</td>
       <td class='whitespace-nowrap' data-label='submitted'>
           <span>$ArticleId</span><br><br>
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
    echo "<tr><td>Internal Server Error</td></tr>";

}