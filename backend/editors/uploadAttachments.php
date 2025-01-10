<?php 
require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__. '/../../');
$dotenv->load();
use Cloudinary\Cloudinary;
function uploadToCloudinary($fileTmpPath, $fileName) {
    $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
            'api_key' => $_ENV['CLOUDINARY_API_KEY'],
            'api_secret' => $_ENV['CLOUDINARY_API_SECRET'],
        ],
    ]);

    // Upload file to Cloudinary
    $result = $cloudinary->uploadApi()->upload($fileTmpPath, [
        'folder' => 'asfirj/email_attachments',
        'public_id' => pathinfo($fileName, PATHINFO_FILENAME),
        'resource_type' => 'auto',
    ]);

    return $result['secure_url']; // Return the URL of the uploaded file
}