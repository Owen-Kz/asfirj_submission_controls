<?php
if (isset($_GET['file'])) {
    $fileName = basename($_GET['file']);
  
    $filePath = '../uploadedFiles/' . $fileName;

    if (file_exists($filePath)) {
        // Serve the file as a download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        echo 'File not found';
    }
} else {
    http_response_code(400);
    echo 'No file specified';
}
