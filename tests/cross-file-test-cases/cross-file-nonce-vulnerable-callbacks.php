<?php
/**
 * Cross-File Nonce Vulnerability Test Case - Callback Functions
 * This file contains the callback functions that lack proper nonce verification
 */

// Vulnerable AJAX handler without nonce verification
function vulnerable_ajax_handler() {
    $data = $_POST['data'];
    echo $data; // XSS vulnerability - no nonce verification
}

// Vulnerable class with vulnerable method
class VulnerableClass {
    public function vulnerable_method() {
        $data = $_POST['data'];
        echo $data; // XSS vulnerability - no nonce verification
    }
}

// Vulnerable priority handler without nonce verification
function vulnerable_priority_handler() {
    $data = $_POST['data'];
    echo $data; // XSS vulnerability - no nonce verification
}

// Vulnerable both handler without nonce verification
function vulnerable_both_handler() {
    $data = $_POST['data'];
    echo $data; // XSS vulnerability - no nonce verification
}

// Nonce verification with wrong action name (should fail)
function vulnerable_nonce_mismatch_handler() {
    check_ajax_referer('wrong_action_name', 'nonce'); // Wrong action name
    $data = $_POST['data'];
    echo $data;
}

// Nonce verification with wp_verify_nonce but wrong action
function vulnerable_wp_verify_mismatch_handler() {
    if (wp_verify_nonce($_POST['nonce'], 'wrong_action_name')) { // Wrong action name
        $data = $_POST['data'];
        echo $data;
    }
}

// Weak nonce verification (generic action name)
function vulnerable_weak_nonce_handler() {
    check_ajax_referer('nonce', 'security'); // Generic action name
    $data = $_POST['data'];
    echo $data;
}

// Missing nonce verification entirely
function vulnerable_no_nonce_handler() {
    $data = $_POST['data'];
    echo $data; // No nonce verification at all
}
