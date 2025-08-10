<?php
/**
 * Taint Analysis Safe Examples
 * 
 * This file contains examples that demonstrate proper sanitization
 * and should NOT trigger taint analysis rules. These show how to
 * properly handle untrusted data using WordPress sanitization functions
 * and other security best practices.
 */

// =============================================================================
// XSS PREVENTION - Proper output escaping
// =============================================================================

// Safe XSS prevention with esc_html
$user_input = $_GET['name'];
echo esc_html($user_input); // Safe - should NOT trigger taint analysis

// Safe XSS prevention with esc_attr
$user_input = $_POST['message'];
echo '<input value="' . esc_attr($user_input) . '">'; // Safe - should NOT trigger taint analysis

// Safe XSS prevention with esc_js
$user_input = $_REQUEST['title'];
echo '<script>var title = "' . esc_js($user_input) . '";</script>'; // Safe - should NOT trigger taint analysis

// Safe XSS prevention with sanitize_text_field
$user_input = $_GET['search'];
echo sanitize_text_field($user_input); // Safe - should NOT trigger taint analysis

// =============================================================================
// SQL INJECTION PREVENTION - Proper query preparation
// =============================================================================

// Safe SQL with prepare
$user_id = $_POST['id'];
$wpdb->prepare("SELECT * FROM users WHERE id = %d", $user_id); // Safe - should NOT trigger taint analysis

// Safe SQL with esc_sql
$search = $_POST['search'];
$wpdb->query("SELECT * FROM posts WHERE title LIKE '%" . esc_sql($search) . "%'"); // Safe - should NOT trigger taint analysis

// Safe SQL with type casting
$user_id = $_GET['id'];
$wpdb->prepare("SELECT * FROM users WHERE id = %d", (int)$user_id); // Safe - should NOT trigger taint analysis

// =============================================================================
// FILE INCLUSION PREVENTION - Proper path validation
// =============================================================================

// Safe file inclusion with path validation
$file_path = $_GET['file'];
$safe_path = sanitize_file_name($file_path);
if (file_exists($safe_path) && strpos($safe_path, '../') === false) {
    include $safe_path; // Safe - should NOT trigger taint analysis
}

// Safe file content with validation
$file_path = $_POST['config'];
$safe_path = realpath($file_path);
if ($safe_path && strpos($safe_path, '/var/www/') === 0) {
    $content = file_get_contents($safe_path); // Safe - should NOT trigger taint analysis
}

// =============================================================================
// COMMAND INJECTION PREVENTION - Proper command validation
// =============================================================================

// Safe command execution with validation
$command = $_POST['cmd'];
$allowed_commands = ['ls', 'pwd', 'whoami'];
if (in_array($command, $allowed_commands)) {
    exec($command); // Safe - should NOT trigger taint analysis
}

// Safe command with escaping
$command = $_GET['system_cmd'];
$escaped_command = escapeshellcmd($command);
system($escaped_command); // Safe - should NOT trigger taint analysis

// =============================================================================
// HEADER INJECTION PREVENTION - Proper URL validation
// =============================================================================

// Safe header with URL validation
$redirect_url = $_GET['redirect'];
$safe_url = sanitize_url($redirect_url);
if (filter_var($safe_url, FILTER_VALIDATE_URL)) {
    header("Location: " . $safe_url); // Safe - should NOT trigger taint analysis
}

// =============================================================================
// TYPE CASTING SANITIZATION - Proper type validation
// =============================================================================

// Safe integer casting
$user_id = $_GET['id'];
$safe_id = (int)$user_id;
echo $safe_id; // Safe - should NOT trigger taint analysis

// Safe float casting
$price = $_POST['price'];
$safe_price = (float)$price;
echo $safe_price; // Safe - should NOT trigger taint analysis

// Safe string casting
$text = $_REQUEST['text'];
$safe_text = (string)$text;
echo $safe_text; // Safe - should NOT trigger taint analysis

// Safe array casting
$data = $_POST['data'];
$safe_data = (array)$data;
echo $safe_data['key']; // Safe - should NOT trigger taint analysis

// =============================================================================
// VALIDATION SANITIZATION - Proper input validation
// =============================================================================

// Safe numeric validation
$number = $_GET['number'];
if (is_numeric($number)) {
    echo $number; // Safe - should NOT trigger taint analysis
}

// Safe email validation
$email = $_POST['email'];
if (is_email($email)) {
    echo $email; // Safe - should NOT trigger taint analysis
}

// Safe URL validation
$url = $_REQUEST['url'];
if (is_url($url)) {
    echo $url; // Safe - should NOT trigger taint analysis
}

