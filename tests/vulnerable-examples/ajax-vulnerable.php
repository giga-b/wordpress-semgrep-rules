<?php
// AJAX Security Vulnerabilities - Test Cases

// 1. Missing Nonce Verification
add_action('wp_ajax_my_action', 'my_ajax_handler');
function my_ajax_handler() {
    $data = $_POST['data'];
    echo $data; // XSS vulnerability
}

// 2. Missing Capability Check
add_action('wp_ajax_delete_user', 'delete_user_ajax');
function delete_user_ajax() {
    $user_id = $_POST['user_id'];
    wp_delete_user($user_id); // No capability check
}

// 3. Direct Output of AJAX Data (XSS)
add_action('wp_ajax_echo_data', 'echo_ajax_data');
function echo_ajax_data() {
    $data = $_POST['data'];
    echo $data; // XSS vulnerability
    wp_die();
}

// 4. Weak Nonce Verification
add_action('wp_ajax_weak_nonce', 'weak_nonce_handler');
function weak_nonce_handler() {
    check_ajax_referer('nonce', 'security'); // Generic nonce action
    $data = $_POST['data'];
    echo $data;
}

// 5. Missing Input Sanitization
add_action('wp_ajax_process_data', 'process_ajax_data');
function process_ajax_data() {
    $data = $_POST['data'];
    $result = some_function($data); // No sanitization
    echo json_encode($result);
}

// 6. SQL Injection Risk
add_action('wp_ajax_search_posts', 'search_ajax_posts');
function search_ajax_posts() {
    global $wpdb;
    $search = $_POST['search'];
    $results = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_title LIKE '%$search%'");
    echo json_encode($results);
}

// 7. Unsafe File Upload
add_action('wp_ajax_upload_file', 'upload_ajax_file');
function upload_ajax_file() {
    $file = $_FILES['file'];
    move_uploaded_file($file['tmp_name'], '/path/to/upload/' . $file['name']);
    echo 'File uploaded successfully';
}

// 8. Sensitive Data Exposure
add_action('wp_ajax_get_user_data', 'get_ajax_user_data');
function get_ajax_user_data() {
    $user_id = $_POST['user_id'];
    $user = get_user_by('id', $user_id);
    echo json_encode(array(
        'password' => $user->user_pass,
        'email' => $user->user_email,
        'user_login' => $user->user_login
    ));
}

// 9. Missing Error Handling
add_action('wp_ajax_expensive_operation', 'expensive_ajax_operation');
function expensive_ajax_operation() {
    $data = $_POST['data'];
    $result = some_expensive_operation($data);
    echo $result; // No error handling
}

// 10. Insecure Direct Object Reference
add_action('wp_ajax_get_post', 'get_ajax_post');
function get_ajax_post() {
    $post_id = $_POST['post_id'];
    $post = get_post($post_id);
    echo json_encode($post); // No ownership validation
}

// 11. Missing Rate Limiting
add_action('wp_ajax_heavy_operation', 'heavy_ajax_operation');
function heavy_ajax_operation() {
    $data = $_POST['data'];
    $result = very_expensive_operation($data);
    echo json_encode($result); // No rate limiting
}

// 12. Unsafe JSON Response
add_action('wp_ajax_unsafe_json', 'unsafe_json_handler');
function unsafe_json_handler() {
    $data = $_POST['data'];
    $result = array('data' => $data);
    echo json_encode($result);
    wp_die();
}

// 13. Missing CSRF Protection for Non-Logged Users
add_action('wp_ajax_nopriv_public_action', 'public_ajax_handler');
function public_ajax_handler() {
    $data = $_POST['data'];
    process_public_data($data); // No CSRF protection
}

// 14. Unsafe Redirect
add_action('wp_ajax_redirect', 'redirect_ajax_handler');
function redirect_ajax_handler() {
    $url = $_POST['redirect_url'];
    wp_redirect($url); // Open redirect vulnerability
    exit;
}

// 15. Missing Content-Type Validation
add_action('wp_ajax_upload_image', 'upload_ajax_image');
function upload_ajax_image() {
    $file = $_FILES['image'];
    $upload = wp_handle_upload($file, array('test_form' => false));
    echo json_encode($upload); // No content-type validation
}

// 16. AJAX Handler with Multiple Vulnerabilities
add_action('wp_ajax_multi_vuln', 'multi_vulnerable_handler');
function multi_vulnerable_handler() {
    // Missing nonce verification
    // Missing capability check
    $user_id = $_POST['user_id'];
    $data = $_POST['data'];
    
    // SQL injection
    global $wpdb;
    $user = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID = $user_id");
    
    // XSS
    echo $data;
    
    // Sensitive data exposure
    echo json_encode(array(
        'password' => $user->user_pass,
        'email' => $user->user_email
    ));
}

// 17. AJAX Handler with File Path Traversal
add_action('wp_ajax_read_file', 'read_ajax_file');
function read_ajax_file() {
    $file_path = $_POST['file_path'];
    $content = file_get_contents($file_path); // Path traversal vulnerability
    echo $content;
}

// 18. AJAX Handler with Command Injection Risk
add_action('wp_ajax_execute_command', 'execute_ajax_command');
function execute_ajax_command() {
    $command = $_POST['command'];
    $output = shell_exec($command); // Command injection vulnerability
    echo $output;
}

// 19. AJAX Handler with Unsafe Deserialization
add_action('wp_ajax_deserialize_data', 'deserialize_ajax_data');
function deserialize_ajax_data() {
    $data = $_POST['serialized_data'];
    $object = unserialize($data); // Object injection vulnerability
    echo json_encode($object);
}

// 20. AJAX Handler with Missing Input Validation
add_action('wp_ajax_validate_input', 'validate_ajax_input');
function validate_ajax_input() {
    $email = $_POST['email'];
    $age = $_POST['age'];
    
    // No validation
    $user_data = array(
        'email' => $email,
        'age' => $age
    );
    
    echo json_encode($user_data);
}

// Helper functions (these would normally be defined elsewhere)
function some_function($data) {
    return $data;
}

function some_expensive_operation($data) {
    return $data;
}

function very_expensive_operation($data) {
    return $data;
}

function process_public_data($data) {
    return $data;
}

function process_data($data) {
    return $data;
}
