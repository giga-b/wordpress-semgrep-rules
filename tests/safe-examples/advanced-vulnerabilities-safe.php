<?php
// Advanced Security Vulnerabilities - Safe Examples
// This file contains secure implementations for advanced attack patterns

// 1. Advanced Obfuscation Protection
function secure_xss_protection() {
    $user_input = $_GET['data'];
    $encoded = base64_decode($user_input);
    $decoded = urldecode($encoded);
    echo esc_html($decoded); // Proper escaping
}

function secure_sql_protection() {
    global $wpdb;
    $input = $_POST['search'];
    $parts = explode('|', $input);
    $query = $wpdb->prepare(
        "SELECT * FROM {$wpdb->posts} WHERE post_title LIKE %s",
        '%' . $wpdb->esc_like($parts[0]) . '%'
    );
    return $wpdb->get_results($query); // Proper SQL preparation
}

// 2. Secure Token Handling
function secure_token_logging() {
    $api_key = get_option('my_plugin_api_key');
    error_log("API request processed successfully"); // No token leakage
}

function secure_debug_headers() {
    $request_id = wp_generate_uuid4();
    header('X-Request-ID: ' . $request_id); // Safe debug header
}

function secure_admin_notice() {
    $status = get_option('plugin_status');
    echo '<div class="notice">Plugin Status: ' . esc_html($status) . '</div>'; // Safe notice
}

// 3. Secure Nonce Usage
function secure_nonce_verification() {
    if (wp_verify_nonce($_POST['nonce'], 'my_plugin_action')) {
        // Using correct nonce action
        update_option('my_data', sanitize_text_field($_POST['data']));
    }
}

function secure_nonce_creation() {
    $nonce = wp_create_nonce('my_plugin_action');
    return $nonce; // Proper nonce creation
}

// 4. Secure Deserialization
function secure_deserialization() {
    $custom_header = $_SERVER['HTTP_X_CUSTOM_DATA'];
    $data = maybe_unserialize($custom_header);
    
    // Validate data structure before use
    if (is_array($data) && isset($data['allowed_field'])) {
        $safe_data = sanitize_text_field($data['allowed_field']);
        return $safe_data;
    }
    return false;
}

function secure_content_processing() {
    $encoded_data = $_POST['encoded_data'];
    $data = maybe_unserialize(base64_decode($encoded_data));
    
    // Whitelist approach
    $allowed_fields = ['title', 'content', 'author'];
    $safe_data = array_intersect_key($data, array_flip($allowed_fields));
    return array_map('sanitize_text_field', $safe_data);
}

// 5. Secure Path Handling
function secure_path_validation() {
    $base_path = '/var/www/html/wp-content/uploads/';
    $user_path = $_GET['file'];
    
    // Strong path validation
    $real_path = realpath($base_path . $user_path);
    if ($real_path && strpos($real_path, $base_path) === 0) {
        return file_get_contents($real_path);
    }
    return false;
}

function secure_path_sanitization() {
    $base = '/var/www/html/';
    $path = sanitize_file_name($_GET['path']);
    $full = $base . $path;
    
    // Additional validation
    if (file_exists($full) && is_readable($full)) {
        return file_get_contents($full);
    }
    return false;
}

// 6. Secure SSRF Protection
function secure_url_validation() {
    $url = base64_decode($_POST['url']);
    
    // URL validation and whitelist
    $allowed_domains = ['api.example.com', 'trusted-service.com'];
    $parsed_url = parse_url($url);
    
    if (in_array($parsed_url['host'], $allowed_domains)) {
        $response = wp_remote_get($url);
        return $response;
    }
    return false;
}

function secure_redirect_handling() {
    $initial_url = $_GET['url'];
    
    // Validate initial URL
    if (filter_var($initial_url, FILTER_VALIDATE_URL)) {
        $response = wp_remote_get($initial_url, [
            'timeout' => 5,
            'redirection' => 0 // Disable redirects
        ]);
        return $response;
    }
    return false;
}

// 7. Secure XSS Protection
function secure_dom_handling() {
    $user_data = $_POST['data'];
    $safe_data = wp_json_encode(esc_js($user_data));
    echo "<script>var data = {$safe_data};</script>"; // Secure DOM handling
}

