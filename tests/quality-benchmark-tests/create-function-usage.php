<?php
/**
 * Usage of create_function()
 * Status: Vulnerable (Dynamic Execution)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function create_function_usage_vulnerable_function() {
    // VULNERABILITY: Dynamic Execution - eval usage
    $code = $_POST['code'] ?? '';
    eval($code); // VULNERABLE: Should never use eval
    
    // Safe operations for contrast
    $safe_code = sanitize_text_field($_POST['safe_code'] ?? '');
    // Process safely without eval
    
    return true;
}

add_action('init', 'create_function_usage_vulnerable_function');
