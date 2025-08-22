<?php
/**
 * Secrets stored in post meta
 * Status: Vulnerable (Secrets Storage)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function secrets_in_meta_vulnerable_function() {
    // VULNERABILITY: Secrets Storage - API key in options
    $api_key = $_POST['api_key'] ?? '';
    update_option('my_plugin_api_key', $api_key); // VULNERABLE: Storing sensitive data in options
    
    // Safe operations for contrast
    $safe_setting = sanitize_text_field($_POST['safe_setting'] ?? '');
    update_option('my_plugin_safe_setting', $safe_setting);
    
    return true;
}

add_action('init', 'secrets_in_meta_vulnerable_function');
