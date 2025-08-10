<?php
// REST API Security Safe Examples - Test Cases

// 1. Proper Authentication
register_rest_route('my-plugin/v1', '/data', [
    'methods' => 'GET',
    'callback' => 'get_data_secure',
    'permission_callback' => function($request) {
        return current_user_can('read') && wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest');
    }
]);

function get_data_secure($request) {
    $data = get_option('my_data');
    return array('data' => sanitize_text_field($data));
}

// 2. Strong Authentication with Capability Check
register_rest_route('my-plugin/v1', '/users', [
    'methods' => 'POST',
    'callback' => 'create_user_secure',
    'permission_callback' => function($request) {
        return current_user_can('manage_options') && wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest');
    }
]);

function create_user_secure($request) {
    $user_data = $request->get_param('user_data');
    if (empty($user_data) || !is_array($user_data)) {
        return new WP_Error('invalid_data', 'Invalid user data', array('status' => 400));
    }
    
    $sanitized_data = array();
    foreach ($user_data as $key => $value) {
        $sanitized_data[$key] = sanitize_text_field($value);
    }
    
    return array('status' => 'success', 'data' => $sanitized_data);
}

// 3. Proper Input Validation
register_rest_route('my-plugin/v1', '/search', [
    'methods' => 'GET',
    'callback' => 'search_posts_secure',
    'permission_callback' => function($request) {
        return current_user_can('read');
    }
]);

function search_posts_secure($request) {
    $query = $request->get_param('q');
    
    if (empty($query) || !is_string($query)) {
        return new WP_Error('invalid_query', 'Invalid search query', array('status' => 400));
    }
    
    $query = sanitize_text_field($query);
    $posts = get_posts(array(
        's' => $query,
        'post_status' => 'publish'
    ));
    
    return array('posts' => $posts);
}

// 4. Safe Output (No XSS)
register_rest_route('my-plugin/v1', '/echo', [
    'methods' => 'POST',
    'callback' => 'echo_data_secure',
    'permission_callback' => function($request) {
        return current_user_can('read');
    }
]);

function echo_data_secure($request) {
    $data = $request->get_param('data');
    $sanitized_data = sanitize_text_field($data);
    return array('data' => $sanitized_data, 'status' => 'success');
}

// 5. Proper Nonce Verification
register_rest_route('my-plugin/v1', '/update', [
    'methods' => 'POST',
    'callback' => 'update_data_secure',
    'permission_callback' => function($request) {
        return current_user_can('edit_posts') && wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest');
    }
]);

function update_data_secure($request) {
    $data = $request->get_param('data');
    $sanitized_data = sanitize_text_field($data);
    $result = update_option('my_data', $sanitized_data);
    return array('status' => $result ? 'success' : 'error');
}

// 6. SQL Injection Prevention with Prepared Statements
register_rest_route('my-plugin/v1', '/posts', [
    'methods' => 'GET',
    'callback' => 'get_posts_by_title_secure',
    'permission_callback' => function($request) {
        return current_user_can('read');
    }
]);

function get_posts_by_title_secure($request) {
    global $wpdb;
    $title = $request->get_param('title');
    
    if (empty($title) || !is_string($title)) {
        return new WP_Error('invalid_title', 'Invalid title parameter', array('status' => 400));
    }
    
    $title = sanitize_text_field($title);
    $result = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $wpdb->posts WHERE post_title = %s AND post_status = 'publish'",
        $title
    ));
    
    return array('posts' => $result);
}

// 7. Safe File Upload
register_rest_route('my-plugin/v1', '/upload', [
    'methods' => 'POST',
    'callback' => 'upload_file_secure',
    'permission_callback' => function($request) {
        return current_user_can('upload_files');
    }
]);

function upload_file_secure($request) {
    if (!isset($_FILES['file'])) {
        return new WP_Error('no_file', 'No file uploaded', array('status' => 400));
    }
    
    $file = $_FILES['file'];
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    $file_type = wp_check_filetype($file['name']);
    
    if (!in_array($file_type['ext'], $allowed_types)) {
        return new WP_Error('invalid_file_type', 'Invalid file type', array('status' => 400));
    }
    
    $upload = wp_handle_upload($file, array('test_form' => false));
    
    if (isset($upload['error'])) {
        return new WP_Error('upload_error', $upload['error'], array('status' => 500));
    }
    
    return array('file' => $upload);
}

// 8. Rate Limiting Implementation
register_rest_route('my-plugin/v1', '/public/data', [
    'methods' => 'GET',
    'callback' => 'get_public_data_secure',
    'permission_callback' => function($request) {
        return check_rate_limit($request);
    }
]);

