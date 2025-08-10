<?php
// XSS Taint Analysis Rules - Vulnerable Examples
// This file contains examples that SHOULD trigger XSS taint analysis rules

// =============================================================================
// XSS TAINT SOURCES - User input that can contain malicious scripts
// =============================================================================

// Direct user input sources
$user_input_get = $_GET['input'];
$user_input_post = $_POST['data'];
$user_input_request = $_REQUEST['content'];
$user_input_cookie = $_COOKIE['user_data'];

// Database sources that may contain user input
global $wpdb;
$db_content = $wpdb->get_var("SELECT content FROM posts WHERE id = 1");
$db_row = $wpdb->get_row("SELECT * FROM posts WHERE id = 1");
$db_results = $wpdb->get_results("SELECT * FROM posts");

// WordPress function sources
$post_meta = get_post_meta($post_id, 'custom_field', true);
$option_value = get_option('site_description');
$user_meta = get_user_meta($user_id, 'bio', true);

// File content sources
$file_content = file_get_contents('user_uploaded_file.txt');
$remote_content = wp_remote_get('https://external-site.com/user-data');

// =============================================================================
// XSS TAINT SINKS - Output functions where XSS can occur
// =============================================================================

// Direct output sinks - VULNERABLE
echo $user_input_get;                    // Should trigger xss-taint-sink-echo
print $user_input_post;                  // Should trigger xss-taint-sink-print
printf($user_input_request);             // Should trigger xss-taint-sink-printf

// HTML output sinks - VULNERABLE
echo "<div>" . $user_input_get . "</div>";                    // Should trigger xss-taint-sink-html-content
echo "<input value='" . $user_input_post . "'>";              // Should trigger xss-taint-sink-html-attribute
echo "<span class='" . $user_input_request . "'>Text</span>"; // Should trigger xss-taint-sink-html-attribute

// JavaScript output sinks - VULNERABLE
echo "<script>var data = '" . $user_input_get . "';</script>";           // Should trigger xss-taint-sink-javascript-variable
echo "<script>alert('" . $user_input_post . "');</script>";              // Should trigger xss-taint-sink-javascript-string
echo "<script>document.title = '" . $user_input_request . "';</script>"; // Should trigger xss-taint-sink-javascript-string

// CSS output sinks - VULNERABLE
echo "<div style='color: " . $user_input_get . "'>Content</div>";        // Should trigger xss-taint-sink-css-style
echo "<span style='background: " . $user_input_post . "'>Text</span>";   // Should trigger xss-taint-sink-css-style

// WordPress output sinks - VULNERABLE
echo wp_json_encode($user_input_get);    // Should trigger xss-taint-sink-wp-json-encode
wp_send_json_success($user_input_post);  // Should trigger xss-taint-sink-wp-send-json

// =============================================================================
// XSS TAINT FLOW DETECTION - Complete vulnerability flows
// =============================================================================

// Direct user input to output flows - VULNERABLE
$user_input = $_GET['input'];
// ... some processing ...
echo $user_input;                        // Should trigger xss-taint-flow-user-to-echo

$user_input = $_POST['data'];
// ... some processing ...
echo "<div>" . $user_input . "</div>";   // Should trigger xss-taint-flow-user-to-html

$user_input = $_REQUEST['content'];
// ... some processing ...
echo "<input value='" . $user_input . "'>"; // Should trigger xss-taint-flow-user-to-attribute

$user_input = $_GET['data'];
// ... some processing ...
echo "<script>var data = '" . $user_input . "';</script>"; // Should trigger xss-taint-flow-user-to-javascript

// Database content to output flows - VULNERABLE
$result = $wpdb->get_row("SELECT * FROM posts WHERE id = 1");
// ... some processing ...
echo $result->content;                   // Should trigger xss-taint-flow-database-to-output

$meta_value = get_post_meta($post_id, 'custom_field', true);
// ... some processing ...
echo $meta_value;                        // Should trigger xss-taint-flow-post-meta-to-output

$option_value = get_option('site_description');
// ... some processing ...
echo $option_value;                      // Should trigger xss-taint-flow-option-to-output

// AJAX response flows - VULNERABLE
$user_input = $_POST['ajax_data'];
// ... some processing ...
wp_send_json_success($user_input);       // Should trigger xss-taint-flow-user-to-ajax

// REST API response flows - VULNERABLE
$user_input = $_POST['rest_data'];
// ... some processing ...
return new WP_REST_Response(['data' => $user_input], 200); // Should trigger xss-taint-flow-user-to-rest

// =============================================================================
// COMPLEX VULNERABILITY PATTERNS
// =============================================================================

