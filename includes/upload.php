<?php
/**
 * Life is Character Radio - Image Upload Handler
 * 
 * Location: /includes/upload.php
 * 
 * Image upload with validation, resizing, and security
 */

// Ensure config is loaded
if (!defined('UPLOAD_PATH')) require_once __DIR__ . '/config.php';

/**
 * Upload image file with validation
 * 
 * @param array $file $_FILES array element
 * @param string $directory Target directory (e.g., 'events', 'blog')
 * @param int $maxWidth Maximum width (auto-resize if larger)
 * @param int $maxHeight Maximum height (auto-resize if larger)
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function uploadImage(array $file, string $directory, int $maxWidth = 1200, int $maxHeight = 800): array {
    // Validate upload
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Invalid file upload'];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => getUploadError($file['error'])];
    }
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File too large (max ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB)'];
    }
    
    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type. Only images allowed'];
    }
    
    // Validate extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMAGE_EXTENSIONS)) {
        return ['success' => false, 'error' => 'Invalid file extension'];
    }
    
    // Verify it's an actual image
    if (!getimagesize($file['tmp_name'])) {
        return ['success' => false, 'error' => 'File is not a valid image'];
    }
    
    // Generate unique filename
    $filename = generateUniqueFilename($ext);
    $targetDir = UPLOAD_PATH . $directory . '/';
    $targetPath = $targetDir . $filename;
    
    // Create directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Resize and save image
    if (resizeImage($file['tmp_name'], $targetPath, $maxWidth, $maxHeight, $mimeType)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $directory . '/' . $filename,
            'url' => UPLOAD_URL . $directory . '/' . $filename
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to process image'];
}

/**
 * Resize image if necessary and save
 * 
 * @param string $source Source file path
 * @param string $destination Destination file path
 * @param int $maxWidth Maximum width
 * @param int $maxHeight Maximum height
 * @param string $mimeType Image MIME type
 * @return bool True if successful
 */
function resizeImage(string $source, string $destination, int $maxWidth, int $maxHeight, string $mimeType): bool {
    list($width, $height) = getimagesize($source);
    
    // Calculate new dimensions
    if ($width <= $maxWidth && $height <= $maxHeight) {
        // No resize needed, just copy
        return copy($source, $destination);
    }
    
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);
    
    // Create image resource based on type
    $sourceImage = match($mimeType) {
        'image/jpeg' => imagecreatefromjpeg($source),
        'image/png' => imagecreatefrompng($source),
        'image/gif' => imagecreatefromgif($source),
        'image/webp' => imagecreatefromwebp($source),
        default => false
    };
    
    if (!$sourceImage) return false;
    
    // Create new image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Resize
    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save based on type
    $result = match($mimeType) {
        'image/jpeg' => imagejpeg($newImage, $destination, 90),
        'image/png' => imagepng($newImage, $destination, 9),
        'image/gif' => imagegif($newImage, $destination),
        'image/webp' => imagewebp($newImage, $destination, 90),
        default => false
    };
    
    // Cleanup
    imagedestroy($sourceImage);
    imagedestroy($newImage);
    
    return $result;
}

/**
 * Delete uploaded image
 * 
 * @param string $path Relative path (e.g., 'events/image.jpg')
 * @return bool True if deleted
 */
function deleteImage(string $path): bool {
    $fullPath = UPLOAD_PATH . ltrim($path, '/');
    return file_exists($fullPath) ? unlink($fullPath) : false;
}

/**
 * Generate unique filename
 * 
 * @param string $extension File extension
 * @return string Unique filename
 */
function generateUniqueFilename(string $extension): string {
    return uniqid('img_', true) . '_' . time() . '.' . $extension;
}

/**
 * Get human-readable upload error message
 * 
 * @param int $code PHP upload error code
 * @return string Error message
 */
function getUploadError(int $code): string {
    return match($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File too large',
        UPLOAD_ERR_PARTIAL => 'File partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
        UPLOAD_ERR_EXTENSION => 'Upload blocked by extension',
        default => 'Unknown upload error'
    };
}

/**
 * Validate and sanitize filename
 * 
 * @param string $filename Original filename
 * @return string Sanitized filename
 */
function sanitizeFilename(string $filename): string {
    $filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);
    return substr($filename, 0, 255);
}
