<?php
// AJAX Security Safe Examples - Test Cases

// 1. Proper Nonce Verification
add_action('wp_ajax_my_action', 'my_safe_ajax_handler');
function my_safe_ajax_handler() {
    check_ajax_referer('my_action_nonce', 'security');
    $data = sanitize_text_field($_POST['data']);
    echo esc_html($data);
    wp_die();
}

// 2. Proper Capability Check
add_action('wp_ajax_delete_user', 'delete_user_ajax_safe');
function delete_user_ajax_safe() {
    if (!current_user_can('delete_users')) {
        wp_die('Unauthorized');
    }
    $user_id = intval($_POST['user_id']);
    wp_delete_user($user_id);
    wp_send_json_success('User deleted');
}

// 3. Safe Output with Escaping
add_action('wp_ajax_echo_data', 'echo_ajax_data_safe');
function echo_ajax_data_safe() {
    check_ajax_referer('echo_data_nonce', 'security');
    $data = sanitize_text_field($_POST['data']);
    echo esc_html($data);
    wp_die();
}

// 4. Strong Nonce Verification
add_action('wp_ajax_strong_nonce', 'strong_nonce_handler');
function strong_nonce_handler() {
    check_ajax_referer('strong_nonce_action', 'security');
    $data = sanitize_text_field($_POST['data']);
    echo esc_html($data);
}

// 5. Proper Input Sanitization
add_action('wp_ajax_process_data', 'process_ajax_data_safe');
function process_ajax_data_safe() {
    check_ajax_referer('process_data_nonce', 'security');
    $data = sanitize_text_field($_POST['data']);
    $result = some_safe_function($data);
    wp_send_json_success($result);
}

// 6. Safe SQL Queries with Prepared Statements
add_action('wp_ajax_search_posts', 'search_ajax_posts_safe');
function search_ajax_posts_safe() {
    global $wpdb;
    check_ajax_referer('search_posts_nonce', 'security');
    $search = sanitize_text_field($_POST['search']);
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $wpdb->posts WHERE post_title LIKE %s",
        '%' . $wpdb->esc_like($search) . '%'
    ));
    wp_send_json_success($results);
}

// 7. Safe File Upload
add_action('wp_ajax_upload_file', 'upload_ajax_file_safe');
function upload_ajax_file_safe() {
    check_ajax_referer('upload_file_nonce', 'security');
    if (!current_user_can('upload_files')) {
        wp_die('Unauthorized');
    }
    
    $file = $_FILES['file'];
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
    
    if (!in_array($file['type'], $allowed_types)) {
        wp_send_json_error('Invalid file type');
    }
    
    $upload = wp_handle_upload($file, array('test_form' => false));
    if ($upload['error']) {
        wp_send_json_error($upload['error']);
    }
    
    wp_send_json_success($upload);
}

// 8. Safe User Data Access
add_action('wp_ajax_get_user_data', 'get_ajax_user_data_safe');
function get_ajax_user_data_safe() {
    check_ajax_referer('get_user_data_nonce', 'security');
    
    if (!current_user_can('manage_users')) {
        wp_die('Unauthorized');
    }
    
    $user_id = intval($_POST['user_id']);
    $user = get_user_by('id', $user_id);
    
    if (!$user) {
        wp_send_json_error('User not found');
    }
    
    wp_send_json_success(array(
        'id' => $user->ID,
        'user_login' => $user->user_login,
        'user_email' => $user->user_email,
        'display_name' => $user->display_name
    ));
}

// 9. Proper Error Handling
add_action('wp_ajax_expensive_operation', 'expensive_ajax_operation_safe');
function expensive_ajax_operation_safe() {
    check_ajax_referer('expensive_operation_nonce', 'security');
    
    try {
        $data = sanitize_text_field($_POST['data']);
        $result = some_expensive_operation_safe($data);
        wp_send_json_success($result);
    } catch (Exception $e) {
        wp_send_json_error('Operation failed');
    }
}

