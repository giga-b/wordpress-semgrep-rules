<?php
/**
 * Vulnerable WordPress File Upload Examples
 * 
 * This file demonstrates unsafe file upload patterns that should be detected
 * even with WordPress media handler suppressions in place.
 */

// VULNERABLE: Direct move_uploaded_file without validation
function vulnerable_direct_move() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['vulnerable_file'];
    
    // VULNERABLE: Direct move without type validation
    move_uploaded_file($file['tmp_name'], '/uploads/' . $file['name']);
    
    return true;
}

// VULNERABLE: move_uploaded_file with weak validation
function vulnerable_weak_validation() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['weak_validation_file'];
    
    // VULNERABLE: Only checks file size, not type
    if ($file['size'] < wp_max_upload_size()) {
        move_uploaded_file($file['tmp_name'], '/uploads/' . $file['name']);
    }
    
    return true;
}

// VULNERABLE: move_uploaded_file with client-provided type
function vulnerable_client_type() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['client_type_file'];
    
    // VULNERABLE: Relies on client-provided MIME type
    if (in_array($file['type'], ['image/jpeg', 'image/png'])) {
        move_uploaded_file($file['tmp_name'], '/uploads/' . $file['name']);
    }
    
    return true;
}

// VULNERABLE: move_uploaded_file without sanitization
function vulnerable_no_sanitization() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['no_sanitization_file'];
    
    // VULNERABLE: No filename sanitization
    $destination = '/uploads/' . $file['name'];
    move_uploaded_file($file['tmp_name'], $destination);
    
    return true;
}

// VULNERABLE: move_uploaded_file with path traversal
function vulnerable_path_traversal() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['path_traversal_file'];
    $user_dir = $_POST['user_directory']; // User controlled
    
    // VULNERABLE: User-controlled directory without validation
    $destination = $user_dir . '/' . $file['name'];
    move_uploaded_file($file['tmp_name'], $destination);
    
    return true;
}

// VULNERABLE: move_uploaded_file after media handler failure
function vulnerable_media_handler_fallback() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['fallback_file'];
    $post_id = get_the_ID();
    
    // Try WordPress handler first
    $attachment_id = media_handle_upload('fallback_file', $post_id);
    
    if (is_wp_error($attachment_id)) {
        // VULNERABLE: Falls back to unsafe direct move
        move_uploaded_file($file['tmp_name'], '/uploads/' . $file['name']);
    }
    
    return $attachment_id;
}

// VULNERABLE: move_uploaded_file with async scan after move
function vulnerable_async_scan_after_move() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['async_scan_file'];
    
    // VULNERABLE: Move first, scan later
    move_uploaded_file($file['tmp_name'], '/uploads/' . $file['name']);
    
    // Schedule scan after file is already in final location
    wp_schedule_single_event(time() + 300, 'scan_uploaded_file', ['/uploads/' . $file['name']]);
    
    return true;
}

// VULNERABLE: move_uploaded_file without malware scan
function vulnerable_no_malware_scan() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['no_scan_file'];
    
    // VULNERABLE: No malware scanning
    move_uploaded_file($file['tmp_name'], '/uploads/' . $file['name']);
    
    return true;
}
?>