function check_rate_limit($request) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = 'rate_limit_' . md5($ip);
    $limit = 100; // requests per hour
    $current = get_transient($key);
    
    if ($current === false) {
        set_transient($key, 1, HOUR_IN_SECONDS);
        return true;
    }
    
    if ($current >= $limit) {
        return false;
    }
    
    set_transient($key, $current + 1, HOUR_IN_SECONDS);
    return true;
}

function get_public_data_secure($request) {
    $data = get_option('public_data');
    return array('data' => sanitize_text_field($data));
}

// 9. Safe CORS Configuration
add_action('rest_api_init', function() {
    $allowed_origins = array('https://trusted-site.com', 'https://myapp.com');
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_array($origin, $allowed_origins)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST');
        header('Access-Control-Allow-Headers: Content-Type, X-WP-Nonce');
    }
});

// 10. Safe Error Handling
register_rest_route('my-plugin/v1', '/process', [
    'methods' => 'POST',
    'callback' => 'process_data_secure',
    'permission_callback' => function($request) {
        return current_user_can('manage_options');
    }
]);

function process_data_secure($request) {
    try {
        $data = $request->get_param('data');
        $sanitized_data = sanitize_text_field($data);
        $result = some_operation($sanitized_data);
        return array('result' => $result);
    } catch (Exception $e) {
        return new WP_Error('operation_failed', 'Operation failed', array('status' => 500));
    }
}

// 11. Safe Data Exposure (No Sensitive Data)
register_rest_route('my-plugin/v1', '/user', [
    'methods' => 'GET',
    'callback' => 'get_user_data_secure',
    'permission_callback' => function($request) {
        return current_user_can('read');
    }
]);

function get_user_data_secure($request) {
    $user_id = $request->get_param('user_id');
    
    if (empty($user_id) || !is_numeric($user_id)) {
        return new WP_Error('invalid_user_id', 'Invalid user ID', array('status' => 400));
    }
    
    $user = get_user_by('id', intval($user_id));
    
    if (!$user) {
        return new WP_Error('user_not_found', 'User not found', array('status' => 404));
    }
    
    return array(
        'id' => $user->ID,
        'user_login' => $user->user_login,
        'user_email' => $user->user_email,
        'display_name' => $user->display_name
        // Note: user_pass is intentionally excluded
    );
}

// 12. Restricted HTTP Methods
register_rest_route('my-plugin/v1', '/resource', [
    'methods' => 'GET, POST',
    'callback' => 'handle_resource_secure',
    'permission_callback' => function($request) {
        return current_user_can('manage_options');
    }
]);

function handle_resource_secure($request) {
    $method = $request->get_method();
    
    if ($method === 'GET') {
        return array('data' => get_option('resource_data'));
    } elseif ($method === 'POST') {
        $data = $request->get_param('data');
        $sanitized_data = sanitize_text_field($data);
        update_option('resource_data', $sanitized_data);
        return array('status' => 'success');
    }
    
    return new WP_Error('method_not_allowed', 'Method not allowed', array('status' => 405));
}

// 13. Strong Namespace
register_rest_route('my-plugin/v1', '/data', [
    'methods' => 'GET',
    'callback' => 'get_api_data_secure',
    'permission_callback' => function($request) {
        return current_user_can('read');
    }
]);

function get_api_data_secure($request) {
    $data = get_option('my_plugin_api_data');
    return array('data' => sanitize_text_field($data));
}

// 14. Proper Sanitization
register_rest_route('my-plugin/v1', '/save', [
    'methods' => 'POST',
    'callback' => 'save_data_secure',
    'permission_callback' => function($request) {
        return current_user_can('manage_options');
    }
]);

function save_data_secure($request) {
    $data = $request->get_param('data');
    $sanitized_data = sanitize_text_field($data);
    $result = process_data($sanitized_data);
    return array('result' => $result);
}

// 15. Security Headers
register_rest_route('my-plugin/v1', '/secure', [
    'methods' => 'GET',
    'callback' => 'get_secure_data_secure',
    'permission_callback' => function($request) {
        return current_user_can('read');
    }
]);

function get_secure_data_secure($request) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    
    $data = get_data();
    return array('data' => sanitize_text_field($data));
}

// AJAX endpoint with proper nonce verification
add_action('wp_ajax_my_action', 'my_ajax_handler_secure');
function my_ajax_handler_secure() {
    check_ajax_referer('my_nonce_action', 'nonce');
    $data = $_POST['data'];
    $sanitized_data = sanitize_text_field($data);
    echo esc_html($sanitized_data);
    wp_die();
}
