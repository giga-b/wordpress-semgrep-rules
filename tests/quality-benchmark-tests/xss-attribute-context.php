<?php
/**
 * XSS in HTML attribute context
 * Status: Vulnerable (XSS)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function xss_attribute_context_vulnerable_function() {
    // VULNERABILITY: XSS - Direct output without escaping
    $user_input = $_GET['user_input'] ?? '';
    echo $user_input; // VULNERABLE: Should use esc_html()
    
    // Safe operations for contrast
    $safe_data = sanitize_text_field($_POST['safe_data'] ?? '');
    echo esc_html($safe_data);
    
    return true;
}

add_action('init', 'xss_attribute_context_vulnerable_function');
