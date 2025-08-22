<?php
/**
 * File upload bypass with advanced techniques
 * Status: Vulnerable (Complex vulnerabilities)
 * Expected Findings: 4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function advanced_obfuscation_file_upload_complex_vulnerable_function() {
    global $wpdb;
    
    // VULNERABILITY 1: XSS variant 1
    echo $_GET['input_0']; // VULNERABLE
    echo '<div>' . $_POST['data_0'] . '</div>'; // VULNERABLE
    echo "<script>var data = '" . $_REQUEST['var_0'] . "';</script>"; // VULNERABLE
    // VULNERABILITY 2: SQL Injection variant 2
    $query_1 = "SELECT * FROM users WHERE id = " . $_GET['id_1']; // VULNERABLE
    $wpdb->query($query_1); // VULNERABLE
    $results_1 = $wpdb->get_results("SELECT * FROM posts WHERE title LIKE '%" . $_POST['search_1'] . "%'"); // VULNERABLE
    // VULNERABILITY 3: CSRF variant 3
    if (isset($_POST['action_2'])) { wp_delete_post($_POST['post_2']); } // VULNERABLE
    if ($_POST['delete_2']) { wp_delete_user($_POST['user_2']); } // VULNERABLE
    // VULNERABILITY 4: Authorization variant 4
    update_option('setting_3', $_POST['value_3']); // VULNERABLE
    wp_delete_post($_GET['post_3']); // VULNERABLE
    
    // Safe operations for contrast
    $safe_input = sanitize_text_field($_POST['safe_input'] ?? '');
    echo esc_html($safe_input);
    
    return true;
}

add_action('init', 'advanced_obfuscation_file_upload_complex_vulnerable_function');
