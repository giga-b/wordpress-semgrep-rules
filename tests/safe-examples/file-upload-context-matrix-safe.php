<?php
// File Upload Context Matrix - Safe Examples

// Minimal WordPress stubs for linting
if (!function_exists('wp_handle_upload')) { function wp_handle_upload($f, $a=array()){ return ['file'=>$f['name'],'url'=>'/uploads/'.$f['name']]; } }
if (!function_exists('wp_check_filetype')) { function wp_check_filetype($name){ $ext = pathinfo($name, PATHINFO_EXTENSION); return ['ext'=>$ext,'type'=>'']; } }
if (!function_exists('sanitize_file_name')) { function sanitize_file_name($s){ return $s; } }
if (!function_exists('wp_upload_dir')) { function wp_upload_dir(){ return ['path'=>'/var/www/html/wp-content/uploads','url'=>'/uploads']; } }
if (!function_exists('wp_unique_filename')) { function wp_unique_filename($dir, $name){ return $name; } }
if (!function_exists('add_action')) { function add_action($hook, $cb){ /* no-op */ } }

// 1. Validate file type before upload
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf');
    $type = wp_check_filetype($file['name']);
    if (in_array($type['ext'], $allowed_types, true)) {
        $upload = wp_handle_upload($file, array('test_form' => false));
    }
}

// 2. Safe destination with sanitized filename
if (isset($file)) {
    $uploads = wp_upload_dir();
    $safe_name = sanitize_file_name($file['name']);
    $unique = wp_unique_filename($uploads['path'], $safe_name);
    // Simulate safe move using wp_handle_upload (preferred) instead of direct move
    $upload = wp_handle_upload($file, array('test_form' => false));
}

// 3. REST endpoint with validation
function rest_upload_safe($request){
    if (!isset($_FILES['file'])) { return 'no-file'; }
    $file = $_FILES['file'];
    $type = wp_check_filetype($file['name']);
    $allowed = array('jpg','jpeg','png','gif');
    if (!in_array($type['ext'], $allowed, true)) { return 'invalid'; }
    $upload = wp_handle_upload($file, array('test_form' => false));
    return $upload;
}

// 4. AJAX handler with validation
add_action('wp_ajax_upload_file_safe', 'ajax_upload_safe');
function ajax_upload_safe(){
    if (!isset($_FILES['file'])) { return; }
    $file = $_FILES['file'];
    $type = wp_check_filetype($file['name']);
    $allowed = array('jpg','jpeg','png','gif');
    if (!in_array($type['ext'], $allowed, true)) { return; }
    $upload = wp_handle_upload($file, array('test_form' => false));
}


