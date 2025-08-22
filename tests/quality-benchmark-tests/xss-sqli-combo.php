<?php
/**
 * Combined XSS and SQL injection
 * Status: Vulnerable (xss, sqli)
 * Expected Findings: 2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function xss_sqli_combo_vulnerable_function() {
    global $wpdb;
    
    // VULNERABILITY: XSS - Direct output without escaping
    echo $_GET['user_input']; // VULNERABLE
    // VULNERABILITY: SQL Injection - Direct concatenation
    $query = "SELECT * FROM {$wpdb->prefix}users WHERE ID = " . $_GET['user_id']; // VULNERABLE
    $results = $wpdb->get_results($query);
    
    // Safe operations for contrast
    $safe_input = sanitize_text_field($_POST['safe_input'] ?? '');
    echo esc_html($safe_input);
    $safe_results = $wpdb->get_results($wpdb->prepare('SELECT * FROM {$wpdb->prefix}users WHERE ID = %d', intval($_GET['safe_id'])));
    if (wp_verify_nonce($_POST['safe_nonce'], 'safe_action') && current_user_can('manage_options')) {
        update_option('safe_setting', sanitize_text_field($_POST['safe_setting']));
    }
    
    return true;
}

add_action('init', 'xss_sqli_combo_vulnerable_function');
