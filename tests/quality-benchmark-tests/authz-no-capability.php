<?php
/**
 * Admin function without capability check
 * Status: Vulnerable (Authorization)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function authz_no_capability_vulnerable_function() {
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

add_action('init', 'authz_no_capability_vulnerable_function');