// 10. Secure Direct Object Reference
add_action('wp_ajax_get_post', 'get_ajax_post_safe');
function get_ajax_post_safe() {
    check_ajax_referer('get_post_nonce', 'security');
    
    $post_id = intval($_POST['post_id']);
    $post = get_post($post_id);
    
    if (!$post) {
        wp_send_json_error('Post not found');
    }
    
    // Check ownership or capability
    if ($post->post_author != get_current_user_id() && !current_user_can('edit_others_posts')) {
        wp_die('Unauthorized');
    }
    
    wp_send_json_success($post);
}

// 11. Rate Limiting Implementation
add_action('wp_ajax_heavy_operation', 'heavy_ajax_operation_safe');
function heavy_ajax_operation_safe() {
    check_ajax_referer('heavy_operation_nonce', 'security');
    
    $user_id = get_current_user_id();
    $rate_key = "rate_limit_heavy_operation_$user_id";
    
    if (get_transient($rate_key)) {
        wp_send_json_error('Rate limit exceeded. Please try again later.');
    }
    
    set_transient($rate_key, true, 60); // 1 minute rate limit
    
    $data = sanitize_text_field($_POST['data']);
    $result = very_expensive_operation_safe($data);
    wp_send_json_success($result);
}

// 12. Safe JSON Response
add_action('wp_ajax_safe_json', 'safe_json_handler');
function safe_json_handler() {
    check_ajax_referer('safe_json_nonce', 'security');
    $data = sanitize_text_field($_POST['data']);
    $result = array('data' => $data);
    wp_send_json_success($result);
}

// 13. CSRF Protection for Non-Logged Users
add_action('wp_ajax_nopriv_public_action', 'public_ajax_handler_safe');
function public_ajax_handler_safe() {
    check_ajax_referer('public_action_nonce', 'security');
    $data = sanitize_text_field($_POST['data']);
    $result = process_public_data_safe($data);
    wp_send_json_success($result);
}

// 14. Safe Redirect
add_action('wp_ajax_redirect', 'redirect_ajax_handler_safe');
function redirect_ajax_handler_safe() {
    check_ajax_referer('redirect_nonce', 'security');
    $url = esc_url_raw($_POST['redirect_url']);
    
    if (wp_http_validate_url($url)) {
        wp_send_json_success(array('redirect_url' => $url));
    } else {
        wp_send_json_error('Invalid URL');
    }
}

// 15. Content-Type Validation
add_action('wp_ajax_upload_image', 'upload_ajax_image_safe');
function upload_ajax_image_safe() {
    check_ajax_referer('upload_image_nonce', 'security');
    
    if (!current_user_can('upload_files')) {
        wp_die('Unauthorized');
    }
    
    $file = $_FILES['image'];
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
    
    if (!in_array($file['type'], $allowed_types)) {
        wp_send_json_error('Invalid file type');
    }
    
    $upload = wp_handle_upload($file, array('test_form' => false));
    if ($upload['error']) {
        wp_send_json_error($upload['error']);
    }
    
    wp_send_json_success($upload);
}

// 16. Comprehensive Secure AJAX Handler
add_action('wp_ajax_comprehensive_secure', 'comprehensive_secure_handler');
function comprehensive_secure_handler() {
    // 1. Nonce verification
    check_ajax_referer('comprehensive_secure_nonce', 'security');
    
    // 2. Capability check
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    // 3. Input sanitization
    $user_id = intval($_POST['user_id']);
    $data = sanitize_text_field($_POST['data']);
    
    // 4. Rate limiting
    $user_id = get_current_user_id();
    $rate_key = "rate_limit_comprehensive_$user_id";
    if (get_transient($rate_key)) {
        wp_send_json_error('Rate limit exceeded');
    }
    set_transient($rate_key, true, 30);
    
    try {
        // 5. Safe database operations
        global $wpdb;
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT ID, user_login, user_email FROM $wpdb->users WHERE ID = %d",
            $user_id
        ));
        
        if (!$user) {
            wp_send_json_error('User not found');
        }
        
        // 6. Safe output
        wp_send_json_success(array(
            'user_id' => $user->ID,
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'processed_data' => esc_html($data)
        ));
        
    } catch (Exception $e) {
        wp_send_json_error('Operation failed');
    }
}

