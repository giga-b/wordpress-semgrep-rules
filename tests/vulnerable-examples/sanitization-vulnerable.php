<?php
/**
 * WordPress Sanitization Vulnerabilities - Test Cases
 * These examples demonstrate common sanitization vulnerabilities in WordPress
 * that should be detected by the sanitization rules.
 */

// Missing input sanitization
function vulnerable_missing_input() {
    $data = $_POST['user_input'];
    echo $data; // VULNERABLE: Direct output without sanitization
}

// Missing GET parameter sanitization
function vulnerable_missing_get() {
    $param = $_GET['parameter'];
    global $wpdb;
    $wpdb->query("SELECT * FROM table WHERE id = '$param'"); // VULNERABLE: SQL injection
}

// Missing REQUEST data sanitization
function vulnerable_missing_request() {
    $data = $_REQUEST['input'];
    update_option('setting', $data); // VULNERABLE: Unsanitized data saved to options
}

// Unsafe database query
function vulnerable_unsafe_db_query() {
    $user_input = $_POST['search'];
    global $wpdb;
    $wpdb->query("SELECT * FROM posts WHERE title LIKE '%$user_input%'"); // VULNERABLE: SQL injection
}

// Unsafe database insert
function vulnerable_unsafe_insert() {
    $title = $_POST['title'];
    global $wpdb;
    $wpdb->insert('posts', array('title' => $title)); // VULNERABLE: Unsanitized insert
}

// Unsafe output without escaping
function vulnerable_unsafe_output() {
    $user_data = $_POST['content'];
    echo $user_data; // VULNERABLE: XSS vulnerability
}

// Unsafe attribute output
function vulnerable_unsafe_attribute() {
    $value = $_GET['param'];
    echo "<input value='$value'>"; // VULNERABLE: XSS in attribute
}

// Unsafe file path usage
function vulnerable_unsafe_file_path() {
    $filename = $_POST['filename'];
    $file = fopen($filename, 'r'); // VULNERABLE: Path traversal
}

// Unsafe include with user input
function vulnerable_unsafe_include() {
    $page = $_GET['page'];
    include($page . '.php'); // VULNERABLE: Remote file inclusion
}

// Unsafe URL usage
function vulnerable_unsafe_url() {
    $url = $_POST['redirect_url'];
    wp_redirect($url); // VULNERABLE: Open redirect
}

// Unsafe link output
function vulnerable_unsafe_link() {
    $link = $_GET['link'];
    echo "<a href='$link'>Click here</a>"; // VULNERABLE: XSS in href
}

// Unsafe email usage
function vulnerable_unsafe_email() {
    $email = $_POST['email'];
    wp_mail($email, 'Subject', 'Message'); // VULNERABLE: Email injection
}

// Unsafe JSON output
function vulnerable_unsafe_json() {
    $data = $_POST['json_data'];
    echo json_encode($data); // VULNERABLE: JSON injection
}

// Wrong sanitization function
function vulnerable_wrong_function() {
    $html_content = $_POST['content'];
    $sanitized = sanitize_text_field($html_content); // VULNERABLE: Strips HTML when it should be preserved
    echo $sanitized;
}

// Double sanitization
function vulnerable_double_sanitization() {
    $data = sanitize_text_field($_POST['input']);
    $data = sanitize_text_field($data); // VULNERABLE: Unnecessary double sanitization
}

// Missing validation
function vulnerable_missing_validation() {
    $email = sanitize_email($_POST['email']);
    wp_mail($email, 'Subject', 'Message'); // VULNERABLE: No email validation
}

// AJAX handler missing sanitization
add_action('wp_ajax_my_action', 'vulnerable_ajax_handler');
function vulnerable_ajax_handler() {
    $data = $_POST['data'];
    echo $data; // VULNERABLE: AJAX without sanitization
}

// REST API missing sanitization
register_rest_route('myplugin/v1', '/data', array(
    'callback' => 'vulnerable_rest_callback',
    'methods' => 'POST'
));
function vulnerable_rest_callback($request) {
    $data = $request->get_param('data');
    return $data; // VULNERABLE: REST API without sanitization
}

// Options update without sanitization
function vulnerable_options_update() {
    if (isset($_POST['save_settings'])) {
        update_option('my_setting', $_POST['setting_value']); // VULNERABLE: Unsanitized options
    }
}

// User meta update without sanitization
function vulnerable_usermeta_update() {
    $user_id = get_current_user_id();
    update_user_meta($user_id, 'custom_field', $_POST['value']); // VULNERABLE: Unsanitized user meta
}