function secure_template_rendering() {
    $template = sanitize_html_class($_GET['template']);
    $data = sanitize_text_field($_POST['data']);
    echo "<div class='{$template}'>" . esc_html($data) . "</div>"; // Secure template
}

// 8. Secure SQL Operations
function secure_stored_procedure() {
    global $wpdb;
    $user_id = intval($_GET['id']);
    $result = $wpdb->get_results($wpdb->prepare(
        "CALL get_user_data(%d)",
        $user_id
    )); // Secure stored procedure call
    return $result;
}

function secure_union_query() {
    global $wpdb;
    $search = sanitize_text_field($_POST['search']);
    $query = $wpdb->prepare(
        "SELECT id, title FROM {$wpdb->posts} WHERE title LIKE %s",
        '%' . $wpdb->esc_like($search) . '%'
    );
    return $wpdb->get_results($query); // Secure query without union
}

// 9. Secure File Upload
function secure_file_upload() {
    $file = $_FILES['upload'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'png', 'gif'];
    
    if (in_array($ext, $allowed)) {
        // Additional validation
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($mime, $allowed_mimes)) {
            $safe_name = sanitize_file_name($file['name']);
            $upload_path = '/uploads/' . $safe_name;
            return move_uploaded_file($file['tmp_name'], $upload_path);
        }
    }
    return false;
}

// 10. Secure Authentication
function secure_hash_comparison() {
    $stored_hash = get_option('admin_hash');
    $user_hash = $_POST['hash'];
    
    if (hash_equals($stored_hash, $user_hash)) { // Secure comparison
        wp_set_current_user(1);
        return true;
    }
    return false;
}

function secure_token_validation() {
    $correct_token = get_option('secret_token');
    $user_token = $_POST['token'];
    
    if (hash_equals($correct_token, $user_token)) {
        return true;
    }
    return false;
}

// 11. Secure CSRF Protection
function secure_origin_validation() {
    $origin = $_SERVER['HTTP_ORIGIN'];
    $allowed_origins = ['https://trusted.com', 'https://app.trusted.com'];
    
    if (in_array($origin, $allowed_origins)) {
        // Additional nonce verification
        if (wp_verify_nonce($_POST['nonce'], 'csrf_action')) {
            process_sensitive_action(sanitize_text_field($_POST['data']));
        }
    }
}

function secure_referer_check() {
    $referer = $_SERVER['HTTP_REFERER'];
    $parsed_referer = parse_url($referer);
    
    if ($parsed_referer && $parsed_referer['host'] === $_SERVER['HTTP_HOST']) {
        // Additional nonce verification
        if (wp_verify_nonce($_POST['nonce'], 'secure_action')) {
            update_option('sensitive_setting', sanitize_text_field($_POST['value']));
        }
    }
}

// 12. Secure Information Handling
function secure_debug_information() {
    if (WP_DEBUG) {
        echo "Debug mode is enabled"; // No sensitive information
    }
}

function secure_error_handling() {
    try {
        $result = some_secure_operation();
    } catch (Exception $e) {
        error_log("Operation failed: " . $e->getMessage()); // Log instead of display
        echo "An error occurred. Please try again."; // Generic message
    }
}

// 13. Secure Command Execution
function secure_command_execution() {
    $user_input = sanitize_text_field($_POST['input']);
    $allowed_commands = ['ls', 'pwd', 'whoami'];
    
    if (in_array($user_input, $allowed_commands)) {
        $output = shell_exec(escapeshellcmd($user_input));
        return esc_html($output);
    }
    return false;
}

// 14. Secure Session Management
function secure_session_handling() {
    // Regenerate session ID on login
    if (!session_id()) {
        session_start();
    }
    session_regenerate_id(true);
}

function secure_session_validation() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    if (isset($_SESSION['user_agent']) && 
        isset($_SESSION['ip_address']) &&
        $_SESSION['user_agent'] === $user_agent &&
        $_SESSION['ip_address'] === $ip_address) {
        return true;
    }
    return false;
}

