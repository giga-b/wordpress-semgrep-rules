<?php
/**
 * REST endpoint without permission_callback
 * Status: Vulnerable (Authorization)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function authz_rest_permission_vulnerable_function() {
    // VULNERABILITY: Authorization - No capability check
    if (isset($_POST['admin_action'])) {
        // VULNERABLE: No capability check before admin action
        update_option('admin_setting', $_POST['admin_setting']);
    }
    
    // Safe operations for contrast
    if (isset($_POST['safe_admin_action']) && current_user_can('manage_options')) {
        $safe_setting = sanitize_text_field($_POST['safe_admin_setting']);
        update_option('safe_admin_setting', $safe_setting);
    }
    
    return true;
}

add_action('init', 'authz_rest_permission_vulnerable_function');
