<?php
/**
 * WordPress Sanitization Safe Examples - Test Cases
 * These examples demonstrate proper sanitization practices in WordPress
 * that should NOT trigger the sanitization rules.
 */

// Proper input sanitization
function safe_input_sanitization() {
    $data = sanitize_text_field($_POST['user_input']);
    echo esc_html($data); // SAFE: Properly sanitized and escaped
}

// Proper GET parameter sanitization
function safe_get_sanitization() {
    $param = sanitize_text_field($_GET['parameter']);
    global $wpdb;
    $wpdb->prepare("SELECT * FROM table WHERE id = %s", $param); // SAFE: Prepared statement
}

// Proper REQUEST data sanitization
function safe_request_sanitization() {
    $data = sanitize_text_field($_REQUEST['input']);
    update_option('setting', $data); // SAFE: Sanitized before saving
}

// Safe database query with prepared statement
function safe_db_query() {
    $user_input = sanitize_text_field($_POST['search']);
    global $wpdb;
    $wpdb->prepare("SELECT * FROM posts WHERE title LIKE %s", '%' . $wpdb->esc_like($user_input) . '%'); // SAFE: Prepared with esc_like
}

// Safe database insert
function safe_db_insert() {
    $title = sanitize_text_field($_POST['title']);
    global $wpdb;
    $wpdb->insert('posts', array('title' => $title)); // SAFE: Sanitized before insert
}

// Safe output with proper escaping
function safe_output() {
    $user_data = wp_kses_post($_POST['content']);
    echo $user_data; // SAFE: HTML content properly sanitized
}

// Safe attribute output
function safe_attribute_output() {
    $value = esc_attr($_GET['param']);
    echo "<input value='$value'>"; // SAFE: Attribute properly escaped
}

// Safe file path usage
function safe_file_path() {
    $filename = sanitize_file_name($_POST['filename']);
    $file = fopen($filename, 'r'); // SAFE: Filename sanitized
}

// Safe include with validation
function safe_include() {
    $page = sanitize_text_field($_GET['page']);
    $allowed_pages = array('allowed1', 'allowed2', 'allowed3');
    if (in_array($page, $allowed_pages)) {
        include($page . '.php'); // SAFE: Validated against whitelist
    }
}

// Safe URL usage
function safe_url_usage() {
    $url = esc_url_raw($_POST['redirect_url']);
    if (wp_http_validate_url($url)) {
        wp_redirect($url); // SAFE: URL validated and escaped
    }
}

// Safe link output
function safe_link_output() {
    $link = esc_url($_GET['link']);
    echo "<a href='$link'>Click here</a>"; // SAFE: URL properly escaped
}

// Safe email usage
function safe_email_usage() {
    $email = sanitize_email($_POST['email']);
    if (is_email($email)) {
        wp_mail($email, 'Subject', 'Message'); // SAFE: Email validated and sanitized
    }
}

// Safe JSON output
function safe_json_output() {
    $data = sanitize_text_field($_POST['json_data']);
    echo wp_json_encode($data); // SAFE: Data sanitized before JSON encoding
}

// Proper sanitization function for context
function safe_context_appropriate_sanitization() {
    $html_content = $_POST['content'];
    $sanitized = wp_kses_post($html_content); // SAFE: HTML content preserved with kses
    echo $sanitized;
}

// Single sanitization (no double sanitization)
function safe_single_sanitization() {
    $data = sanitize_text_field($_POST['input']); // SAFE: Single sanitization
    return $data;
}

// Sanitization with validation
function safe_sanitization_with_validation() {
    $email = sanitize_email($_POST['email']);
    if (is_email($email)) {
        wp_mail($email, 'Subject', 'Message'); // SAFE: Email validated after sanitization
    }
}

// Safe AJAX handler
add_action('wp_ajax_my_action', 'safe_ajax_handler');
function safe_ajax_handler() {
    $data = sanitize_text_field($_POST['data']);
    echo esc_html($data); // SAFE: AJAX data properly sanitized and escaped
}

// Safe REST API endpoint
register_rest_route('myplugin/v1', '/data', array(
    'callback' => 'safe_rest_callback',
    'methods' => 'POST'
));
function safe_rest_callback($request) {
    $data = sanitize_text_field($request->get_param('data'));
    return $data; // SAFE: REST API data properly sanitized
}

