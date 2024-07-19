<?php
include "../backend/cors.php";
include "../backend/db.php";
include "../backend/checkifAuthorExists.php";
include "../backend/updateSubmission.php";
// include "../backend/addSubmissoinKeywords.php";
// include "../backend/addSuggestedReviewers.php";
// include "../backend/createCoAuthor.php";
session_start();
function MoveFile($outputFile, $designatedDirectory, $newFilename)
{
    // Move the final merged PDF to the designated folder
      // Move the final merged PDF to the designated folder
      $manuscriptFile = basename($_FILES[$outputFile]["name"]);
      $targetFile = __DIR__."/../uploadedFiles/" . $manuscriptFile;
      
  
      if (move_uploaded_file($_FILES[$outputFile]["tmp_name"], $targetFile)) {
          // move_uploaded_file($outputFile["tmp_name"], $targetFile);
          rename(__DIR__."/../uploadedFiles/" . $_FILES[$outputFile]["name"],__DIR__."/../uploadedFiles/" . $newFilename);
          // print_r("File Uploaded");
      } else {
          echo "Could Not Upload File " . json_encode($_FILES[$outputFile]);
      }

}
$combinedFilename  = "";
$title = $_POST["manuscript_full_title"];
$type = $_POST["article_type"];
$discipline = $_POST["discipline"];
$manuscript_file = $_FILES["manuscript_file"];
$figures = $_FILES["figures"];
$supplementary_material = $_FILES["supplementary_materials"];
$graphic_abstract = $_FILES["graphic_abstract"];
$tables = $_FILES["tables"];
$submissionStatus = $_POST["review_status"];

$cover_letter = $_FILES["cover_letter"];
$cover_letter_file = "";
$manuscriptFileName = "";
$tablesFileName = "";
$figuresFileName = "";
$supplementaryMaterialsFileName = "";
$graphicAbstractFileName = "";
$cover_letter_file_main = $_FILES["cover_letter"];

$authorsPrefix = [];
$authors_firstname = [];
$authors_lastname = [];
$authors_other_name = [];
$affiliation = [];
$affiliation_country = [];
$affiliation_city = [];
$authorEmail = [];
$authors_orcid = [];

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
$submissionsCount = "";

