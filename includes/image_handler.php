<?php

function handleImageUpload($file, $type = 'note') {
    // Basic validation
    if (!isset($file) || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Set up upload directory
    $uploadDir = __DIR__ . '/../uploads/' . $type . 's/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        chmod($uploadDir, 0777);
    }

    // Generate filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $filename;

    // Attempt to move the file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        chmod($uploadPath, 0644); // Make file readable
        return [
            'success' => true,
            'relative_path' => 'uploads/' . $type . 's/' . $filename
        ];
    }

    return false;
}

function deleteImage($imagePath) {
    if (!empty($imagePath) && file_exists($imagePath)) {
        unlink($imagePath);
    }
}