// Nested variable assignment - VULNERABLE
$temp_var = $_GET['input'];
$processed_var = $temp_var;
echo $processed_var;                     // Should trigger taint flow detection

// Array access - VULNERABLE
$user_data = $_POST['user_data'];
$name = $user_data['name'];
echo "<h1>" . $name . "</h1>";           // Should trigger taint flow detection

// Object property access - VULNERABLE
$user_input = $_REQUEST['input'];
$user_obj = new stdClass();
$user_obj->data = $user_input;
echo $user_obj->data;                    // Should trigger taint flow detection

// Function parameter passing - VULNERABLE
function display_content($content) {
    echo $content;                       // Should trigger taint flow detection
}

$user_input = $_GET['content'];
display_content($user_input);            // Should trigger taint flow detection

// Class method - VULNERABLE
class ContentDisplay {
    public function display($content) {
        echo "<div>" . $content . "</div>"; // Should trigger taint flow detection
    }
}

$display = new ContentDisplay();
$user_input = $_POST['content'];
$display->display($user_input);          // Should trigger taint flow detection

// =============================================================================
// WORDPRESS-SPECIFIC VULNERABILITY PATTERNS
// =============================================================================

// Widget output - VULNERABLE
function vulnerable_widget_output($instance) {
    $title = $instance['title'];
    echo "<h2>" . $title . "</h2>";      // Should trigger taint flow detection
}

// Shortcode output - VULNERABLE
function vulnerable_shortcode($atts) {
    $content = $atts['content'];
    echo "<div>" . $content . "</div>";  // Should trigger taint flow detection
}

// AJAX handler - VULNERABLE
add_action('wp_ajax_vulnerable_handler', 'vulnerable_ajax_handler');
function vulnerable_ajax_handler() {
    $user_input = $_POST['data'];
    wp_send_json_success($user_input);   // Should trigger taint flow detection
}

// REST API endpoint - VULNERABLE
add_action('rest_api_init', function () {
    register_rest_route('vulnerable/v1', '/data', array(
        'methods' => 'POST',
        'callback' => 'vulnerable_rest_callback'
    ));
});

function vulnerable_rest_callback($request) {
    $user_input = $request->get_param('data');
    return new WP_REST_Response(['result' => $user_input], 200); // Should trigger taint flow detection
}

// Template tag - VULNERABLE
function vulnerable_template_tag($post_id) {
    $custom_field = get_post_meta($post_id, 'custom_content', true);
    echo $custom_field;                  // Should trigger taint flow detection
}

// =============================================================================
// ADVANCED VULNERABILITY PATTERNS
// =============================================================================

// Conditional output - VULNERABLE
$user_input = $_GET['input'];
if (!empty($user_input)) {
    echo $user_input;                    // Should trigger taint flow detection
}

// Loop output - VULNERABLE
$user_inputs = $_POST['inputs'];
foreach ($user_inputs as $input) {
    echo "<li>" . $input . "</li>";      // Should trigger taint flow detection
}

// Ternary operator - VULNERABLE
$user_input = $_REQUEST['input'];
echo $user_input ? $user_input : 'default'; // Should trigger taint flow detection

// String concatenation - VULNERABLE
$user_input = $_GET['input'];
$output = "User said: " . $user_input;
echo $output;                            // Should trigger taint flow detection

// Heredoc syntax - VULNERABLE
$user_input = $_POST['input'];
echo <<<HTML
<div class="content">
    $user_input
</div>
HTML;                                    // Should trigger taint flow detection

// =============================================================================
// MIXED CONTENT PATTERNS (PARTIALLY VULNERABLE)
// =============================================================================

// Mixed safe and unsafe content - VULNERABLE
$user_input = $_GET['input'];
$safe_content = "This is safe content";
echo "<div>" . $safe_content . " " . $user_input . "</div>"; // Should trigger taint flow detection

// Multiple user inputs - VULNERABLE
$input1 = $_POST['input1'];
$input2 = $_POST['input2'];
echo "<form><input value='" . $input1 . "'><input value='" . $input2 . "'></form>"; // Should trigger multiple taint flow detections

// =============================================================================
// CONFIGURATION COMMENTS FOR TESTING
// =============================================================================

// XSS_TAINT_SOURCES: $_GET, $_POST, $_REQUEST, $_COOKIE, $wpdb->get_*, get_post_meta, get_option, file_get_contents
// XSS_TAINT_SINKS: echo, print, printf, HTML output, JavaScript output, wp_send_json, wp_json_encode
// XSS_TAINT_SANITIZERS: esc_html, esc_attr, esc_js, esc_url, sanitize_text_field, wp_kses_post, wp_json_encode
