<?php
// REST API Security Vulnerabilities - Test Cases

// 1. Missing Authentication
register_rest_route('my-plugin/v1', '/data', [
    'methods' => 'GET',
    'callback' => 'get_data',
    'permission_callback' => '__return_true' // No authentication!
]);

function get_data($request) {
    return $_GET['data']; // Direct output without sanitization
}

// 2. Weak Authentication
register_rest_route('my-plugin/v1', '/users', [
    'methods' => 'POST',
    'callback' => 'create_user',
    'permission_callback' => function() { return true; } // Always returns true
]);

function create_user($request) {
    $user_data = $request->get_param('user_data');
    return $user_data; // No validation or sanitization
}

// 3. Missing Input Validation
register_rest_route('my-plugin/v1', '/search', [
    'methods' => 'GET',
    'callback' => 'search_posts',
    'permission_callback' => function() { return current_user_can('read'); }
]);

function search_posts($request) {
    $query = $request->get_param('q');
    return $query; // No validation
}

// 4. Direct Parameter Output (XSS)
register_rest_route('my-plugin/v1', '/echo', [
    'methods' => 'POST',
    'callback' => 'echo_data',
    'permission_callback' => function() { return current_user_can('read'); }
]);

function echo_data($request) {
    $data = $request->get_param('data');
    echo $data; // XSS vulnerability
    return array('status' => 'success');
}

// 5. Missing Nonce Verification
register_rest_route('my-plugin/v1', '/update', [
    'methods' => 'POST',
    'callback' => 'update_data',
    'permission_callback' => function($request) {
        return current_user_can('edit_posts');
    }
]);

function update_data($request) {
    $data = $request->get_param('data');
    // No nonce verification
    return update_option('my_data', $data);
}

// 6. SQL Injection Risk
register_rest_route('my-plugin/v1', '/posts', [
    'methods' => 'GET',
    'callback' => 'get_posts_by_title',
    'permission_callback' => function() { return current_user_can('read'); }
]);

function get_posts_by_title($request) {
    global $wpdb;
    $title = $request->get_param('title');
    $result = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_title = '$title'");
    return $result; // SQL injection vulnerability
}

// 7. Unsafe File Upload
register_rest_route('my-plugin/v1', '/upload', [
    'methods' => 'POST',
    'callback' => 'upload_file',
    'permission_callback' => function() { return current_user_can('upload_files'); }
]);

function upload_file($request) {
    $file = $_FILES['file'];
    move_uploaded_file($file['tmp_name'], '/path/to/upload/' . $file['name']);
    return 'File uploaded'; // No validation
}

// 8. Missing Rate Limiting
register_rest_route('my-plugin/v1', '/public/data', [
    'methods' => 'GET',
    'callback' => 'get_public_data',
    'permission_callback' => '__return_true'
]);

function get_public_data($request) {
    return get_option('public_data'); // No rate limiting
}

// 9. Unsafe CORS Configuration
add_action('rest_api_init', function() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
});

// 10. Error Information Disclosure
register_rest_route('my-plugin/v1', '/process', [
    'methods' => 'POST',
    'callback' => 'process_data',
    'permission_callback' => function() { return current_user_can('manage_options'); }
]);

function process_data($request) {
    try {
        $data = $request->get_param('data');
        $result = some_operation($data);
        return $result;
    } catch (Exception $e) {
        return array(
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ); // Sensitive information disclosure
    }
}

// 11. Sensitive Data Exposure
register_rest_route('my-plugin/v1', '/user', [
    'methods' => 'GET',
    'callback' => 'get_user_data',
    'permission_callback' => function() { return current_user_can('read'); }
]);

function get_user_data($request) {
    $user_id = $request->get_param('user_id');
    $user = get_user_by('id', $user_id);
    return array(
        'id' => $user->ID,
        'user_login' => $user->user_login,
        'user_pass' => $user->user_pass, // Password hash exposed
        'user_email' => $user->user_email
    );
}

// 12. Unsafe HTTP Methods
register_rest_route('my-plugin/v1', '/resource', [
    'methods' => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
    'callback' => 'handle_resource',
    'permission_callback' => function() { return current_user_can('manage_options'); }
]);

function handle_resource($request) {
    return 'Resource handled';
}

// 13. Weak Namespace
register_rest_route('api/v1', '/data', [
    'methods' => 'GET',
    'callback' => 'get_api_data',
    'permission_callback' => function() { return current_user_can('read'); }
]);

function get_api_data($request) {
    return get_option('api_data');
}

// 14. Missing Sanitization
register_rest_route('my-plugin/v1', '/save', [
    'methods' => 'POST',
    'callback' => 'save_data',
    'permission_callback' => function() { return current_user_can('manage_options'); }
]);

function save_data($request) {
    $data = $request->get_param('data');
    $result = process_data($data); // No sanitization
    return $result;
}

// 15. Missing Security Headers
register_rest_route('my-plugin/v1', '/secure', [
    'methods' => 'GET',
    'callback' => 'get_secure_data',
    'permission_callback' => function() { return current_user_can('read'); }
]);

function get_secure_data($request) {
    $data = get_data();
    return $data; // No security headers
}

// AJAX endpoint without nonce verification
add_action('wp_ajax_my_action', 'my_ajax_handler');
function my_ajax_handler() {
    $data = $_POST['data'];
    echo $data; // XSS vulnerability
}
