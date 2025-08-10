<?php
// Advanced Security Vulnerabilities - Test Cases
// This file contains sophisticated attack patterns and edge cases

// 1. Advanced Obfuscation Techniques
function obfuscated_xss_vulnerability() {
    $user_input = $_GET['data'];
    $encoded = base64_decode($user_input);
    $decoded = urldecode($encoded);
    echo $decoded; // XSS via obfuscation
}

function obfuscated_sql_injection() {
    global $wpdb;
    $input = $_POST['search'];
    $parts = explode('|', $input);
    $query = "SELECT * FROM {$wpdb->posts} WHERE post_title LIKE '%" . $parts[0] . "%'";
    return $wpdb->get_results($query); // SQL injection via obfuscation
}

// 2. Token Leakage Patterns
function token_leakage_error_log() {
    $api_key = get_option('my_plugin_api_key');
    error_log("API Key for debugging: " . $api_key); // Token leakage
}

function token_leakage_debug_header() {
    $secret_token = wp_generate_password(32);
    header('X-Debug-Token: ' . $secret_token); // Token leakage via headers
}

function token_leakage_admin_notice() {
    $access_token = get_option('oauth_token');
    echo '<div class="notice">Access Token: ' . esc_html($access_token) . '</div>'; // Token leakage
}

// 3. Nonce Confusion Patterns
function nonce_confusion_wrong_action() {
    if (wp_verify_nonce($_POST['nonce'], 'other_plugin_action')) {
        // Using nonce from different plugin/action
        update_option('my_data', $_POST['data']);
    }
}

function nonce_confusion_global_action() {
    if (wp_verify_nonce($_POST['security'], 'global_action')) {
        // Using generic global action
        delete_option('sensitive_data');
    }
}

// 4. Advanced Deserialization Vulnerabilities
function deserialization_header_processor() {
    $custom_header = $_SERVER['HTTP_X_CUSTOM_DATA'];
    $data = maybe_unserialize($custom_header);
    if (is_object($data) && method_exists($data, 'process')) {
        $data->process(); // Dangerous deserialization
    }
}

function deserialization_content_processor() {
    $encoded_data = $_POST['encoded_data'];
    $data = maybe_unserialize(base64_decode($encoded_data));
    if (is_array($data) && isset($data['inject'])) {
        eval($data['inject']); // Code injection via deserialization
    }
}

// 5. Advanced Path Traversal
function path_traversal_obfuscated() {
    $base_path = '/var/www/html/wp-content/uploads/';
    $user_path = $_GET['file'];
    $normalized = str_replace('../', '', $user_path); // Weak normalization
    $full_path = $base_path . $normalized;
    return file_get_contents($full_path); // Path traversal still possible
}

function path_traversal_encoded() {
    $base = '/var/www/html/';
    $path = urldecode($_GET['path']);
    $full = $base . $path;
    include($full); // Path traversal via URL encoding
}

// 6. Advanced SSRF Patterns
function ssrf_wp_remote_obfuscated() {
    $url = base64_decode($_POST['url']);
    $response = wp_remote_get($url); // SSRF via obfuscation
    return $response;
}

function ssrf_redirect_chain() {
    $initial_url = $_GET['url'];
    $response = wp_remote_get($initial_url);
    $final_url = wp_remote_retrieve_header($response, 'location');
    $final_response = wp_remote_get($final_url); // SSRF via redirect chain
}

// 7. Advanced XSS via DOM Manipulation
function xss_dom_manipulation() {
    $user_data = $_POST['data'];
    echo "<script>var data = '" . $user_data . "';</script>"; // XSS via DOM
}

function xss_template_injection() {
    $template = $_GET['template'];
    $data = $_POST['data'];
    echo "<div class='{$template}'>{$data}</div>"; // XSS via template injection
}

// 8. Advanced SQL Injection via Stored Procedures
function sql_injection_stored_procedure() {
    global $wpdb;
    $user_id = $_GET['id'];
    $result = $wpdb->get_results("CALL get_user_data($user_id)"); // SQL injection
    return $result;
}

function sql_injection_union_attack() {
    global $wpdb;
    $search = $_POST['search'];
    $query = "SELECT id, title FROM {$wpdb->posts} WHERE title LIKE '%$search%'";
    $query .= " UNION SELECT user_login, user_pass FROM {$wpdb->users}"; // Union attack
    return $wpdb->get_results($query);
}

// 9. Advanced File Upload Vulnerabilities
function file_upload_extension_bypass() {
    $file = $_FILES['upload'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'png', 'gif'];
    
    if (in_array(strtolower($ext), $allowed)) {
        // Weak extension check - can be bypassed with .php.jpg
        move_uploaded_file($file['tmp_name'], '/uploads/' . $file['name']);
    }
}

