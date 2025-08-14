<?php
// File Upload Context Matrix - Vulnerable Examples

// Minimal WordPress stubs for linting
if (!function_exists('wp_handle_upload')) { function wp_handle_upload($f, $a=array()){ return ['file'=>$f['name'],'url'=>'/uploads/'.$f['name']]; } }
if (!function_exists('wp_check_filetype')) { function wp_check_filetype($name){ $ext = pathinfo($name, PATHINFO_EXTENSION); return ['ext'=>$ext,'type'=>'']; } }
if (!function_exists('sanitize_file_name')) { function sanitize_file_name($s){ return $s; } }
if (!function_exists('wp_upload_dir')) { function wp_upload_dir(){ return ['path'=>'/var/www/html/wp-content/uploads','url'=>'/uploads']; } }
if (!function_exists('wp_unique_filename')) { function wp_unique_filename($dir, $name){ return $name; } }
if (!function_exists('is_uploaded_file')) { function is_uploaded_file($p){ return true; } }
if (!function_exists('unzip_file')) { function unzip_file($zip, $dest){ return true; } }
if (!function_exists('add_action')) { function add_action($hook, $cb){ /* no-op */ } }

// 1. Direct move_uploaded_file to user-provided name (no validation)
$f = $_FILES['file'];
move_uploaded_file($f['tmp_name'], $f['name']); // vulnerable

// 2. Arbitrary destination using user-controlled path
$dir = $_POST['dir'];
move_uploaded_file($f['tmp_name'], $dir . '/' . $f['name']); // vulnerable

// 3. Using wp_handle_upload without validating content type
$upload = wp_handle_upload($f, array('test_form' => false)); // vulnerable (missing allowlist)

// 4. REST endpoint with unsafe upload
function rest_upload_vuln($request){
    $file = $_FILES['file'];
    move_uploaded_file($file['tmp_name'], '/var/www/html/uploads/' . $file['name']); // vulnerable
    return 'ok';
}

// 5. AJAX handler with unsafe upload
add_action('wp_ajax_upload_file_vuln', 'ajax_upload_vuln');
function ajax_upload_vuln(){
    $FILE = $_FILES['file'];
    move_uploaded_file($FILE['tmp_name'], $FILE['name']); // vulnerable
}

// 5b. AJAX handler matching missing content-type validation
add_action('wp_ajax_upload_image', 'upload_ajax_image');
function upload_ajax_image() {
    $FILE = $_FILES['image'];
    $upload = wp_handle_upload($FILE, array('test_form' => false)); // vulnerable: no allowlist
}

// 6. ZIP extraction without path validation
$zip = $_GET['zip'];
unzip_file($zip, '/var/www/html/'); // vulnerable

// 7. Direct unsafe upload matching basic config pattern
move_uploaded_file($_FILES['file']['tmp_name'], $_FILES['file']['name']); // vulnerable exact pattern

// 8. REST API endpoint matching rest-api-security literal pattern
function my_callback($request) {
    $file = $_FILES['file'];
    move_uploaded_file($file['tmp_name'], '/path/to/upload/' . $file['name']); // vulnerable literal path
    return 'File uploaded';
}

// 8b. REST route registering the unsafe callback
if (!function_exists('register_rest_route')) { function register_rest_route($ns,$route,$args){ /* no-op */ } }
register_rest_route('my-plugin/v1','/upload', array(
    'methods' => 'POST',
    'callback' => 'my_callback',
    'permission_callback' => function(){ return true; }
));


