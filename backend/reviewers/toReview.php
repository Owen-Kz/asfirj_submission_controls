<?php

include "../cors.php";
include "../db.php";

// $data = json_decode(file_get_contents("php://input"), true);
// $email = $data["encrypted"];
$email = $_GET["user"];

$stmt = $con->prepare("SELECT * FROM authors_account WHERE md5(id) = ? OR md5(email) = ? OR email = ?");
$stmt->bind_param("sss", $email, $email, $email);
$stmt->execute();
$userResults = $stmt->get_result();

if(mysqli_num_rows($userResults) > 0){
    $userRow = $userResults->fetch_assoc();
    $userEMail = $userRow["email"];

    $stmt = $con->prepare("SELECT * FROM `submitted_for_review` WHERE `reviewer_email` = ? AND `status` = 'review_invitation_accepted' ORDER BY `id` DESC");
    $stmt->bind_param("s", $userEMail);
    $stmt->execute();
    $reviewResult = $stmt->get_result();
 
    if(mysqli_num_rows($reviewResult) > 0){
        $toReviewList = array();
        $hasResults = false;

        while ($reviewRow = $reviewResult->fetch_assoc()) {
            $submissionId = $reviewRow["article_id"];
            
            // If you don't have due dates in database, calculate based on invitation date
            $invitationDate = $reviewRow["date_submitted"]; // Assuming you have this column
            $dueDate = date('Y-m-d', strtotime($invitationDate . ' + 14 days'));
            
            $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `revision_id` = ?");
            $stmt->bind_param("s", $submissionId);
            $stmt->execute();
            $submissionResult = $stmt->get_result();
            
            if(mysqli_num_rows($submissionResult) > 0){
                $hasResults = true;
                
                while($subRow = $submissionResult->fetch_assoc()){
                    $toReviewList[] = $subRow;

                    $articleType = $subRow["article_type"];
                    $Status = $subRow["status"];
                    $ArticleId = $subRow["article_id"];
                    $Title = $subRow["title"];
                    $dateSubmitted = $subRow["date_submitted"];
                    
                    // Format dates
                    $formattedDate = date("d-M-Y", strtotime($dateSubmitted));
                    $formattedDueDate = $dueDate ? date("d-M-Y", strtotime($dueDate)) : "No due date";
                    
                    // Check if due date has passed
                    $isPastDue = false;
                    if ($dueDate) {
                        $currentDate = new DateTime();
                        $dueDateTime = new DateTime($dueDate);
                        $isPastDue = $currentDate > $dueDateTime;
                    }
                    
                    $StatusMain = "";
                    $statusColor = "bg-gray-100 text-gray-800";
                    
                    if($Status === "accepted"){
                        $StatusMain = "Accepted";
                        $statusColor = "bg-green-100 text-green-800";
                    } else if($Status === 'rejected'){
                        $StatusMain = "Rejected";
                        $statusColor = "bg-red-100 text-red-800";
                    } else if($Status === 'review_submitted' || $Status === "submitted_for_review" || $Status === "review_invite_accepted"){
                        $StatusMain = "Under Review";
                        $statusColor = "bg-blue-100 text-blue-800";
                    }
                    
                    // Add overdue status if applicable
                    if ($isPastDue && $StatusMain === "Under Review") {
                        $StatusMain = "Overdue";
                        $statusColor = "bg-red-100 text-red-800";
                    }
                    
                    echo "<tr class='table-row-hover'>        
                    <td class='px-6 py-4 whitespace-nowrap'>";
                    
                    // Only show dropdown if not past due date
                    if (!$isPastDue) {
                        echo "<form onsubmit='return false' class='actionForm'>
                            <input type='hidden' value='$ArticleId' readonly name='a'/>
                            <select name='do' class='w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent reviewAction'>
                                <option value=''>Choose an Action</option>
                                <option value='view'>View Manuscript</option>
                                <option value='score'>Review & Score</option>
                            </select>
                        </form>";
                    } else {
                        echo "<div class='text-center text-red-600 font-medium'>
                            <i class='bi bi-clock-history mr-1'></i> Past Due
                        </div>";
                    }
                    
                    echo "</td>

                    <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700 " . ($isPastDue ? "bg-red-50 text-red-700 font-semibold" : "") . "'>
                        $formattedDueDate
                        " . ($isPastDue ? "<br><span class='text-xs'>Past due</span>" : "") . "
                    </td>
                                     
                    <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'>
                        $articleType
                    </td>
                    
                    <td class='px-6 py-4'>
                        <div class='text-sm font-medium text-gray-900'>$ArticleId</div>
                        <div class='text-sm text-gray-600 mt-1'>$Title</div>
                    </td>
                    
                    <td class='px-6 py-4 whitespace-nowrap'>
                        <span class='status-badge $statusColor'>$StatusMain</span>
                    </td>
                    </tr>";
                }
            }
        }
        
        // If no submissions were found for any review requests
        if (!$hasResults) {
            echo "<tr>
                    <td colspan='5' class='px-6 py-8 text-center'>
                        <div class='flex flex-col items-center justify-center text-gray-500'>
                            <i class='bi bi-clipboard-check text-4xl mb-3'></i>
                            <p class='text-lg'>No matching submissions found</p>
                            <p class='text-sm mt-1'>Review requests exist but no matching submissions were found.</p>
                        </div>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr>
                <td colspan='5' class='px-6 py-8 text-center'>
                    <div class='flex flex-col items-center justify-center text-gray-500'>
                        <i class='bi bi-clipboard-check text-4xl mb-3'></i>
                        <p class='text-lg'>No review requests</p>
                        <p class='text-sm mt-1'>You don't have any pending review requests at this time.</p>
                    </div>
                </td>
              </tr>";
    }
} else {
    echo "<tr>
            <td colspan='5' class='px-6 py-8 text-center'>
                <div class='flex flex-col items-center justify-center text-red-500'>
                    <i class='bi bi-exclamation-triangle text-4xl mb-3'></i>
                    <p class='text-lg'>Authentication Error</p>
                    <p class='text-sm mt-1'>Could not verify user credentials. Please try logging in again.</p>
                </div>
            </td>
          </tr>";
}