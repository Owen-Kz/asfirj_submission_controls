<?php

function MoveFile($outputFile, $designatedDirectory, $newFilename)
{
    // Define the upload directory
    $uploadDir = __DIR__ . "/uploadedFiles/";

    // Ensure the target directory is writable and exists
    if (!is_writable($uploadDir)) {
        die("Target directory is not writable.");
    }

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Define the target file path with the original name
    $manuscriptFile = basename($_FILES[$outputFile]["name"]);
    $targetFile = $uploadDir . $manuscriptFile;

    // // Check if the original file already exists, and delete it if necessary
    // if (file_exists($targetFile)) {
    //     unlink($targetFile);
    // }

    // Attempt to move the uploaded file to the designated directory with the original name
    if (move_uploaded_file($_FILES[$outputFile]["tmp_name"], $targetFile)) {
        
        // Define the path for the renamed copy
        $renamedFilePath = $uploadDir . $newFilename;

        // Copy the file to create a renamed version, keeping the original file
        if (!copy($targetFile, $renamedFilePath)) {
            echo "Error creating the renamed copy of the file.";
        }

    } else {
        echo "Could Not Upload File: " . json_encode($_FILES[$outputFile]);
    }
}
