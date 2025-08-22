<?php
/**
 * Basic path traversal vulnerability
 * Status: Vulnerable (Path Traversal)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function path_traversal_basic_vulnerable_function() {
    // VULNERABILITY: Path Traversal - Direct file access
    $file_path = $_GET['file'] ?? '';
    $full_path = ABSPATH . $file_path; // VULNERABLE: Path traversal possible
    $content = file_get_contents($full_path);
    
    // Safe operations for contrast
    $safe_file = sanitize_file_name($_GET['safe_file'] ?? '');
    $safe_full_path = ABSPATH . 'wp-content/uploads/' . $safe_file;
    if (strpos(realpath($safe_full_path), ABSPATH . 'wp-content/uploads/') === 0) {
        $safe_content = file_get_contents($safe_full_path);
    }
    
    return $content;
}

add_action('init', 'path_traversal_basic_vulnerable_function');