// 15. Secure Encryption
function secure_encryption() {
    $data = $_POST['sensitive_data'];
    $key = wp_salt('auth'); // Use WordPress salt
    $encrypted = wp_encrypt_data($data, $key);
    return $encrypted;
}

function secure_randomness() {
    $token = wp_generate_password(32, false); // Secure random token
    return $token;
}

// 16. Secure Business Logic
function secure_race_condition_handling() {
    // Use database transactions
    global $wpdb;
    $wpdb->query('START TRANSACTION');
    
    $balance = get_option('user_balance');
    if ($balance >= 100) {
        update_option('user_balance', $balance - 100);
        $payment_result = process_payment();
        
        if ($payment_result) {
            $wpdb->query('COMMIT');
            return true;
        } else {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }
    
    $wpdb->query('ROLLBACK');
    return false;
}

function secure_privilege_management() {
    $user_id = intval($_POST['user_id']);
    $new_role = sanitize_text_field($_POST['role']);
    
    // Check ownership and permissions
    if (current_user_can('edit_users') && 
        ($user_id === get_current_user_id() || current_user_can('promote_users'))) {
        
        $allowed_roles = ['subscriber', 'contributor', 'author', 'editor'];
        if (in_array($new_role, $allowed_roles)) {
            wp_update_user([
                'ID' => $user_id,
                'role' => $new_role
            ]);
            return true;
        }
    }
    return false;
}

// 17. Secure API Handling
function secure_mass_assignment() {
    $user_data = $_POST;
    $allowed_fields = ['user_login', 'user_email', 'display_name'];
    $safe_data = array_intersect_key($user_data, array_flip($allowed_fields));
    
    // Sanitize each field
    $safe_data = array_map('sanitize_text_field', $safe_data);
    wp_insert_user($safe_data);
}

function secure_object_reference() {
    $post_id = intval($_GET['id']);
    $post = get_post($post_id);
    
    // Check permissions
    if ($post && (current_user_can('read_private_posts') || 
                  $post->post_status === 'publish' ||
                  $post->post_author === get_current_user_id())) {
        echo wp_json_encode($post);
    }
}

// 18. Secure WordPress Operations
function secure_hook_management() {
    $hook_name = sanitize_key($_POST['hook']);
    $callback = sanitize_text_field($_POST['callback']);
    
    // Validate hook name and callback
    $allowed_hooks = ['my_plugin_action', 'my_plugin_filter'];
    if (in_array($hook_name, $allowed_hooks) && function_exists($callback)) {
        add_action($hook_name, $callback);
    }
}

function secure_option_management() {
    $option_name = sanitize_key($_POST['option']);
    $option_value = sanitize_text_field($_POST['value']);
    
    // Validate option name
    $allowed_options = ['my_plugin_setting', 'my_plugin_config'];
    if (in_array($option_name, $allowed_options)) {
        update_option($option_name, $option_value);
    }
}

// 19. Secure Taint Analysis
function secure_taint_flow() {
    $input = $_GET['data'];
    $filtered = wp_kses_post($input); // Proper sanitization
    $encoded = base64_encode($filtered);
    $decoded = base64_decode($encoded);
    echo esc_html($decoded); // Safe output
}

function secure_conditional_flow() {
    $user_input = $_POST['input'];
    $is_admin = current_user_can('manage_options');
    
    if ($is_admin) {
        $safe_input = wp_kses_post($user_input);
    } else {
        $safe_input = sanitize_text_field($user_input); // Always sanitize
    }
    
    echo esc_html($safe_input);
}

// 20. Secure Evasion Protection
function secure_unicode_handling() {
    $input = $_GET['data'];
    $normalized = Normalizer::normalize($input, Normalizer::FORM_C);
    echo esc_html($normalized); // Safe Unicode handling
}

function secure_encoding_handling() {
    $input = $_POST['data'];
    $encoded = urlencode(base64_encode($input));
    $decoded = base64_decode(urldecode($encoded));
    echo esc_html($decoded); // Safe encoding handling
}

// Helper functions
function some_secure_operation() {
    // Secure operation implementation
    return true;
}

function process_sensitive_action($data) {
    update_option('sensitive_data', sanitize_text_field($data));
}

function process_payment() {
    // Secure payment processing
    return true;
}
?>
