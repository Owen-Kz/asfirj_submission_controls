<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['combined_file'])) {
        $file = $_FILES['combined_file'];
        $uploadDir = '../uploadedFiles/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadFile = '../uploadedFiles/' . basename($file['name']);

        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