// Safe options update
function safe_options_update() {
    if (isset($_POST['save_settings'])) {
        $setting_value = sanitize_text_field($_POST['setting_value']);
        update_option('my_setting', $setting_value); // SAFE: Options sanitized before saving
    }
}

// Safe user meta update
function safe_usermeta_update() {
    $user_id = get_current_user_id();
    $value = sanitize_text_field($_POST['value']);
    update_user_meta($user_id, 'custom_field', $value); // SAFE: User meta sanitized
}

// Safe post meta update
function safe_postmeta_update() {
    $post_id = intval($_POST['post_id']);
    $value = sanitize_text_field($_POST['value']);
    update_post_meta($post_id, 'custom_field', $value); // SAFE: Post meta sanitized
}

// Safe comment insertion
function safe_comment_insert() {
    $comment_data = array(
        'comment_content' => wp_filter_comment($_POST['comment']),
        'comment_author' => sanitize_text_field($_POST['author'])
    );
    wp_insert_comment($comment_data); // SAFE: Comment data properly sanitized
}

// Safe search query
function safe_search_query() {
    $search_term = sanitize_text_field($_GET['s']);
    $query = new WP_Query(array('s' => $search_term)); // SAFE: Search term sanitized
}

// Safe file upload
function safe_file_upload() {
    $uploaded_file = $_FILES['file'];
    $upload_overrides = array('test_form' => false);
    $moved_file = wp_handle_upload($uploaded_file, $upload_overrides); // SAFE: WordPress upload handler
}

// Safe cookie usage
function safe_cookie_usage() {
    $cookie_value = sanitize_text_field($_COOKIE['user_preference']);
    echo esc_html($cookie_value); // SAFE: Cookie data sanitized and escaped
}

// Safe session usage
function safe_session_usage() {
    session_start();
    $session_data = sanitize_text_field($_SESSION['user_data']);
    echo esc_html($session_data); // SAFE: Session data sanitized and escaped
}

// Safe form processing
function safe_form_processing() {
    if (isset($_POST['submit'])) {
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $message = wp_kses_post($_POST['message']);
        
        // SAFE: All form data properly sanitized
        global $wpdb;
        $wpdb->insert('contact_form', array(
            'name' => $name,
            'email' => $email,
            'message' => $message
        ));
        
        echo esc_html("Thank you, $name!"); // SAFE: Output properly escaped
    }
}

// Safe admin form
function safe_admin_form() {
    if (current_user_can('manage_options')) {
        if (isset($_POST['admin_action'])) {
            $setting = sanitize_text_field($_POST['admin_setting']);
            update_option('admin_option', $setting); // SAFE: Admin settings sanitized
        }
    }
}

// Safe plugin settings
function safe_plugin_settings() {
    if (isset($_POST['plugin_settings'])) {
        $plugin_option = sanitize_text_field($_POST['plugin_option']);
        update_option('my_plugin_setting', $plugin_option); // SAFE: Plugin settings sanitized
    }
}

// Safe custom post type
function safe_custom_post_type() {
    if (isset($_POST['create_post'])) {
        $post_title = sanitize_text_field($_POST['post_title']);
        $post_content = wp_kses_post($_POST['post_content']);
        
        wp_insert_post(array(
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_type' => 'custom_type'
        )); // SAFE: Post data properly sanitized
    }
}

// Safe widget data
function safe_widget_data() {
    if (isset($_POST['widget_data'])) {
        $widget_value = sanitize_text_field($_POST['widget_value']);
        update_option('widget_setting', $widget_value); // SAFE: Widget data sanitized
    }
}

// Safe shortcode
function safe_shortcode($atts) {
    $custom_attr = sanitize_text_field($atts['custom']);
    echo esc_html($custom_attr); // SAFE: Shortcode attribute sanitized and escaped
}
add_shortcode('safe', 'safe_shortcode');

// Safe template tag
function safe_template_tag() {
    $custom_value = sanitize_text_field($_GET['custom']);
    echo esc_html($custom_value); // SAFE: Template output sanitized and escaped
}

// Safe API response
function safe_api_response() {
    $api_data = sanitize_text_field($_POST['api_data']);
    wp_send_json($api_data); // SAFE: API response sanitized
}

// Safe complex database query
function safe_complex_query() {
    $search = sanitize_text_field($_GET['search']);
    $category = sanitize_text_field($_GET['category']);
    $author = sanitize_text_field($_GET['author']);
    
    global $wpdb;
    $query = $wpdb->prepare(
        "SELECT * FROM posts WHERE title LIKE %s AND category = %s AND author = %s",
        '%' . $wpdb->esc_like($search) . '%',
        $category,
        $author
    );
    $wpdb->query($query); // SAFE: All parameters properly prepared
}

