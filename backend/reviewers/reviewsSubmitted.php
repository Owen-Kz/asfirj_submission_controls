<?php
include "../cors.php";
include "../db.php";

$email = $_GET["user"];
$userEMail = "";
$stmt = $con->prepare("SELECT * FROM authors_account WHERE md5(id) = ? OR md5(email) = ? OR email = ?");
$stmt->bind_param("sss", $email, $email, $email);
$stmt->execute();
$results = $stmt->get_result();

if(mysqli_num_rows($results) > 0){
    $row = $results->fetch_assoc();
    $userEMail = $row["email"];
    $userName = $row["firstname"] . " " . $row["lastname"];
    
    $stmt = $con->prepare("SELECT * FROM `reviews` WHERE `reviewer_email` = ? AND (`review_status` = 'review_submitted' OR `review_status` = 'review_completed')");
    $stmt->bind_param("s", $userEMail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if(mysqli_num_rows($result) > 0){
        while ($row = $result->fetch_assoc()) {
            $submissionId = $row["review_id"];
            $stmt = $con->prepare("SELECT * FROM `reviews` WHERE `review_id` = ?");
            $stmt->bind_param("s", $submissionId);
            $stmt->execute();
            $reviewResult = $stmt->get_result();
            
            while($subRow = $reviewResult->fetch_assoc()){
                $reviewId = $subRow["review_id"];
                $Status = $subRow["review_status"];
                $ArticleId = $subRow["article_id"];
                $Title = $subRow["general_comment"];
                $submissionDate = date("d-M-Y", strtotime($subRow["date_created"]));
                $completionDate = !empty($subRow["completion_date"]) ? date("d-M-Y", strtotime($subRow["completion_date"])) : "N/A";
                
                // Get article title if available
                $articleTitle = "Untitled";
                $articleStmt = $con->prepare("SELECT title FROM submissions WHERE revision_id = ?");
                $articleStmt->bind_param("s", $ArticleId);
                $articleStmt->execute();
                $articleResult = $articleStmt->get_result();
                if($articleResult->num_rows > 0) {
                    $articleData = $articleResult->fetch_assoc();
                    $articleTitle = $articleData["title"];
                }
                
                $StatusMain = "";
                $statusClass = "";
                if($Status === "accepted"){
                    $StatusMain = "Accepted";
                    $statusClass = "status-completed";
                } else if($Status === 'rejected'){
                    $StatusMain = "Rejected";
                    $statusClass = "status-rejected";
                } else if($Status === 'review_saved' || $Status === "submitted_for_review" || $Status === "review_invite_accepted"){
                    $StatusMain = "Under Review";
                    $statusClass = "status-in-review";
                } else if($Status === 'review_completed' || $Status === 'review_submitted' ) {
                    $StatusMain = "Completed";
                    $statusClass = "status-completed";
                }
                
                echo "<tr id='review_$reviewId' class='review-row hover:bg-gray-50 transition-colors duration-200'>         
                    <td class='px-6 py-4 whitespace-nowrap'>
                    
                        <div class='flex flex-col space-y-2'>
                            <button onclick='viewReview(\"$reviewId\")' class='action-btn view-btn inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-primary hover:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary'>
                                <i class='las la-eye mr-1'></i> View Review
                            </button>
                            <button onclick='contactJournal(\"$ArticleId\", \"$reviewId\")' class='action-btn contact-btn inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary'>
                                <i class='las la-envelope mr-1'></i> Contact
                            </button>
                            <button onclick='downloadReview(\"$reviewId\")' class='action-btn download-btn inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary'>
                                <i class='las la-download mr-1'></i> Download
                            </button>
                        </div>
                    </td>
                    <td class='px-6 py-4 whitespace-nowrap'>
                        <div class='text-sm text-gray-900'>Submitted: $submissionDate</div>
                        <div class='text-sm text-gray-500'>Completed: $completionDate</div>
                    </td>
                    <td class='px-6 py-4'>
                        <div class='text-sm font-medium text-gray-900'>ID: $ArticleId</div>
                        <div class='text-sm text-gray-500 mt-1 line-clamp-2'>$articleTitle</div>
                        <div class='text-xs text-gray-400 mt-1'>Your comment: " . substr($Title, 0, 100) . "...</div>
                    </td>
                    <td class='px-6 py-4 whitespace-nowrap'>
                        <span class='px-2.5 py-0.5 rounded-full text-xs font-medium $statusClass'>$StatusMain</span>
                    </td>
                </tr>";
            }
        }
    } else {
        echo "<tr><td colspan='4' class='px-6 py-8 text-center'>
                <div class='flex flex-col items-center justify-center text-gray-400'>
                    <i class='las la-clipboard-list text-4xl mb-3'></i>
                    <p class='text-lg font-medium text-gray-500'>No reviews submitted yet</p>
                    <p class='text-sm mt-1'>You haven't submitted any reviews yet.</p>
                </div>
            </td></tr>";
    }
} else {
    echo "<tr><td colspan='4' class='px-6 py-4 text-center text-red-600'>Invalid User ID</td></tr>";
}
?>

<style>
.review-row {
    border-bottom: 1px solid #e5e7eb;
}

.action-btn {
    transition: all 0.2s ease;
}

.action-btn:hover {
    transform: translateY(-1px);
}

.status-completed {
    background-color: #10B981;
    color: white;
}

.status-pending {
    background-color: #F59E0B;
    color: white;
}

.status-rejected {
    background-color: #EF4444;
    color: white;
}

.status-in-review {
    background-color: #3B82F6;
    color: white;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.review-details {
    background-color: #f9fafb;
    border-left: 4px solid #310357;
}

.progress-bar {
    height: 8px;
    border-radius: 4px;
    background-color: #e5e7eb;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background-color: #310357;
    border-radius: 4px;
    transition: width 0.3s ease;
}
</style>

<script>
function viewReview(reviewId) {
    // Show loading state
    const row = document.getElementById(`review_${reviewId}`);
    const buttons = row.querySelectorAll('.action-btn');
    buttons.forEach(btn => btn.disabled = true);
    
    // Simulate API call to fetch review details
    setTimeout(() => {
        // In a real implementation, this would fetch data from your backend
        const reviewDetails = {
            id: reviewId,
            title: "Review Details",
            content: "Detailed review content would appear here..."
        };
        
        // Show review in a modal or expand the row
        // showReviewModal(reviewDetails);
        window.location.href = `../viewReview/?s=${reviewId}`;

        // Re-enable buttons
        buttons.forEach(btn => btn.disabled = false);
    }, 500);
}

function contactJournal(articleId, reviewId) {
    // Open contact form or modal
    const subject = `Regarding Review ${reviewId} for Article ${articleId}`;
    const body = `Dear Editorial Team,\n\nI would like to discuss the following regarding my review (ID: ${reviewId}) for article ${articleId}:\n\n`;
    
    window.open(`mailto:editorial@asfirj.org?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`, '_blank');
}

function downloadReview(reviewId) {
    // Show loading state on the download button
    const downloadBtn = document.querySelector(`#review_${reviewId} .download-btn`);
    const originalHtml = downloadBtn.innerHTML;
    downloadBtn.innerHTML = '<i class="las la-spinner la-spin mr-1"></i> Preparing...';
    downloadBtn.disabled = true;
    
    // Simulate download process
    setTimeout(() => {
        // In a real implementation, this would generate or fetch a PDF
        const link = document.createElement('a');
        link.href = `../api/download_review.php?id=${reviewId}`;
        link.target = '_blank';
        // link.click();
        alert("Coming Soon!Z")
        
        // Restore button state
        downloadBtn.innerHTML = originalHtml;
        downloadBtn.disabled = false;
    }, 1500);
}

function showReviewModal(reviewDetails) {
    // Create modal element
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">Review Details: ${reviewDetails.id}</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="las la-times text-2xl"></i>
                    </button>
                </div>
                <div class="mt-4 review-details p-4 rounded-lg">
                    <h4 class="font-medium text-gray-700 mb-2">Article Information</h4>
                    <p class="text-gray-600">${reviewDetails.content}</p>
                    
                    <div class="mt-6">
                        <h4 class="font-medium text-gray-700 mb-2">Review Progress</h4>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 100%"></div>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Completed on: ${new Date().toLocaleDateString()}</p>
                    </div>
                    
                    <div class="mt-6 flex space-x-3">
                        <button onclick="downloadReview('${reviewDetails.id}')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-purple-800">
                            <i class="las la-download mr-2"></i> Download PDF
                        </button>
                        <button onclick="closeModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Add close function to global scope
    window.closeModal = function() {
        document.body.removeChild(modal);
        delete window.closeModal;
    };
}

// Add keyboard event listener to close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && typeof window.closeModal === 'function') {
        window.closeModal();
    }
});
</script>