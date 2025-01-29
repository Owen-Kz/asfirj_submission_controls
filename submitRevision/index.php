<?php
include "../backend/cors.php";
include "../backend/db.php";
include "../backend/checkifAuthorExists.php";
include "../backend/updateRevision.php";
// include "../backend/addSubmissoinKeywords.php";
// include "../backend/addSuggestedReviewers.php";
// include "../backend/createCoAuthor.php";
include "../moveFile.php";


session_start();
// function MoveFile($outputFile, $designatedDirectory, $newFilename){
//     // Move the final merged PDF to the designated folder
// // $manuscriptFile = basename($_FILES[$outputFile]["name"]);
// // $targetFile = "../uploadedFiles/". $manuscriptFile;
// // if (!is_writable("../uploadedFiles/")) {
// //     die("Target directory is not writable.");
// // }
// // if (!file_exists("../uploadedFiles/")) {
// //     mkdir("../uploadedFiles/", 0777, true);
// // }
// // if (move_uploaded_file($_FILES[$outputFile]["tmp_name"], $targetFile)) {
// // // move_uploaded_file($outputFile["tmp_name"], $targetFile);
// // rename("../uploadedFiles/". $_FILES[$outputFile]["name"], "../uploadedFiles/".$newFilename);
// // // print_r("File Uploaded");
// // }else{
// //    echo "Could Not Upload File ".json_encode($_FILES[$outputFile]);
// // }
//     // Move the final merged PDF to the designated folder
//     $manuscriptFile = basename($_FILES[$outputFile]["name"]);
//     $targetFile = __DIR__."../uploadedFiles/" . $manuscriptFile;
    

//     // print("Attempting to move file to: " . $targetFile);

//     if (!file_exists(__DIR__."../uploadedFiles/")) {
//         print("Directory does not exist. Creating directory...");
//         mkdir(__DIR__."../uploadedFiles/", 0777, true);
//     }

//     if (!is_writable(__DIR__."../uploadedFiles/")) {
//         print("Target directory is not writable.");
//         die("Target directory is not writable.");
//     }
//     // if (!file_exists("../uploadedFiles/")) {
//     //     mkdir("../uploadedFiles/", 0777, true);
//     // }
//     if (move_uploaded_file($_FILES[$outputFile]["tmp_name"], $targetFile)) {
//         // move_uploaded_file($outputFile["tmp_name"], $targetFile);
//         rename(__DIR__."../uploadedFiles/" . $_FILES[$outputFile]["name"],__DIR__."../uploadedFiles/" . $newFilename);
//         // print_r("File Uploaded");
//     } else {
//         echo "Could Not Upload File " . json_encode($_FILES[$outputFile]);
//     }

// }

$title = $_POST["manuscript_full_title"];
$type = $_POST["article_type"];
$discipline = $_POST["discipline"];
$manuscript_file = $_FILES["manuscript_file"];
$figures = $_FILES["figures"];
$supplementary_material = $_FILES["supplementary_materials"];
$graphic_abstract = $_FILES["graphic_abstract"];
$tables = $_FILES["tables"];
$manuscriptId = $_POST["manuscript_id"];
$revisionStatus = $_POST["review_status"];

$combinedFilename = "";
$originalFilename = "";
$combinedDocFile = "";

$trackedManuscriptFile  = $_FILES["tracked_revisedmanuscript_file"];
$trackedManuscriptFileName = "";

$cover_letter = $_FILES["cover_letter"];
$cover_letter_file = "";
$manuscriptFileName = "";
$tablesFileName = "";
$figuresFileName = "";
$supplementaryMaterialsFileName = "";
$graphicAbstractFileName = "";
$cover_letter_file_main = $_FILES["cover_letter"];

$correspondence = "";
$previousManuscriptID = "";
$otherFiles = "";

if(isset($_POST["correspondence"])){
$correspondence = $_POST["correspondence"];
}

if(isset($_POST["previous_id"])){
$previousManuscriptID = $_POST["previous_id"];
}


$authorsPrefix = [];
$authors_firstname = [];
$authors_lastname = [];
$authors_other_name = [];
$affiliation = [];
$affiliation_country = [];
$affiliation_city = [];
$authorEmail = [];
$authors_orcid = [];
$membership_id = [];



