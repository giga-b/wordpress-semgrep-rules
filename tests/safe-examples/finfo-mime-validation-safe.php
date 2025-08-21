<?php
/**
 * Safe File Upload with finfo_file MIME Validation + wp_unique_filename
 * 
 * This file demonstrates safe file upload patterns that combine:
 * 1. MIME type validation using finfo_file (more reliable than client-provided type)
 * 2. Safe filename generation using wp_unique_filename
 * 3. Proper WordPress upload directory handling
 */

// Safe: Complete MIME validation + safe filename flow
function safe_upload_with_finfo_validation() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['upload_file'];
    
    // Validate file size
    if ($file['size'] > wp_max_upload_size()) {
        return false;
    }
    
    // Use finfo_file for reliable MIME type detection
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        return false;
    }
    
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    // Allow only safe MIME types
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    if (!in_array($mime_type, $allowed_types)) {
        return false;
    }
    
    // Get WordPress upload directory
    $upload_dir = wp_upload_dir();
    $upload_path = $upload_dir['path'];
    
    // Sanitize filename and ensure uniqueness
    $safe_filename = sanitize_file_name($file['name']);
    $unique_filename = wp_unique_filename($upload_path, $safe_filename);
    
    // Construct safe destination path
    $destination = $upload_path . '/' . $unique_filename;
    
    // Move file to safe destination
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $destination;
    }
    
    return false;
}

// Safe: Conditional MIME validation with safe filename
function safe_conditional_upload() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['conditional_file'];
    
    // Basic validation
    if ($file['size'] > wp_max_upload_size()) {
        return false;
    }
    
    // MIME validation using finfo_file
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    // Conditional processing based on MIME type
    if (strpos($mime_type, 'image/') === 0) {
        // Handle image uploads
        $upload_dir = wp_upload_dir();
        $safe_name = sanitize_file_name($file['name']);
        $unique_name = wp_unique_filename($upload_dir['path'], $safe_name);
        $dest = $upload_dir['path'] . '/' . $unique_name;
        
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return $dest;
        }
    } elseif ($mime_type === 'application/pdf') {
        // Handle PDF uploads
        $upload_dir = wp_upload_dir();
        $safe_name = sanitize_file_name($file['name']);
        $unique_name = wp_unique_filename($upload_dir['path'], $safe_name);
        $dest = $upload_dir['path'] . '/' . $unique_name;
        
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return $dest;
        }
    }
    
    return false;
}

// Safe: Advanced validation with multiple checks
function safe_advanced_upload() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['advanced_file'];
    
    // Size validation
    if ($file['size'] > wp_max_upload_size()) {
        return false;
    }
    
    // MIME validation using finfo_file
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    // Additional content validation for images
    if (strpos($mime_type, 'image/') === 0) {
        $image_info = getimagesize($file['tmp_name']);
        if (!$image_info) {
            return false; // Not a valid image
        }
        
        // Check image dimensions
        if ($image_info[0] > 5000 || $image_info[1] > 5000) {
            return false; // Image too large
        }
    }
    
    // Get upload directory and create safe filename
    $upload_dir = wp_upload_dir();
    $safe_filename = sanitize_file_name($file['name']);
    $unique_filename = wp_unique_filename($upload_dir['path'], $safe_filename);
    
    // Ensure upload directory exists
    if (!wp_mkdir_p($upload_dir['path'])) {
        return false;
    }
    
    // Move file to safe destination
    $destination = $upload_dir['path'] . '/' . $unique_filename;
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $destination;
    }
    
    return false;
}

// Safe: Batch upload with validation
function safe_batch_upload($files) {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $upload_dir = wp_upload_dir();
    $results = [];
    
    foreach ($files as $file) {
        // Validate each file
        if ($file['size'] > wp_max_upload_size()) {
            continue;
        }
        
        // MIME validation
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // Allow only safe types
        if (!in_array($mime_type, ['image/jpeg', 'image/png', 'image/gif'])) {
            continue;
        }
        
        // Create safe filename
        $safe_name = sanitize_file_name($file['name']);
        $unique_name = wp_unique_filename($upload_dir['path'], $safe_name);
        $dest = $upload_dir['path'] . '/' . $unique_name;
        
        // Move file
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $results[] = $dest;
        }
    }
    
    return $results;
}
?>