if (isset($type)) {
    $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `title` = ? AND `status` != 'returned_for_revision' AND `status` != 'saved_for_later'");
    $stmt->bind_param("s", $title);
    if (!$stmt) {
        $response = array("status" => "error", "message" => $con->error);
        echo json_encode($response);
        exit;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $count = mysqli_num_rows($result);
    if ($count > 0) {
        $response = array("status" => "error", "message" => "A submission already exists with this title");
        echo json_encode($response);
        exit;
    }
    // Select Count to add to ID 
    $stmt = $con->prepare("SELECT COUNT(*) AS `totalSubmissions` FROM `submissions` WHERE 1");
    if (!$stmt) {
        $response = array("status" => "error", "message" => $con->error);
        echo json_encode($response);
        exit;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    // $count = mysqli_num_rows($result);
// if($count > 0){
    $row = $result->fetch_assoc();
    $countSub = $row["totalSubmissions"] + 1;
    if ($countSub < 10) {
        $submissionsCount = "0000" . $row["totalSubmissions"] + 1;
    } else if ($countSub > 10 && $countSub < 100) {
        $submissionsCount = "000" . $row["totalSubmissions"] + 1;
    } else if ($countSub > 100 && $countSub < 1000) {
        $submissionsCount = "00" . $row["totalSubmissions"] + 1;
    } else if ($countSub > 1000 && $countSub < 10000) {
        $submissionsCount = "0" . $row["totalSubmissions"] + 1;
    } else {
        $submissionsCount = $row["totalSubmissions"] + 1;

    }

    // }
    $articleID = "ASFIRJ-" . date("Y") . "-" . $submissionsCount;
    $RevisionsId = $articleID;
    $revisionsCount = 0;
    // End Select Count 


    

    // Prepare files for sending to Node.js server
    if ($submissionStatus === "saved_for_later") {
        // Logic For file upload should go here 
        if (isset($cover_letter_file_main) && $cover_letter_file_main["size"] > 0 && isset($_FILES["cover_letter"]["tmp_name"])) {
            $cover_letter_file = "coverLetter" . time() . '-' . basename($cover_letter_file_main["name"]);

            MoveFile("cover_letter", __DIR__ . "/uploadedFiles", $cover_letter_file);
        }
        $tables = $_FILES["tables"];
        if (isset($manuscript_file) && $manuscript_file["size"] > 0 && isset($_FILES["manuscript_file"]["tmp_name"])) {
            $combinedFilename = "manuscriptFile" . time() . '-' . basename($manuscript_file["name"]);

            MoveFile("manuscript_file", __DIR__ . "/uploadedFiles", $combinedFilename);
        }
        if (isset($figures) && $figures["size"] > 0 && isset($_FILES["figures"]["tmp_name"])) {
            $figuresFileName = "figures" . time() . '-' . basename($figures["name"]);

            MoveFile("figures", __DIR__ . "/uploadedFiles", $figuresFileName);
        }
        if (isset($supplementary_material) && $supplementary_material["size"] > 0 && isset($_FILES["supplementary_materials"]["tmp_name"])) {
            $supplementaryMaterialsFileName = "supplementaryMaterial" . time() . '-' . basename($supplementary_material["name"]);

            MoveFile("supplementary_materials", __DIR__ . "/uploadedFiles", $supplementaryMaterialsFileName);
        }
        if (isset($graphic_abstract) && $graphic_abstract["size"] > 0 && isset($_FILES["graphic_abstract"]["tmp_name"])) {
            $graphicAbstractFileName = "graphicAbstract" . time() . '-' . basename($graphic_abstract["name"]);

            MoveFile("graphic_abstract", __DIR__ . "/uploadedFiles", $graphicAbstractFileName);
        }
        if (isset($tables) && $tables["size"] > 0 && isset($_FILES["tables"]["tmp_name"])) {
            $tablesFileName = "tables" . time() . '-' . basename($figures["name"]);
            MoveFile("tables", __DIR__ . "/uploadedFiles", $tablesFileName);
        }


        // then update or insert the file into the database 
        UpdateTheSubmission($type, $RevisionsId, $revisionsCount, $discipline, $title, $combinedFilename, $cover_letter_file, $abstract, $corresponding_author, $articleID, $submissionStatus, $tablesFileName, $figuresFileName, $graphicAbstractFileName, $supplementaryMaterialsFileName,  $authorsPrefix, $authorEmail,$authors_firstname,$authors_lastname, $authors_other_name,  $authors_orcid, $affiliation, $affiliation_country, $affiliation_city, $keywords, $suggested_reviewer_fullname, $suggested_reviewer_affiliation, $suggested_reviewer_country, $suggested_reviewer_city, $suggestedReviewerEmail, $LoggedInauthorsPrefix,$LoggedInauthors_firstname, $LoggedInauthors_lastname, $LoggedInauthors_other_name, $LoggedInauthorEmail, $loggedIn_authors_ORCID, $LoggedInaffiliation, $LoggedInaffiliation_country, $LoggedInaffiliation_city);


    } else {
             // Logic For file upload should go here 
             if(isset($cover_letter_file_main) && $cover_letter_file_main["size"] > 0 && isset($_FILES["cover_letter"]["tmp_name"])){
                $cover_letter_file = "coverLetter".time() . '-' . basename($cover_letter_file_main["name"]);
            
                MoveFile("cover_letter",  __DIR__."/uploadedFiles", $cover_letter_file);
            }
// Path to save the dummy PDF file
$dummyPDFPath = '../temp/dummy.pdf';
// $fields = array(
//     'manuscript_file' => new CURLFile($manuscript_file['tmp_name'], $manuscript_file['type'], $manuscript_file['name']),
// );
        // // Logic For file upload should go here 
        // if(isset($cover_letter_file_main) && $cover_letter_file_main["size"] > 0 && isset($_FILES["cover_letter"]["tmp_name"])){
        //     $cover_letter_file = "coverLetter".time() . '-' . basename($cover_letter_file_main["name"]);
        
        //     MoveFile("cover_letter",  __DIR__."/uploadedFiles", $cover_letter_file);
        // }

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


        // Send files to Node.js server
        $url = "https://asfischolar.org/external/api/combinePDF"; // Replace with your Node.js server URL


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

        if ($response) {
            $responseDecoded = json_decode($response, true);
            if ($responseDecoded['success']) {
                $combinedFilename = $responseDecoded['filename'];
                $combinedFilePath = 'uploads/' . $combinedFilename;


                if ($combinedFilename) {
                    // then update or insert the file into the database 
                    UpdateTheSubmission($type, $RevisionsId, $revisionsCount, $discipline, $title, $combinedFilename, $cover_letter_file, $abstract, $corresponding_author, $articleID, "submitted", $tablesFileName, $figuresFileName, $graphicAbstractFileName, $supplementaryMaterialsFileName,  $authorsPrefix, $authorEmail,$authors_firstname,$authors_lastname, $authors_other_name,  $authors_orcid, $affiliation, $affiliation_country, $affiliation_city, $keywords, $suggested_reviewer_fullname, $suggested_reviewer_affiliation, $suggested_reviewer_country, $suggested_reviewer_city, $suggestedReviewerEmail, $LoggedInauthorsPrefix,$LoggedInauthors_firstname, $LoggedInauthors_lastname, $LoggedInauthors_other_name, $LoggedInauthorEmail, $loggedIn_authors_ORCID, $LoggedInaffiliation, $LoggedInaffiliation_country, $LoggedInaffiliation_city);


                } else {
                    $response = array("status" => "error", "message" => "Error moving combined PDF to designated folder");
                    echo json_encode($response);
                }
            }
        } else {
            $response = array("status" => "error", "message" => "Error combining PDFs");
            echo json_encode($response);
        }
    }
} else {
    $response = array("status" => "error", "message" => "Incomplete Fields");
    echo json_encode($response);
}