if(isset($_POST["authors_prefix"])){
    $authorsPrefix = $_POST["authors_prefix"];
    $authors_firstname = $_POST["authors_first_name"];
    $authors_lastname = $_POST["authors_last_name"];
    $authors_other_name = $_POST["authors_other_name"];
    $affiliation = $_POST["affiliation"];
    $affiliation_country = $_POST["affiliation_country"];
    $affiliation_city = $_POST["affiliation_city"];
    $authorEmail = $_POST["email"];
    $authors_orcid = $_POST["authors_orcid"];
    }
    $LoggedInauthorsPrefix = $_POST["loggedIn_authors_prefix"];
$LoggedInauthors_firstname = $_POST["loggedIn_authors_first_name"];
$LoggedInauthors_lastname = $_POST["loggedIn_authors_last_name"];
$LoggedInauthors_other_name = $_POST["loggedIn_authors_other_name"];
$LoggedInaffiliation = $_POST["loggedIn_affiliation"];
$LoggedInaffiliation_country = $_POST["loggedIn_affiliation_country"];
$LoggedInaffiliation_city = $_POST["loggedIn_affiliation_city"];
$LoggedInauthorEmail = $_POST["loggedIn_author"];

$loggedIn_authors_ORCID = $_POST["loggedIn_authors_ORCID"];

    $suggestedReviewerEmail = $_POST["suggested_reviewer_email"];
    $suggested_reviewer_fullname = $_POST["suggested_reviewer_fullname"];
    $suggested_reviewer_affiliation = $_POST["suggested_reviewer_affiliation"];
    $suggested_reviewer_country = $_POST["suggested_reviewer_country"];
    $suggested_reviewer_city = $_POST["suggested_reviewer_city"];
    
    $keywords = $_POST["keyword"];
    

$abstract = $_POST["abstract"];
$corresponding_author = $_POST["corresponding_author"];
$Buffer = bin2hex(random_bytes(7));
$articleID = $manuscriptId;

