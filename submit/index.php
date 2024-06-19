<?php
include "../backend/cors.php";
include "/backend/db.php";
include "/backend/checkifAuthorExixts.php";
// Convert the Doucments to PDF 
require 'vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader\PdfReader;

session_start();

$title = $_POST["title"];
$type = $_POST["type"];
$manuscript_file = $_FILES["mansuscript_file"];
$cover_letter = $_FILES["cover_letter"];
$figures = $_FILES["figures"];
$supplementary_material = $_FILES["supplementary_material"];
$graphic_abstract = $_FILES["graphic_abstract"];
$abstract = $_POST["abstract"];
$corresponding_author = $_POSt["corresponding_author"];
$Buffer = bin2hex(random_bytes(7)); // 10 bytes = 20 characters in hexadecimal representation
$articleID = "ASFIRJ_submission_$Buffer";

if($title){
// first check if an Article with that title already ecists 
$stmt = $con->prepare("SELECT * FROM `submissions` WHERE `title` =?");
$stmt->bind_param("s", $title);
$stmt->execute();
$result = $stmt->get_result();
$count = mysqli_num_rows($result);
if($count > 0){
    $response = array("status"=>"error", "message" => "A submission already exists with this title");
    echo json_encode($response);
}else{

// Get the Array for authors name and other details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authorsPrefix = $_POST["authors_prefix"];
    $authors_firstname = $_POST["authors_firstname"];
    $authors_lastname = $_POST["authors_lastname"];
    $authors_other_name = $_POST["authors_other_name"];
    $affiliation = $_POST["affiliation"];
    $affiliation_country = $_POST["affiliation_country"];
    $affiliation_city = $_POSt["affiliation_city"];
    $authorEmail = $_POST["authorEmail"];
    

    for ($i = 0; $i<count($authorsPrefix); $i++){
        $authorsFullname = "$authorsPrefix. $authors_firstname[$i] $authors_lastname[$i] $authors_other_name[$i]";

        try {
            $stmt = $con->prepare("INSERT INTO `submission_authors` (`submission_id`, `authors_fullname`, `authors_email`, `affiliations`, `affiliation_country`, `affiliation_city`) VALUES(?, ?, ?, ?, ?, ?, ?)");
        
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $con->error);
            }
        
            $stmt->bind_param("ssssss", $articleID, $authorsFullname, $authorsEmail[$i], $affiliation[$i], $affiliation_country[$i], $affiliation_city[$i]);
            CheckAccountExists($authorsPrefix[$i],$authors_firstname[$i], $authors_lastname[$i],$authors_other_name[$i], $authorsEmail[$i], $affiliation[$i], $affiliation_country[$i], $affiliation_city[$i]);

            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement Author: " . $stmt->error);
            }
        
        } catch (Exception $e) {
   
            $response = array('status'=> 'error', 'message' => 'ErrorAuthor:'  . $e->getMessage());
            echo json_encode($response);
        }

    }
}



// Convert the Doucments to PDF 
// Function to check if the file is a Word document
function isWordDocument($file) {
    $allowedExtensions = ['doc', 'docx'];
    $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
    return in_array($fileExtension, $allowedExtensions);
}

// Function to convert Word document to PDF
function convertWordToPDF($wordFile, $outputFile) {
    $phpWord = IOFactory::load($wordFile);
    $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');
    $pdfWriter->save($outputFile);
}

// Function to merge multiple PDFs into one
function mergePDFs($files, $outputFile) {
    $pdf = new Fpdi();

    foreach ($files as $file) {
        $pageCount = $pdf->setSourceFile($file);
        for ($i = 1; $i <= $pageCount; $i++) {
            $pdf->AddPage();
            $tplId = $pdf->importPage($i);
            $pdf->useTemplate($tplId);
        }
    }

    $pdf->Output('F', $outputFile);
}

// Assuming files are uploaded via a form and stored in $_FILES
$files = [
    'manuscript_file' => $_FILES["manuscript_file"]["tmp_name"],
    'cover_letter' => $_FILES["cover_letter"]["tmp_name"],
    'figures' => $_FILES["figures"]["tmp_name"],
    'supplementary_material' => $_FILES["supplementary_material"]["tmp_name"],
    'graphic_abstract' => $_FILES["graphic_abstract"]["tmp_name"]
];

// Prepare an array to hold the paths of the converted PDFs
$pdfFiles = [];

// Convert Word documents to PDF and collect all PDFs
foreach ($files as $key => $file) {
    if($file){
    if (isWordDocument($file)) {
        $outputFile = tempnam(sys_get_temp_dir(), 'pdf') . '.pdf';
        convertWordToPDF($file, $outputFile);
        $pdfFiles[] = $outputFile;
    } else {
        $pdfFiles[] = $file;  // Assuming it is already a PDF
    }
}
}

// Merge all PDFs into one
$outputFile = 'merged_' . rand(1000, 9999) . '.pdf';
mergePDFs($pdfFiles, $outputFile);

// Move the final merged PDF to the designated folder
$designatedFolder = '/uploadedFiles';
if (!file_exists($designatedFolder)) {
    mkdir($designatedFolder, 0777, true);
}
rename($outputFile, $designatedFolder . $outputFile);

echo "Merged PDF created and moved to designated folder successfully!";

// Finaly UploadDocuments after file has been combined
$stmt = $con->prepare("INSERT INTO `submissions` (`article_type`, `title`, `manuscript_file`, `abstract`, `corresponding_authors_email`, `article_id`), VALUES(?,?,?,?,?)");
$stmt->bind_param($type, $title, $outputFile, $abstract, $corresponding_author, $articleID);
if($stmt->execute()){
    $response = array("status"=>"success", "message"=>"Submission Successfull");
    echo json_encode($response);
}else{
    $response = array("status"=>"error", "message"=>"Could Not Compelte Submission");
    echo json_encode($response);
}

}
}else{
    $response = array("status"=>"error", "message"=>"Incomplete Fields");
    echo json_encode($response);
}