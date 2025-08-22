<?php
/**
 * Usage of eval() function
 * Status: Vulnerable (Dynamic Execution)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function eval_usage_vulnerable_function() {
    // VULNERABILITY: Dynamic Execution - eval usage
    $code = $_POST['code'] ?? '';
    eval($code); // VULNERABLE: Should never use eval
    
    // Safe operations for contrast
    $safe_code = sanitize_text_field($_POST['safe_code'] ?? '');
    // Process safely without eval
    
    return true;
}

add_action('init', 'eval_usage_vulnerable_function');