function file_upload_mime_bypass() {
    $file = $_FILES['upload'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    
    if (strpos($mime, 'image/') === 0) {
        // MIME type can be spoofed
        move_uploaded_file($file['tmp_name'], '/uploads/' . $file['name']);
    }
}

// 10. Advanced Authentication Bypass
function auth_bypass_weak_comparison() {
    $stored_hash = get_option('admin_hash');
    $user_hash = $_POST['hash'];
    
    if ($stored_hash == $user_hash) { // Weak comparison
        // Authentication bypass possible
        wp_set_current_user(1);
    }
}

function auth_bypass_timing_attack() {
    $correct_token = get_option('secret_token');
    $user_token = $_POST['token'];
    
    if (hash_equals($correct_token, $user_token)) {
        // This is actually secure, but showing the pattern
        return true;
    }
    return false;
}

// 11. Advanced CSRF Patterns
function csrf_weak_origin_check() {
    $origin = $_SERVER['HTTP_ORIGIN'];
    if (strpos($origin, 'trusted.com') !== false) {
        // Weak origin check - can be bypassed with subdomain
        process_sensitive_action($_POST['data']);
    }
}

function csrf_missing_referer() {
    $referer = $_SERVER['HTTP_REFERER'];
    if (empty($referer)) {
        // Missing referer check
        update_option('sensitive_setting', $_POST['value']);
    }
}

// 12. Advanced Information Disclosure
function info_disclosure_debug_mode() {
    if (WP_DEBUG) {
        echo "Database: " . DB_NAME . "<br>";
        echo "User: " . DB_USER . "<br>";
        echo "Host: " . DB_HOST . "<br>";
    }
}

function info_disclosure_error_messages() {
    try {
        $result = some_risky_operation();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage(); // Information disclosure
    }
}

// 13. Advanced Command Injection
function command_injection_obfuscated() {
    $command = base64_decode($_POST['cmd']);
    $output = shell_exec($command); // Command injection
    return $output;
}

function command_injection_encoded() {
    $user_input = $_GET['input'];
    $command = "echo " . escapeshellarg($user_input) . " | wc -l";
    $result = system($command); // Command injection via encoding
}

// 14. Advanced Session Vulnerabilities
function session_fixation() {
    if (isset($_GET['session_id'])) {
        session_id($_GET['session_id']); // Session fixation
    }
    session_start();
}

function session_hijacking() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $user_agent) {
        // Weak session validation
        session_regenerate_id();
    }
}

// 15. Advanced Encryption Vulnerabilities
function weak_encryption() {
    $data = $_POST['sensitive_data'];
    $key = 'static_key_123'; // Weak static key
    $encrypted = base64_encode($data ^ $key); // Weak XOR encryption
    return $encrypted;
}

function weak_randomness() {
    $token = md5(rand()); // Weak randomness
    return $token;
}

// 16. Advanced Business Logic Vulnerabilities
function race_condition() {
    $balance = get_option('user_balance');
    if ($balance >= 100) {
        // Race condition - balance can change between check and update
        update_option('user_balance', $balance - 100);
        process_payment();
    }
}

function privilege_escalation() {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    
    // Missing ownership check
    wp_update_user([
        'ID' => $user_id,
        'role' => $new_role
    ]);
}

// 17. Advanced API Vulnerabilities
function api_mass_assignment() {
    $user_data = $_POST;
    // Mass assignment vulnerability
    wp_insert_user($user_data);
}

function api_insecure_direct_object_reference() {
    $post_id = $_GET['id'];
    $post = get_post($post_id);
    
    // Missing authorization check
    if ($post) {
        echo json_encode($post);
    }
}

// 18. Advanced WordPress-Specific Vulnerabilities
function wp_hook_injection() {
    $hook_name = $_POST['hook'];
    $callback = $_POST['callback'];
    
    // Hook injection vulnerability
    add_action($hook_name, $callback);
}

function wp_option_injection() {
    $option_name = $_POST['option'];
    $option_value = $_POST['value'];
    
    // Option injection vulnerability
    update_option($option_name, $option_value);
}

// 19. Advanced Taint Analysis Edge Cases
function taint_flow_complex() {
    $input = $_GET['data'];
    $filtered = filter_var($input, FILTER_SANITIZE_STRING);
    $encoded = base64_encode($filtered);
    $decoded = base64_decode($encoded);
    echo $decoded; // Taint flow through encoding/decoding
}

function taint_flow_conditional() {
    $user_input = $_POST['input'];
    $is_admin = current_user_can('manage_options');
    
    if ($is_admin) {
        $safe_input = wp_kses_post($user_input);
    } else {
        $safe_input = $user_input; // Taint flow in conditional
    }
    
    echo $safe_input;
}

// 20. Advanced Evasion Techniques
function evasion_unicode_normalization() {
    $input = $_GET['data'];
    $normalized = Normalizer::normalize($input, Normalizer::FORM_C);
    echo $normalized; // XSS via Unicode normalization
}

function evasion_encoding_chain() {
    $input = $_POST['data'];
    $encoded = urlencode(base64_encode($input));
    $decoded = base64_decode(urldecode($encoded));
    echo $decoded; // XSS via encoding chain
}

// Helper functions for testing
function some_risky_operation() {
    throw new Exception("Database connection failed: " . DB_PASSWORD);
}

function process_sensitive_action($data) {
    update_option('sensitive_data', $data);
}

function process_payment() {
    // Payment processing logic
    return true;
}
?>