// =============================================================================
// WORDPRESS SANITIZATION FUNCTIONS - Comprehensive sanitization
// =============================================================================

// Text field sanitization
$text_input = $_GET['text'];
$safe_text = sanitize_text_field($text_input);
echo $safe_text; // Safe - should NOT trigger taint analysis

// Email sanitization
$email_input = $_POST['email'];
$safe_email = sanitize_email($email_input);
echo $safe_email; // Safe - should NOT trigger taint analysis

// URL sanitization
$url_input = $_REQUEST['url'];
$safe_url = sanitize_url($url_input);
echo $safe_url; // Safe - should NOT trigger taint analysis

// Filename sanitization
$filename_input = $_GET['file'];
$safe_filename = sanitize_file_name($filename_input);
echo $safe_filename; // Safe - should NOT trigger taint analysis

// =============================================================================
// COMPLEX SAFE PATTERNS - Multi-step sanitization
// =============================================================================

// Safe multi-step processing
$user_input = $_POST['data'];
$sanitized_data = sanitize_text_field($user_input);
$processed_data = wp_kses_post($sanitized_data);
$final_data = esc_html($processed_data);
echo $final_data; // Safe - should NOT trigger taint analysis

// Safe function with sanitization
function process_safe_data($data) {
    return sanitize_text_field($data);
}

$user_input = $_GET['input'];
$result = process_safe_data($user_input);
echo $result; // Safe - should NOT trigger taint analysis

// Safe array processing
$user_data = $_POST['user_data'];
$name = sanitize_text_field($user_data['name']);
echo $name; // Safe - should NOT trigger taint analysis

// =============================================================================
// CONDITIONAL SANITIZATION - Context-aware sanitization
// =============================================================================

// Conditional sanitization based on context
$user_input = $_GET['content'];
$context = $_GET['context'];

if ($context === 'html') {
    $safe_content = wp_kses_post($user_input); // Safe for HTML context
} elseif ($context === 'text') {
    $safe_content = sanitize_text_field($user_input); // Safe for text context
} else {
    $safe_content = esc_html($user_input); // Safe default
}

echo $safe_content; // Safe - should NOT trigger taint analysis

// =============================================================================
// DATABASE SAFETY - Proper database operations
// =============================================================================

// Safe database query with prepare
$user_id = $_POST['user_id'];
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM users WHERE id = %d AND status = %s",
    $user_id,
    'active'
)); // Safe - should NOT trigger taint analysis

// Safe database query with type casting
$search_term = $_GET['search'];
$safe_search = sanitize_text_field($search_term);
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM posts WHERE title LIKE %s",
    '%' . $wpdb->esc_like($safe_search) . '%'
)); // Safe - should NOT trigger taint analysis

// =============================================================================
// FILE OPERATION SAFETY - Proper file handling
// =============================================================================

// Safe file operations with validation
$file_path = $_GET['file'];
$upload_dir = wp_upload_dir();
$safe_path = realpath($upload_dir['basedir'] . '/' . sanitize_file_name($file_path));

if ($safe_path && strpos($safe_path, $upload_dir['basedir']) === 0) {
    $content = file_get_contents($safe_path); // Safe - should NOT trigger taint analysis
}

// =============================================================================
// COMMAND EXECUTION SAFETY - Proper command handling
// =============================================================================

// Safe command execution with whitelist
$command = $_POST['command'];
$allowed_commands = ['backup', 'cleanup', 'optimize'];

if (in_array($command, $allowed_commands)) {
    $full_command = '/usr/bin/php /path/to/script.php ' . escapeshellarg($command);
    exec($full_command); // Safe - should NOT trigger taint analysis
}

// =============================================================================
// API CALL SAFETY - Proper external API handling
// =============================================================================

// Safe external API call
$api_url = $_GET['api_url'];
$safe_url = sanitize_url($api_url);

if (filter_var($safe_url, FILTER_VALIDATE_URL) && strpos($safe_url, 'https://') === 0) {
    $response = wp_remote_get($safe_url); // Safe - should NOT trigger taint analysis
}

// =============================================================================
// COOKIE SAFETY - Proper cookie handling
// =============================================================================

// Safe cookie usage
$cookie_value = $_COOKIE['user_preference'];
$safe_cookie = sanitize_text_field($cookie_value);
echo $safe_cookie; // Safe - should NOT trigger taint analysis

// =============================================================================
// SERVER VARIABLE SAFETY - Proper server variable handling
// =============================================================================

// Safe server variable usage
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$safe_user_agent = sanitize_text_field($user_agent);
echo $safe_user_agent; // Safe - should NOT trigger taint analysis
