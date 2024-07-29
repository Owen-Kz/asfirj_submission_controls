<?php

function MoveFile($outputFile, $designatedDirectory, $newFilename)
{
    // Move the final merged PDF to the designated folder
      // Move the final merged PDF to the designated folder
      $manuscriptFile = basename($_FILES[$outputFile]["name"]);
      $targetFile = __DIR__."/uploadedFiles/" . $manuscriptFile;
      
  
      if (move_uploaded_file($_FILES[$outputFile]["tmp_name"], $targetFile)) {
          // move_uploaded_file($outputFile["tmp_name"], $targetFile);
          rename(__DIR__."/uploadedFiles/" . $_FILES[$outputFile]["name"],__DIR__."/uploadedFiles/" . $newFilename);
          // print_r("File Uploaded");
      } else {
          echo "Could Not Upload File " . json_encode($_FILES[$outputFile]);
      }

}