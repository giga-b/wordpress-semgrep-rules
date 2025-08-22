<?php
/**
 * Three security issues combined
 * Status: Vulnerable (path_traversal, xss, authz)
 * Expected Findings: 3
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function path_traversal_xss_authz_combo_vulnerable_function() {
    global $wpdb;
    
    // VULNERABILITY: Path Traversal - Direct file access
    $content = file_get_contents(ABSPATH . $_GET['file']); // VULNERABLE
    // VULNERABILITY: XSS - Direct output without escaping
    echo $_GET['user_input']; // VULNERABLE
    // VULNERABILITY: Authorization - No capability check
    update_option('admin_setting', $_POST['setting']); // VULNERABLE
    
    // Safe operations for contrast
    $safe_input = sanitize_text_field($_POST['safe_input'] ?? '');
    echo esc_html($safe_input);
    $safe_results = $wpdb->get_results($wpdb->prepare('SELECT * FROM {$wpdb->prefix}users WHERE ID = %d', intval($_GET['safe_id'])));
    if (wp_verify_nonce($_POST['safe_nonce'], 'safe_action') && current_user_can('manage_options')) {
        update_option('safe_setting', sanitize_text_field($_POST['safe_setting']));
    }
    
    return true;
}

add_action('init', 'path_traversal_xss_authz_combo_vulnerable_function');
