<?php

function resizeImage($sourcePath, $targetPath, $maxWidth = 800, $maxHeight = 800, $quality = 80) {
    // Get image info
    $imageInfo = getimagesize($sourcePath);
    if ($imageInfo === false) {
        error_log("Failed to get image info for: " . $sourcePath);
        return false;
    }

    $width = $imageInfo[0];
    $height = $imageInfo[1];
    $mimeType = $imageInfo['mime'];

    // Check if resize is needed
    if ($width <= $maxWidth && $height <= $maxHeight) {
        error_log("Image already within size limits, no resize needed");
        return true; // Image is already smaller than max dimensions
    }

    // Calculate new dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = round($width * $ratio);
    $newHeight = round($height * $ratio);

    // Create new image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    if (!$newImage) {
        error_log("Failed to create new image resource");
        return false;
    }

    // Handle transparency for PNG images
    if ($mimeType === 'image/png') {
        // Set transparency
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }

    // Create source image resource
    switch ($mimeType) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        default:
            error_log("Unsupported image type: " . $mimeType);
            return false;
    }

    if (!$sourceImage) {
        error_log("Failed to create source image resource");
        return false;
    }

    // Resize
    if (!imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height)) {
        error_log("Failed to resize image");
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        return false;
    }

    // Save the image
    $success = false;
    switch ($mimeType) {
        case 'image/jpeg':
            $success = imagejpeg($newImage, $targetPath, $quality);
            break;
        case 'image/png':
            // PNG quality is 0-9, convert from 0-100
            $pngQuality = round(9 - (($quality / 100) * 9));
            $success = imagepng($newImage, $targetPath, $pngQuality);
            break;
        case 'image/gif':
            $success = imagegif($newImage, $targetPath);
            break;
    }

    // Clean up
    imagedestroy($sourceImage);
    imagedestroy($newImage);

    if (!$success) {
        error_log("Failed to save resized image");
        return false;
    }

    error_log("Successfully resized image to {$newWidth}x{$newHeight}");
    return true;
}
