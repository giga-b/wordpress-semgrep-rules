<?php
// Sample vulnerable PHP file for testing WordPress Semgrep Security

// XSS vulnerability - unsanitized user input
$user_input = $_POST['user_input'];
echo $user_input; // This should trigger a security warning

// SQL injection vulnerability
$user_id = $_GET['id'];
$query = "SELECT * FROM users WHERE id = " . $user_id;

// Missing nonce verification
if (isset($_POST['action'])) {
    // Process form without nonce check
    process_form();
}

// Missing capability check
function admin_action() {
    // Missing capability check
    update_system_settings();
}

// Unsanitized HTML output
$content = $_GET['content'];
echo "<div>" . $content . "</div>";

// File operation without proper checks
$file_path = $_GET['file'];
include($file_path);

// Unvalidated redirect
$redirect_url = $_GET['redirect'];
header("Location: " . $redirect_url);

// Functions that should be tested
function process_form() {
    // Process form data
    echo "Form processed";
}

function update_system_settings() {
    // Update settings
    echo "Settings updated";
}
?>
