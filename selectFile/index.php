<?php
include "../backend/cors.php";

function isCloudinaryUrl($url) {
    return preg_match('/https?:\/\/(res\.cloudinary\.com|cloudinary\.com)\//', $url);
}

if (isset($_GET['file'])) {
    $fileName = $_GET['file'];
    
    // Check if it's a Cloudinary URL
    if (isCloudinaryUrl($fileName)) {
        $cloudinaryFile = file_get_contents($fileName);
        if ($cloudinaryFile !== false) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($fileName));
            header('Content-Length: ' . strlen($cloudinaryFile));
            echo $cloudinaryFile;
            exit;
        }
    }
    
    // Assuming Cloudinary URLs have a fixed pattern
    $cloudinaryBaseUrl = "https://res.cloudinary.com/YOUR_CLOUD_NAME/";
    $cloudinaryUrl = $cloudinaryBaseUrl . $fileName;
    
    // Check if file is stored on Cloudinary
    $cloudinaryFile = @file_get_contents($cloudinaryUrl);
    if ($cloudinaryFile !== false) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($fileName));
        header('Content-Length: ' . strlen($cloudinaryFile));
        echo $cloudinaryFile;
        exit;
    }
    
    // Check if the file exists in the local directory
    $filePath = '../uploadedFiles/' . basename($fileName);
    if (file_exists($filePath)) {
        // Serve the file as a download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($fileName));
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        echo json_encode(["error" => "File not found"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "No file specified"]);
}