// Safe file operations
function safe_file_operations() {
    $filename = sanitize_file_name($_POST['filename']);
    $content = wp_kses_post($_POST['content']);
    
    $file = fopen($filename, 'w');
    fwrite($file, $content); // SAFE: Filename and content properly sanitized
    fclose($file);
}

// Safe HTML output
function safe_html_output() {
    $title = sanitize_text_field($_GET['title']);
    $content = wp_kses_post($_POST['content']);
    $link = esc_url($_GET['link']);
    
    echo '<h1>' . esc_html($title) . '</h1>'; // SAFE: Title escaped
    echo '<div>' . $content . '</div>'; // SAFE: Content properly sanitized
    echo '<a href="' . $link . '">Link</a>'; // SAFE: URL escaped
}

// Safe nonce verification with sanitization
function safe_nonce_with_sanitization() {
    if (isset($_POST['submit'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'my_action')) {
            $data = sanitize_text_field($_POST['data']);
            update_option('my_option', $data); // SAFE: Nonce verified and data sanitized
        }
    }
}

// Safe capability check with sanitization
function safe_capability_with_sanitization() {
    if (current_user_can('manage_options')) {
        if (isset($_POST['admin_action'])) {
            $setting = sanitize_text_field($_POST['setting']);
            update_option('admin_setting', $setting); // SAFE: Capability checked and data sanitized
        }
    }
}

// Safe AJAX with nonce and sanitization
add_action('wp_ajax_safe_action', 'safe_ajax_with_nonce');
function safe_ajax_with_nonce() {
    check_ajax_referer('my_nonce_action', 'nonce');
    $data = sanitize_text_field($_POST['data']);
    echo esc_html($data); // SAFE: Nonce verified, data sanitized and escaped
}

// Safe REST API with capability and sanitization
register_rest_route('myplugin/v1', '/secure', array(
    'callback' => 'safe_rest_with_capability',
    'methods' => 'POST',
    'permission_callback' => function() {
        return current_user_can('manage_options');
    }
));
function safe_rest_with_capability($request) {
    $data = sanitize_text_field($request->get_param('data'));
    return $data; // SAFE: Capability checked and data sanitized
}

// Safe form with all security measures
function safe_complete_form() {
    if (isset($_POST['submit'])) {
        // Verify nonce
        if (!wp_verify_nonce($_POST['_wpnonce'], 'form_action')) {
            wp_die('Security check failed');
        }
        
        // Check capability
        if (!current_user_can('edit_posts')) {
            wp_die('Insufficient permissions');
        }
        
        // Sanitize all inputs
        $title = sanitize_text_field($_POST['title']);
        $content = wp_kses_post($_POST['content']);
        $email = sanitize_email($_POST['email']);
        
        // Validate email
        if (!is_email($email)) {
            wp_die('Invalid email address');
        }
        
        // Safe database operation
        global $wpdb;
        $wpdb->insert('posts', array(
            'title' => $title,
            'content' => $content,
            'email' => $email
        ));
        
        // Safe output
        echo '<div class="success">Post created successfully!</div>'; // SAFE: Static content
    }
}

// Safe file upload with validation
function safe_file_upload_with_validation() {
    if (isset($_FILES['file'])) {
        $uploaded_file = $_FILES['file'];
        
        // Check file type
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $file_extension = strtolower(pathinfo($uploaded_file['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_types)) {
            $upload_overrides = array('test_form' => false);
            $moved_file = wp_handle_upload($uploaded_file, $upload_overrides); // SAFE: Validated and handled properly
        }
    }
}

// Safe search with prepared statement
function safe_search_with_prepared() {
    $search_term = sanitize_text_field($_GET['s']);
    
    global $wpdb;
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM posts WHERE title LIKE %s OR content LIKE %s",
        '%' . $wpdb->esc_like($search_term) . '%',
        '%' . $wpdb->esc_like($search_term) . '%'
    )); // SAFE: Search term properly escaped and prepared
    
    foreach ($results as $result) {
        echo '<h2>' . esc_html($result->title) . '</h2>'; // SAFE: Database output escaped
        echo '<div>' . wp_kses_post($result->content) . '</div>'; // SAFE: Content properly sanitized
    }
}
?>