// 17. Safe File Operations
add_action('wp_ajax_read_file', 'read_ajax_file_safe');
function read_ajax_file_safe() {
    check_ajax_referer('read_file_nonce', 'security');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $file_path = sanitize_text_field($_POST['file_path']);
    $allowed_path = WP_CONTENT_DIR . '/uploads/';
    
    // Prevent directory traversal
    $real_path = realpath($file_path);
    if (!$real_path || strpos($real_path, $allowed_path) !== 0) {
        wp_send_json_error('Invalid file path');
    }
    
    $content = file_get_contents($real_path);
    if ($content === false) {
        wp_send_json_error('Unable to read file');
    }
    
    wp_send_json_success(array('content' => esc_html($content)));
}

// 18. Safe Command Execution (if absolutely necessary)
add_action('wp_ajax_execute_command', 'execute_ajax_command_safe');
function execute_ajax_command_safe() {
    check_ajax_referer('execute_command_nonce', 'security');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $command = sanitize_text_field($_POST['command']);
    $allowed_commands = array('ls', 'pwd', 'whoami');
    
    if (!in_array($command, $allowed_commands)) {
        wp_send_json_error('Command not allowed');
    }
    
    $output = shell_exec(escapeshellcmd($command));
    wp_send_json_success(array('output' => esc_html($output)));
}

// 19. Safe Data Validation
add_action('wp_ajax_validate_input', 'validate_ajax_input_safe');
function validate_ajax_input_safe() {
    check_ajax_referer('validate_input_nonce', 'security');
    
    $email = sanitize_email($_POST['email']);
    $age = intval($_POST['age']);
    
    // Validate email
    if (!is_email($email)) {
        wp_send_json_error('Invalid email address');
    }
    
    // Validate age
    if ($age < 0 || $age > 150) {
        wp_send_json_error('Invalid age');
    }
    
    $user_data = array(
        'email' => $email,
        'age' => $age
    );
    
    wp_send_json_success($user_data);
}

// 20. Safe AJAX with Multiple Security Layers
add_action('wp_ajax_multi_secure', 'multi_secure_handler');
function multi_secure_handler() {
    // Layer 1: Nonce verification
    check_ajax_referer('multi_secure_nonce', 'security');
    
    // Layer 2: Capability check
    if (!current_user_can('edit_posts')) {
        wp_die('Unauthorized');
    }
    
    // Layer 3: Rate limiting
    $user_id = get_current_user_id();
    $rate_key = "rate_limit_multi_secure_$user_id";
    if (get_transient($rate_key)) {
        wp_send_json_error('Rate limit exceeded');
    }
    set_transient($rate_key, true, 60);
    
    // Layer 4: Input sanitization and validation
    $post_id = intval($_POST['post_id']);
    $title = sanitize_text_field($_POST['title']);
    $content = wp_kses_post($_POST['content']);
    
    // Layer 5: Ownership validation
    $post = get_post($post_id);
    if (!$post || ($post->post_author != $user_id && !current_user_can('edit_others_posts'))) {
        wp_die('Unauthorized');
    }
    
    try {
        // Layer 6: Safe operations
        $updated_post = array(
            'ID' => $post_id,
            'post_title' => $title,
            'post_content' => $content
        );
        
        $result = wp_update_post($updated_post);
        
        if (is_wp_error($result)) {
            wp_send_json_error('Update failed');
        }
        
        // Layer 7: Safe response
        wp_send_json_success(array(
            'post_id' => $result,
            'title' => esc_html($title),
            'message' => 'Post updated successfully'
        ));
        
    } catch (Exception $e) {
        wp_send_json_error('Operation failed');
    }
}

// Helper functions
function some_safe_function($data) {
    return esc_html($data);
}

function some_expensive_operation_safe($data) {
    return esc_html($data);
}

function very_expensive_operation_safe($data) {
    return esc_html($data);
}

function process_public_data_safe($data) {
    return esc_html($data);
}
