<?php
/**
 * Test Case: XSS Prevention Detection
 * 
 * This file contains test cases for WordPress XSS prevention patterns.
 * It includes both vulnerable and secure examples for testing rule detection.
 */

// VULNERABLE: Unescaped output of user input
function vulnerable_output_function() {
    $user_input = $_GET['data'];
    echo $user_input; // VULNERABLE: Unescaped output
}

// VULNERABLE: Unescaped output in HTML
function vulnerable_html_output() {
    $user_input = $_POST['content'];
    echo "<div>" . $user_input . "</div>"; // VULNERABLE: Unescaped in HTML
}

// SECURE: Properly escaped output
function secure_output_function() {
    $user_input = $_GET['data'];
    echo esc_html($user_input); // SECURE: Escaped output
}

// SECURE: Properly escaped HTML output
function secure_html_output() {
    $user_input = $_POST['content'];
    echo "<div>" . esc_html($user_input) . "</div>"; // SECURE: Escaped in HTML
}

// VULNERABLE: Unescaped output in attribute
function vulnerable_attribute_output() {
    $user_input = $_GET['class'];
    echo "<div class='" . $user_input . "'>Content</div>"; // VULNERABLE: Unescaped in attribute
}

// SECURE: Properly escaped attribute output
function secure_attribute_output() {
    $user_input = $_GET['class'];
    echo "<div class='" . esc_attr($user_input) . "'>Content</div>"; // SECURE: Escaped attribute
}

// VULNERABLE: Unescaped output in JavaScript
function vulnerable_js_output() {
    $user_input = $_GET['value'];
    echo "<script>var data = '" . $user_input . "';</script>"; // VULNERABLE: Unescaped in JS
}

// SECURE: Properly escaped JavaScript output
function secure_js_output() {
    $user_input = $_GET['value'];
    echo "<script>var data = '" . esc_js($user_input) . "';</script>"; // SECURE: Escaped JS
}

// VULNERABLE: Unescaped output in URL
function vulnerable_url_output() {
    $user_input = $_GET['url'];
    echo "<a href='" . $user_input . "'>Link</a>"; // VULNERABLE: Unescaped URL
}

// SECURE: Properly escaped URL output
function secure_url_output() {
    $user_input = $_GET['url'];
    echo "<a href='" . esc_url($user_input) . "'>Link</a>"; // SECURE: Escaped URL
}
