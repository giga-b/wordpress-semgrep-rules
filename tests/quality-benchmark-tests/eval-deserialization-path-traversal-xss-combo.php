<?php
/**
 * Four security issues combined
 * Status: Vulnerable (eval, deserialization, path_traversal, xss)
 * Expected Findings: 4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function eval_deserialization_path_traversal_xss_combo_vulnerable_function() {
    global $wpdb;
    
    // VULNERABILITY: Dynamic Execution - eval usage
    eval($_POST['code']); // VULNERABLE
    // VULNERABILITY: Deserialization - Unsafe unserialize
    $data = unserialize($_POST['data']); // VULNERABLE
    // VULNERABILITY: Path Traversal - Direct file access
    $content = file_get_contents(ABSPATH . $_GET['file']); // VULNERABLE
    // VULNERABILITY: XSS - Direct output without escaping
    echo $_GET['user_input']; // VULNERABLE
    
    // Safe operations for contrast
    $safe_input = sanitize_text_field($_POST['safe_input'] ?? '');
    echo esc_html($safe_input);
    $safe_results = $wpdb->get_results($wpdb->prepare('SELECT * FROM {$wpdb->prefix}users WHERE ID = %d', intval($_GET['safe_id'])));
    if (wp_verify_nonce($_POST['safe_nonce'], 'safe_action') && current_user_can('manage_options')) {
        update_option('safe_setting', sanitize_text_field($_POST['safe_setting']));
    }
    
    return true;
}

add_action('init', 'eval_deserialization_path_traversal_xss_combo_vulnerable_function');
