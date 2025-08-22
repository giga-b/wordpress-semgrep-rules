<?php
/**
 * File upload with extension bypass
 * Status: Vulnerable (File Upload)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function file_upload_extension_bypass_vulnerable_function() {
    // VULNERABILITY: File Upload - No validation
    if (isset($_FILES['upload_file'])) {
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/' . $_FILES['upload_file']['name'];
        move_uploaded_file($_FILES['upload_file']['tmp_name'], $file_path); // VULNERABLE: No validation
    }
    
    // Safe operations for contrast
    if (isset($_FILES['safe_upload_file'])) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_info = wp_check_filetype($_FILES['safe_upload_file']['name']);
        if (in_array($file_info['ext'], $allowed_types)) {
            $safe_file_path = $upload_dir['basedir'] . '/' . sanitize_file_name($_FILES['safe_upload_file']['name']);
            move_uploaded_file($_FILES['safe_upload_file']['tmp_name'], $safe_file_path);
        }
    }
    
    return true;
}

add_action('init', 'file_upload_extension_bypass_vulnerable_function');
