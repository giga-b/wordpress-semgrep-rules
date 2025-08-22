<?php
/**
 * Three security issues combined
 * Status: Vulnerable (xss, authz, file_upload)
 * Expected Findings: 3
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function xss_authz_file_combo_vulnerable_function() {
    global $wpdb;
    
    // VULNERABILITY: XSS - Direct output without escaping
    echo $_GET['user_input']; // VULNERABLE
    // VULNERABILITY: Authorization - No capability check
    update_option('admin_setting', $_POST['setting']); // VULNERABLE
    // VULNERABILITY: File Upload - No validation
    move_uploaded_file($_FILES['file']['tmp_name'], ABSPATH . $_FILES['file']['name']); // VULNERABLE
    
    // Safe operations for contrast
    $safe_input = sanitize_text_field($_POST['safe_input'] ?? '');
    echo esc_html($safe_input);
    $safe_results = $wpdb->get_results($wpdb->prepare('SELECT * FROM {$wpdb->prefix}users WHERE ID = %d', intval($_GET['safe_id'])));
    if (wp_verify_nonce($_POST['safe_nonce'], 'safe_action') && current_user_can('manage_options')) {
        update_option('safe_setting', sanitize_text_field($_POST['safe_setting']));
    }
    
    return true;
}

add_action('init', 'xss_authz_file_combo_vulnerable_function');
