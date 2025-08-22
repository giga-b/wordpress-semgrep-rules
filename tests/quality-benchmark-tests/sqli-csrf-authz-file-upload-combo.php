<?php
/**
 * Four security issues combined
 * Status: Vulnerable (sqli, csrf, authz, file_upload)
 * Expected Findings: 4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function sqli_csrf_authz_file_upload_combo_vulnerable_function() {
    global $wpdb;
    
    // VULNERABILITY: SQL Injection - Direct concatenation
    $query = "SELECT * FROM {$wpdb->prefix}users WHERE ID = " . $_GET['user_id']; // VULNERABLE
    $results = $wpdb->get_results($query);
    // VULNERABILITY: CSRF - No nonce verification
    if (isset($_POST['action'])) { wp_delete_post($_POST['post_id']); } // VULNERABLE
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

add_action('init', 'sqli_csrf_authz_file_upload_combo_vulnerable_function');
