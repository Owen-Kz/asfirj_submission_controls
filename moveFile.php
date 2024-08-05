<?php

function MoveFile($outputFile, $designatedDirectory, $newFilename)
{
    // Move the final merged PDF to the designated folder
      // Move the final merged PDF to the designated folder
    //   $manuscriptFile = basename($_FILES[$outputFile]["name"]);
    //   $targetFile = __DIR__."/uploadedFiles/" . $manuscriptFile;
    //   if (!is_writable(__DIR__."/uploadedFiles/")) {
    //     die("Target directory is not writable.");
    // }
    // if (!file_exists(__DIR__."/uploadedFiles/")) {
    //     mkdir(__DIR__."/uploadedFiles/", 0777, true);
    // }
  
    //   if (move_uploaded_file($_FILES[$outputFile]["tmp_name"], $targetFile)) {
    //       // move_uploaded_file($outputFile["tmp_name"], $targetFile);
    //       rename(__DIR__."/uploadedFiles/" . $_FILES[$outputFile]["name"],__DIR__."/uploadedFiles/" . $newFilename);
    //       // print_r("File Uploaded");
    //   } else {
    //       echo "Could Not Upload File " . json_encode($_FILES[$outputFile]);
    //   }

    // Ensure the target directory is writable and exists
$uploadDir = __DIR__ . "/uploadedFiles/";
if (!is_writable($uploadDir)) {
    die("Target directory is not writable.");
}

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Define the target file path
$manuscriptFile = basename($_FILES[$outputFile]["name"]);
$targetFile = $uploadDir . $manuscriptFile;

// Check if the file already exists and delete it if necessary
if (file_exists($targetFile)) {
    unlink($targetFile);
}

// Attempt to move the uploaded file
if (move_uploaded_file($_FILES[$outputFile]["tmp_name"], $targetFile)) {
    // Rename the file if needed (set $newFilename appropriately)
    rename(__DIR__."/uploadedFiles/" . $_FILES[$outputFile]["name"],__DIR__."/uploadedFiles/" . $newFilename);

    // echo "File Uploaded Successfully";
} else {
    echo "Could Not Upload File: " . json_encode($_FILES[$outputFile]);
}


}
