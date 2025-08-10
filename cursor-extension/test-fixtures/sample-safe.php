<?php
// Sample safe PHP file for testing WordPress Semgrep Security

// Properly sanitized user input
$user_input = sanitize_text_field($_POST['user_input']);
echo esc_html($user_input);

// Proper SQL injection prevention
$user_id = intval($_GET['id']);
$query = $wpdb->prepare("SELECT * FROM users WHERE id = %d", $user_id);

// Proper nonce verification
if (isset($_POST['action']) && wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {
    process_form();
}

// Proper capability check
function admin_action() {
    if (current_user_can('manage_options')) {
        update_system_settings();
    } else {
        wp_die('Insufficient permissions');
    }
}

// Properly sanitized HTML output
$content = wp_kses_post($_GET['content']);
echo "<div>" . $content . "</div>";

// Proper file operation with checks
$file_path = sanitize_file_name($_GET['file']);
if (file_exists($file_path) && is_readable($file_path)) {
    include($file_path);
}

// Proper redirect validation
$redirect_url = esc_url_raw($_GET['redirect']);
if (wp_http_validate_url($redirect_url)) {
    wp_redirect($redirect_url);
    exit;
}

// Functions that should be tested
function process_form() {
    // Process form data with proper validation
    if (isset($_POST['data'])) {
        $data = sanitize_text_field($_POST['data']);
        // Process the sanitized data
        echo "Form processed safely";
    }
}

function update_system_settings() {
    // Update settings with proper capability check
    if (current_user_can('manage_options')) {
        // Update settings safely
        echo "Settings updated safely";
    }
}
?>
