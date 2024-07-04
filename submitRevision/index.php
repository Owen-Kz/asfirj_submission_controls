<?php
include "../backend/cors.php";
include "../backend/db.php";
include "../backend/checkifAuthorExists.php";

session_start();
function MoveFile($outputFile, $designatedDirectory, $newFilename){
    // Move the final merged PDF to the designated folder
$manuscriptFile = basename($_FILES[$outputFile]["name"]);
$targetFile = "../uploadedFiles/". $manuscriptFile;
if (!is_writable("../uploadedFiles/")) {
    die("Target directory is not writable.");
}
if (!file_exists("../uploadedFiles/")) {
    mkdir("../uploadedFiles/", 0777, true);
}
if (move_uploaded_file($_FILES[$outputFile]["tmp_name"], $targetFile)) {
// move_uploaded_file($outputFile["tmp_name"], $targetFile);
rename("../uploadedFiles/". $_FILES[$outputFile]["name"], "../uploadedFiles/".$newFilename);
// print_r("File Uploaded");
}else{
   echo "Could Not Upload File ".json_encode($_FILES[$outputFile]);
}


}
$title = $_POST["manuscript_full_title"];
$type = $_POST["article_type"];
$discipline = $_POST["discipline"];
$manuscript_file = $_FILES["manuscript_file"];
$figures = $_FILES["figures"];
$supplementary_material = $_FILES["supplementary_materials"];
$graphic_abstract = $_FILES["graphic_abstract"];
$tables = $_FILES["tables"];
$manuscriptId = $_POST["manuscript_id"];

$cover_letter = $_FILES["cover_letter"];
$cover_letter_file = "";
$cover_letter_file_main = $_FILES["cover_letter"];

if(isset($cover_letter_file_main) && $cover_letter_file_main["size"] > 0 && isset($_FILES["cover_letter"]["tmp_name"])){
    $cover_letter_file = "coverLetter".time() . '-' . basename($cover_letter_file_main["name"]);

    MoveFile("cover_letter",  __DIR__."/uploadedFiles", $cover_letter_file);
}

$abstract = $_POST["abstract"];
$corresponding_author = $_POST["corresponding_author"];
$Buffer = bin2hex(random_bytes(7));
$articleID = $manuscriptId;

