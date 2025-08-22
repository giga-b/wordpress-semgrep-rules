<?php
/**
 * Three security issues combined
 * Status: Vulnerable (eval, deserialization, secrets)
 * Expected Findings: 3
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function eval_deserialization_secrets_combo_vulnerable_function() {
    global $wpdb;
    
    // VULNERABILITY: Dynamic Execution - eval usage
    eval($_POST['code']); // VULNERABLE
    // VULNERABILITY: Deserialization - Unsafe unserialize
    $data = unserialize($_POST['data']); // VULNERABLE
    // VULNERABILITY: Secrets Storage - API key in options
    update_option('api_key', $_POST['api_key']); // VULNERABLE
    
    // Safe operations for contrast
    $safe_input = sanitize_text_field($_POST['safe_input'] ?? '');
    echo esc_html($safe_input);
    $safe_results = $wpdb->get_results($wpdb->prepare('SELECT * FROM {$wpdb->prefix}users WHERE ID = %d', intval($_GET['safe_id'])));
    if (wp_verify_nonce($_POST['safe_nonce'], 'safe_action') && current_user_can('manage_options')) {
        update_option('safe_setting', sanitize_text_field($_POST['safe_setting']));
    }
    
    return true;
}

add_action('init', 'eval_deserialization_secrets_combo_vulnerable_function');