// Post meta update without sanitization
function vulnerable_postmeta_update() {
    $post_id = $_POST['post_id'];
    update_post_meta($post_id, 'custom_field', $_POST['value']); // VULNERABLE: Unsanitized post meta
}

// Comment data without sanitization
function vulnerable_comment_insert() {
    $comment_data = array(
        'comment_content' => $_POST['comment'],
        'comment_author' => $_POST['author']
    );
    wp_insert_comment($comment_data); // VULNERABLE: Unsanitized comment data
}

// Search query without sanitization
function vulnerable_search_query() {
    $search_term = $_GET['s'];
    $query = new WP_Query(array('s' => $search_term)); // VULNERABLE: Unsanitized search
}

// File upload without proper validation
function vulnerable_file_upload() {
    $uploaded_file = $_FILES['file'];
    move_uploaded_file($uploaded_file['tmp_name'], $uploaded_file['name']); // VULNERABLE: Unsafe upload
}

// Cookie data without sanitization
function vulnerable_cookie_usage() {
    $cookie_value = $_COOKIE['user_preference'];
    echo $cookie_value; // VULNERABLE: Unsanitized cookie data
}

// Session data without sanitization
function vulnerable_session_usage() {
    session_start();
    $session_data = $_SESSION['user_data'];
    echo $session_data; // VULNERABLE: Unsanitized session data
}

// Form processing without sanitization
function vulnerable_form_processing() {
    if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $message = $_POST['message'];
        
        // VULNERABLE: All form data used without sanitization
        $wpdb->insert('contact_form', array(
            'name' => $name,
            'email' => $email,
            'message' => $message
        ));
        
        echo "Thank you, $name!"; // VULNERABLE: XSS in output
    }
}

// Admin form without sanitization
function vulnerable_admin_form() {
    if (current_user_can('manage_options')) {
        if (isset($_POST['admin_action'])) {
            $setting = $_POST['admin_setting'];
            update_option('admin_option', $setting); // VULNERABLE: Admin settings without sanitization
        }
    }
}

// Plugin settings without sanitization
function vulnerable_plugin_settings() {
    if (isset($_POST['plugin_settings'])) {
        $plugin_option = $_POST['plugin_option'];
        update_option('my_plugin_setting', $plugin_option); // VULNERABLE: Plugin settings without sanitization
    }
}

// Custom post type without sanitization
function vulnerable_custom_post_type() {
    if (isset($_POST['create_post'])) {
        $post_title = $_POST['post_title'];
        $post_content = $_POST['post_content'];
        
        wp_insert_post(array(
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_type' => 'custom_type'
        )); // VULNERABLE: Post data without sanitization
    }
}

// Widget data without sanitization
function vulnerable_widget_data() {
    if (isset($_POST['widget_data'])) {
        $widget_value = $_POST['widget_value'];
        update_option('widget_setting', $widget_value); // VULNERABLE: Widget data without sanitization
    }
}

// Shortcode without sanitization
function vulnerable_shortcode($atts) {
    $custom_attr = $atts['custom'];
    echo $custom_attr; // VULNERABLE: Shortcode attribute without sanitization
}
add_shortcode('vulnerable', 'vulnerable_shortcode');

// Template tag without sanitization
function vulnerable_template_tag() {
    $custom_value = $_GET['custom'];
    echo $custom_value; // VULNERABLE: Template output without sanitization
}

// API response without sanitization
function vulnerable_api_response() {
    $api_data = $_POST['api_data'];
    wp_send_json($api_data); // VULNERABLE: API response without sanitization
}

// Database query with multiple vulnerabilities
function vulnerable_complex_query() {
    $search = $_GET['search'];
    $category = $_GET['category'];
    $author = $_GET['author'];
    
    global $wpdb;
    $query = "SELECT * FROM posts WHERE title LIKE '%$search%' AND category = '$category' AND author = '$author'";
    $wpdb->query($query); // VULNERABLE: Multiple SQL injection points
}

// File operations with multiple vulnerabilities
function vulnerable_file_operations() {
    $filename = $_POST['filename'];
    $content = $_POST['content'];
    
    $file = fopen($filename, 'w');
    fwrite($file, $content); // VULNERABLE: Path traversal and content injection
    fclose($file);
}

// HTML output with multiple vulnerabilities
function vulnerable_html_output() {
    $title = $_GET['title'];
    $content = $_POST['content'];
    $link = $_GET['link'];
    
    echo "<h1>$title</h1>"; // VULNERABLE: XSS in title
    echo "<div>$content</div>"; // VULNERABLE: XSS in content
    echo "<a href='$link'>Link</a>"; // VULNERABLE: XSS in href
}
?>
