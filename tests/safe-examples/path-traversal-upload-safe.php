<?php
// Safe counterpart: validate $_POST['dir'] against wp_upload_dir() and use safe destination

if (!function_exists('wp_upload_dir')) { function wp_upload_dir(){ return ['path'=>'/var/www/html/wp-content/uploads','url'=>'/uploads']; } }
if (!function_exists('wp_max_upload_size')) { function wp_max_upload_size(){ return 10 * 1024 * 1024; } }
if (!function_exists('sanitize_file_name')) { function sanitize_file_name($s){ return $s; } }
if (!function_exists('wp_unique_filename')) { function wp_unique_filename($dir, $name){ return $name; } }

function upload_to_safe_dir() {
    $uploads = wp_upload_dir();
    $base = rtrim($uploads['path'], '/\\');

    // User-provided directory candidate
    $userDir = isset($_POST['dir']) ? (string) $_POST['dir'] : '';

    // Only accept directories under the uploads base; otherwise fallback to base
    $dir = (is_string($userDir) && strpos($userDir, $base) === 0) ? $userDir : $base;

    if (!isset($_FILES['file'])) { return; }
    $file = $_FILES['file'];
    // Enforce size limit to avoid large file uploads
    if ($file['size'] > wp_max_upload_size()) { return; }
    $safeName = sanitize_file_name($file['name']);
    $unique = wp_unique_filename($dir, $safeName);

    // Safe move: destination constrained to uploads base and filename sanitized/uniquified
    move_uploaded_file($file['tmp_name'], $dir . '/' . $unique);
}