if(isset($title)){
  
    $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `article_id` = ? AND (`status` = 'returned_for_revision' OR `status` = 'revision_saved') AND `corresponding_authors_email` = ?");
    $stmt->bind_param("ss", $articleID, $corresponding_author);
    if(!$stmt){
        $response = array("status"=>"error", "message" => $con->error);
        echo json_encode($response);
        exit;
    }
    // ASFIRJ-2024-18
    $stmt->execute();
    $result = $stmt->get_result();
    $count = mysqli_num_rows($result);
    if($count > 0){
        $row = $result->fetch_assoc();
        $revisionsCount = (int) $row["revisions_count"];
        $newRevisionsCount = (int) $revisionsCount + 1;
        $mainTitle = $row["title"];

        $RevisionsId = $articleID.".R".$newRevisionsCount;
  
        if (strlen($title) < 1) {
        $title = $mainTitle;
        }



// if the revision status is set to save for later 
if($revisionStatus === "saved_for_later" || $revisionStatus === "revision_saved"){
    $otherFiles = [];
        // Logic For file upload should go here 
        if(isset($cover_letter_file_main) && $cover_letter_file_main["size"] > 0 && isset($_FILES["cover_letter"]["tmp_name"])){
            $cover_letter_file = "coverLetter". '-' . basename($cover_letter_file_main["name"]);
        
            MoveFile("cover_letter",  __DIR__."/uploadedFiles", $cover_letter_file);
        }
        if(isset($manuscript_file) && $manuscript_file["size"] > 0 && isset($_FILES["manuscript_file"]["tmp_name"])){
            $combinedFilename = "manuscriptFile". '-' . basename($manuscript_file["name"]);
            $originalFilename = "manuscriptFile". '-' . basename($manuscript_file["name"]);

            MoveFile("manuscript_file",  __DIR__."/uploadedFiles", $combinedFilename);
            // MoveFile("manuscript_file",  __DIR__."/uploadedFiles", $originalFilename);
            array_push($otherFiles,  "https://cp.asfirj.org/uploadedFiles/". $combinedFilename);
        }
        if(isset($figures) && $figures["size"] > 0 && isset($_FILES["figures"]["tmp_name"])){
            $figuresFileName = "figures". '-' . basename($figures["name"]);

            MoveFile("figures",  __DIR__."/uploadedFiles", $figuresFileName);
            array_push($otherFiles,  "https://cp.asfirj.org/uploadedFiles/". $figuresFileName);
        }
        if(isset($supplementary_material) && $supplementary_material["size"] > 0 && isset($_FILES["supplementary_materials"]["tmp_name"])){
            $supplementaryMaterialsFileName = "supplementaryMaterial". '-' . basename($supplementary_material["name"]);

            MoveFile("supplementary_materials",  __DIR__."/uploadedFiles", $supplementaryMaterialsFileName);
            array_push($otherFiles,  "https://cp.asfirj.org/uploadedFiles/". $supplementaryMaterialsFileName);
        }
        if(isset($graphic_abstract) && $graphic_abstract["size"] > 0 && isset($_FILES["graphic_abstract"]["tmp_name"])){
            $graphicAbstractFileName = "graphicAbstract". '-' . basename($graphic_abstract["name"]);

            MoveFile("graphic_abstract",  __DIR__."/uploadedFiles", $graphicAbstractFileName);
            array_push($otherFiles,  "https://cp.asfirj.org/uploadedFiles/". $graphicAbstractFileName);
        }

        if(isset($tables) && $tables["size"] > 0 && isset($_FILES["tables"]["tmp_name"])){
            $tablesFileName = "tables". '-' . basename($tables["name"]);

            MoveFile("tables",  __DIR__."/uploadedFiles", $tablesFileName);
            array_push($otherFiles,  "https://cp.asfirj.org/uploadedFiles/". $tablesFileName);
        }

             // For tracked Manuscript File 
             if(isset($trackedManuscriptFile) && $trackedManuscriptFile["size"] > 0 && isset($trackedManuscriptFile["tmp_name"])){
                $fileExtensionTracked = pathinfo($trackedManuscriptFile["name"], PATHINFO_EXTENSION);
    
                $trackedManuscriptFileName = "tracked_revised_manuscript-".$timestamp . '.' . $fileExtensionTracked;
    
                MoveFile("tracked_revisedmanuscript",  __DIR__."/uploadedFiles", $trackedManuscriptFileName);
                array_push($otherFiles,  "https://cp.asfirj.org/uploadedFiles/". $trackedManuscriptFileName);
            }
        
    UpdateRevision($type,$RevisionsId, $revisionsCount, $discipline, $title, $combinedFilename, Json_encode($otherFiles), $cover_letter_file, $abstract, $corresponding_author, $articleID, "revision_saved", $tablesFileName, $figuresFileName, $graphicAbstractFileName, $supplementaryMaterialsFileName, $authorsPrefix, $authorEmail,$authors_firstname,$authors_lastname, $authors_other_name, $authors_orcid, $affiliation, $affiliation_country, $affiliation_city, $keywords, $suggested_reviewer_fullname, $suggested_reviewer_affiliation, $suggested_reviewer_country, $suggested_reviewer_city, $suggestedReviewerEmail, $LoggedInauthorsPrefix,$LoggedInauthors_firstname, $LoggedInauthors_lastname, $LoggedInauthors_other_name, $LoggedInauthorEmail, $loggedIn_authors_ORCID, $LoggedInaffiliation, $LoggedInaffiliation_country, $LoggedInaffiliation_city, $trackedManuscriptFileName, $previousManuscriptID);
}else{
    // $fields = array(
    //     'manuscript_file' => new CURLFile($manuscript_file['tmp_name'], $manuscript_file['type'], $manuscript_file['name']),
    // );
    // Prepare files for sending to Node.js server
             // Logic For file upload should go here 
             if(isset($cover_letter_file_main) && $cover_letter_file_main["size"] > 0 && isset($_FILES["cover_letter"]["tmp_name"])){
                $cover_letter_file = "coverLetter". '-' . basename($cover_letter_file_main["name"]);
            
                MoveFile("cover_letter",  __DIR__."/uploadedFiles", $cover_letter_file);
            }
// Path to save the dummy PDF file
$dummyPDFPath = '../temp/dummy.pdf';

$fields = array(
    'manuscript_file' => new CURLFile($manuscript_file['tmp_name'], $manuscript_file['type'], $manuscript_file['name']),
);
if (isset($figures) && $figures["size"] > 0 && isset($_FILES["figures"]["tmp_name"])) {
    $fields["figures"] = new CURLFile($figures['tmp_name'], $figures['type'], $figures['name']);
} else {
    // Use            the dummy PDF if figures file does not exist
    $fields["figures"] = new CURLFile($dummyPDFPath, 'application/pdf', 'dummy.pdf');
}

if (isset($supplementary_material) && $supplementary_material["size"] > 0 && isset($_FILES["supplementary_materials"]["tmp_name"])) {               
    $fields['supplementary_material'] = new CURLFile($supplementary_material['tmp_name'], $supplementary_material['type'], $supplementary_material['name']);
}else {
    // Use the dummy PDF if supplementary_material file does not exist
    $fields["supplementary_material"] = new CURLFile($dummyPDFPath, 'application/pdf', 'dummy.pdf');
}
if (isset($graphic_abstract) && $graphic_abstract["size"] > 0 && isset($_FILES["graphic_abstract"]["tmp_name"])) {
    $fields['graphic_abstract'] = new CURLFile($graphic_abstract['tmp_name'], $graphic_abstract['type'], $graphic_abstract['name']);
}else{
    $fields["graphic_abstract"] = new CURLFile($dummyPDFPath, 'application/pdf', 'dummy.pdf');
}

if (isset($tables) && $tables["size"] > 0 && isset($_FILES["tables"]["tmp_name"])) {
    $fields["tables"] = new CURLFile($tables['tmp_name'], $tables['type'], $tables['name']);
}else{
    $fields["tables"] = new CURLFile($dummyPDFPath, 'application/pdf', 'dummy.pdf');
}
$fields["revisionId"] = $RevisionsId;

        $url = "https://asfischolar.org/external/api/combinePDF";
        $wordDocURL = "https://process.asfirj.org/external/api/combineDOC";
         // Replace with your Node.js server URL
        // $url = "https://asfischolar.org/external/api/combinePDF"; // Replace with your Node.js server URL


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem'); // Path to cacert.pem file 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (insecure)


        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            // echo 'Error:' . curl_error($ch);
            $response = array("status" => "error", "message" => 'Curl Error:' . curl_error($ch));
            echo json_encode($response);
            exit;
        }
        curl_close($ch);

        // Handle WordDocuments 

//         $ch_WORD = curl_init();
//         curl_setopt($ch_WORD, CURLOPT_URL, $wordDocURL);
//         curl_setopt($ch_WORD, CURLOPT_POST, 1);
//         curl_setopt($ch_WORD, CURLOPT_POSTFIELDS, $fields);
//         // curl_setopt($ch_WORD, CURLOPT_RETURNTRANSFER, true);
// // curl_setopt($ch_WORD, CURLOPT_CAINFO, __DIR__ . '/cacert.pem'); // Path to cacert.pem file 
//         curl_setopt($ch_WORD, CURLOPT_RETURNTRANSFER, true);
//         curl_setopt($ch_WORD, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (insecure)


        // $response_DOC = curl_exec($ch_WORD);
        // if (curl_errno($ch_WORD)) {
        //     // echo 'Error:' . curl_error($ch);
        //     $response = array("status" => "error", "message" => 'Curl Error:' . curl_error($ch));
        //     echo json_encode($response);
        //     exit;
        // }
        // curl_close($ch_WORD);

        if ($response) {
            $responseDecoded = json_decode($response, true);
            // $responseDecoded_DOC = json_decode($response_DOC, true);
            // if ($responseDecoded_DOC) {
            //     $combinedDocFile = $responseDecoded_DOC['filename'];
            //     $combinedFilePath = 'uploads/' . $combinedDocFile;
            // }
    if ($responseDecoded['success']) {
        $combinedFilename = $responseDecoded['combinedFile'];
        $otherFiles = json_encode($responseDecoded['originalFiles']);
        

        if ($combinedFilename) {
            // Finally UploadDocuments after file has been combined
            UpdateRevision($type,$RevisionsId, $revisionsCount, $discipline, $title, $combinedFilename, $otherFiles, $cover_letter_file, $abstract, $corresponding_author, $articleID, $revisionStatus, $tablesFileName, $figuresFileName, $graphicAbstractFileName, $supplementaryMaterialsFileName, $authorsPrefix, $authorEmail,$authors_firstname,$authors_lastname, $authors_other_name, $authors_orcid, $affiliation, $affiliation_country, $affiliation_city, $keywords, $suggested_reviewer_fullname, $suggested_reviewer_affiliation, $suggested_reviewer_country, $suggested_reviewer_city, $suggestedReviewerEmail, $LoggedInauthorsPrefix,$LoggedInauthors_firstname, $LoggedInauthors_lastname, $LoggedInauthors_other_name, $LoggedInauthorEmail, $loggedIn_authors_ORCID, $LoggedInaffiliation, $LoggedInaffiliation_country, $LoggedInaffiliation_city, $trackedManuscriptFileName, $previousManuscriptID);
        } else {
            $response = array("status"=>"error", "message"=>"Error moving combined PDF to designated folder");
            echo json_encode($response);
        }
    }
    } else {
        $response = array("status"=>"error", "message"=>"Error combining PDFs");
        echo json_encode($response);
    }
}
}else{
    $response = array("status"=>"error", "message" => "This Submission Does not Exist Or has not been returned for Review");
    echo json_encode($response);
    exit;
}
    
} else {
    $response = array("status"=>"error", "message"=>"Incomplete Fields");
    echo json_encode($response);
}