if(isset($title)){
    $stmt = $con->prepare("SELECT * FROM `submissions` WHERE `article_id` = ? AND `status` = 'returned_for_revision' AND `corresponding_authors_email` = ?");
    $stmt->bind_param("ss", $articleID, $corresponding_author);
    if(!$stmt){
        $response = array("status"=>"error", "message" => $con->error);
        echo json_encode($response);
        exit;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $count = mysqli_num_rows($result);
    if($count > 0){
        $row = $result->fetch_assoc();
        $revisionsCount = $row["revisions_count"];
        $newRevisionsCount = (int) $revisionsCount + 1;

        $RevisionsId = $articleID.".R".$newRevisionsCount;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // For Logged in Author
        $LoggedInauthorsPrefix = $_POST["loggedIn_authors_prefix"];
        $LoggedInauthors_firstname = $_POST["loggedIn_authors_first_name"];
        $LoggedInauthors_lastname = $_POST["loggedIn_authors_last_name"];
        $LoggedInauthors_other_name = $_POST["loggedIn_authors_other_name"];
        $LoggedInaffiliation = $_POST["loggedIn_affiliation"];
        $LoggedInaffiliation_country = $_POST["loggedIn_affiliation_country"];
        $LoggedInaffiliation_city = $_POST["loggedIn_affiliation_city"];
        $LoggedInauthorEmail = $_POST["loggedIn_author"];

        $LoggedInauthorsFullname = "$LoggedInauthorsPrefix $LoggedInauthors_firstname $LoggedInauthors_lastname $LoggedInauthors_other_name";
        try {
            $stmt = $con->prepare("INSERT INTO `submission_authors` (`submission_id`, `authors_fullname`, `authors_email`, `affiliations`, `affiliation_country`, `affiliation_city`) VALUES(?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $con->error);
            }
            $stmt->bind_param("ssssss", $articleID, $LoggedInauthorsFullname, $LoggedInauthorEmail, $LoggedInaffiliation, $LoggedInaffiliation_country, $LoggedInaffiliation_city);
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement Author: " . $stmt->error);
            }
        } catch (Exception $e) {
            $response = array('status'=> 'error', 'message' => 'ErrorAuthor:'  . $e->getMessage());
            echo json_encode($response);
            exit;
        }

        // For other Authors 
        if(isset($_POST["authors_prefix"])){
        $authorsPrefix = $_POST["authors_prefix"];
        $authors_firstname = $_POST["authors_first_name"];
        $authors_lastname = $_POST["authors_last_name"];
        $authors_other_name = $_POST["authors_other_name"];
        $affiliation = $_POST["affiliation"];
        $affiliation_country = $_POST["affiliation_country"];
        $affiliation_city = $_POST["affiliation_city"];
        $authorEmail = $_POST["email"];

        for ($i = 0; $i < count($authorEmail); $i++){
            $authorsFullname = "$authorsPrefix[$i] $authors_firstname[$i] $authors_lastname[$i] $authors_other_name[$i]";
            try {
                $stmt = $con->prepare("INSERT INTO `submission_authors` (`submission_id`, `authors_fullname`, `authors_email`, `affiliations`, `affiliation_country`, `affiliation_city`) VALUES(?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    throw new Exception("Failed to prepare statement: " . $con->error);
                }
                $stmt->bind_param("ssssss", $articleID, $authorsFullname, $authorEmail[$i], $affiliation[$i], $affiliation_country[$i], $affiliation_city[$i]);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to execute statement Author: " . $stmt->error);
                }
            } catch (Exception $e) {
                $response = array('status'=> 'error', 'message' => 'ErrorAuthor:'  . $e->getMessage());
                echo json_encode($response);
                exit;
            }
        }
    }
    }

    // Prepare files for sending to Node.js server
    $fields = array(
        'manuscript_file' => new CURLFile($manuscript_file['tmp_name'], $manuscript_file['type'], $manuscript_file['name']),
    );
    if(isset($figures['tmp_name'])){
        $fields["figures"] = new CURLFile($figures['tmp_name'], $figures['type'], $figures['name']);
    }
    
    if(isset($supplementary_material['tmp_name'])){

    $fields['supplementary_material'] = new CURLFile($supplementary_material['tmp_name'], $supplementary_material['type'], $supplementary_material['name']);
}

if(isset($graphic_abstract['tmp_name'])){
    $fields['graphic_abstract'] = new CURLFile($graphic_abstract['tmp_name'], $graphic_abstract['type'], $graphic_abstract['name']);
}

if(isset($tables['tmp_name'])){
        $fields["tables"] = new CURLFile($tables['tmp_name'], $tables['type'], $tables['name']);
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
    $response = array("status"=>"error", "message"=>'Error:' . curl_error($ch));
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
            // Finally UploadDocuments after file has been combined
            $stmt = $con->prepare("INSERT INTO `submissions` (`article_type`,`revision_id`,`revisions_count`, `discipline`, `title`, `manuscript_file`,`cover_letter_file`, `abstract`, `corresponding_authors_email`, `article_id`) VALUES(?,?,?, ?, ?, ?, ?, ?, ?,?)");
            $stmt->bind_param("ssisssssss", $type,$RevisionsId, $revisionsCount, $discipline, $title, $combinedFilename, $cover_letter_file, $abstract, $corresponding_author, $articleID);
            if($stmt->execute()){
                // UPdaet the Status 
                $stmt = $con->prepare("UPDATE `submissions` SET `status` = 'revision_submitted' WHERE `article_id` = ?");
                if(!$stmt){
                    echo json_encode(array("status" => "error", "message" => $stmt->error));
                }
                $stmt->bind_param("s", $articleID);
                $stmt->execute();
                $response = array("status"=>"success", "message"=>"Submission Successful");
                echo json_encode($response);
            } else {
                $response = array("status"=>"error", "message"=>"Could Not Complete Submission");
                echo json_encode($response);
            }
        } else {
            $response = array("status"=>"error", "message"=>"Error moving combined PDF to designated folder");
            echo json_encode($response);
        }
    }
    } else {
        $response = array("status"=>"error", "message"=>"Error combining PDFs");
        echo json_encode($response);
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

