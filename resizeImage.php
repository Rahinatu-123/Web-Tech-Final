<?php

function resizeImage($fileTmpPath, $uploadPath, $maxWidth = 800) {
    // Get the file extension and ensure the file is a valid image
    $fileExtension = strtolower(pathinfo($fileTmpPath, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        return false; // Invalid file type
    }

    // Create an image resource from the uploaded file
    $srcImage = null;
    if ($fileExtension == 'jpg' || $fileExtension == 'jpeg') {
        $srcImage = imagecreatefromjpeg($fileTmpPath);
    } elseif ($fileExtension == 'png') {
        $srcImage = imagecreatefrompng($fileTmpPath);
    } elseif ($fileExtension == 'gif') {
        $srcImage = imagecreatefromgif($fileTmpPath);
    }

    if ($srcImage === null) {
        return false; // Failed to create image resource
    }

    // Get the original dimensions of the image
    $width = imagesx($srcImage);
    $height = imagesy($srcImage);
    
    // Calculate the new dimensions
    $newWidth = $maxWidth;
    $newHeight = floor($height * ($newWidth / $width));

    // Create a new true color image with the new dimensions
    $dstImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency if PNG or GIF
    if ($fileExtension == 'png' || $fileExtension == 'gif') {
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
    }
    
    // Resample the image
    imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save the resized image to the upload directory
    if ($fileExtension == 'jpg' || $fileExtension == 'jpeg') {
        imagejpeg($dstImage, $uploadPath);
    } elseif ($fileExtension == 'png') {
        imagepng($dstImage, $uploadPath);
    } elseif ($fileExtension == 'gif') {
        imagegif($dstImage, $uploadPath);
    }

    // Free up memory
    imagedestroy($srcImage);
    imagedestroy($dstImage);

    return true; // Image resized successfully
}

?>
