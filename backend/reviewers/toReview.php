<?php

include "../cors.php";
include "../db.php";

// $data = json_decode(file_get_contents("php://input"), true);

// $email = $data["encrypted"];
$email = $_GET["user"];

$stmt = $con->prepare("SELECT * FROM `submitted_for_review` WHERE md5(`reviewer_email`) = ? AND `status` != 'review_submitted' AND `status` != 'review_completed'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if(mysqli_num_rows($result) > 0){
    $toReviewList = array(); // Initialize an array to store all toReview
    // $ReviewArticleContent = array();

    while ($row = $result->fetch_assoc()) {
        $submissionId = $row["article_id"];
        $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `article_id` =?");
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
       <td data-label='decisioned' class='whitespace-nowrap'>$Status</td>
  </tr>";
}
      
    }
    // $response = array("status" => "success", "submissionsToReview" => $toReviewList);
    // echo json_encode($response);
}else{
    echo "<tr><td>You have no new review requests";
    // $response = array("status" => "success", "submissionsToReview" => []);
    // echo json_encode($response);
}