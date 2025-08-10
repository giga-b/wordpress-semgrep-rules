<?php
// XSS Taint Analysis Rules - Safe Examples
// This file contains examples that should NOT trigger XSS taint analysis rules

// =============================================================================
// XSS TAINT SOURCES - User input that can contain malicious scripts
// =============================================================================

// Direct user input sources (these are sources, not vulnerabilities)
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
// XSS TAINT SINKS - Output functions where XSS can occur (BUT PROPERLY SANITIZED)
// =============================================================================

// Direct output sinks - SAFE with sanitization
echo esc_html($user_input_get);                    // SAFE - properly sanitized
print esc_html($user_input_post);                  // SAFE - properly sanitized
printf(esc_html($user_input_request));             // SAFE - properly sanitized

// HTML output sinks - SAFE with sanitization
echo "<div>" . esc_html($user_input_get) . "</div>";                    // SAFE - properly sanitized
echo "<input value='" . esc_attr($user_input_post) . "'>";              // SAFE - properly sanitized
echo "<span class='" . esc_attr($user_input_request) . "'>Text</span>"; // SAFE - properly sanitized

// JavaScript output sinks - SAFE with sanitization
echo "<script>var data = '" . esc_js($user_input_get) . "';</script>";           // SAFE - properly sanitized
echo "<script>alert(" . wp_json_encode($user_input_post) . ");</script>";       // SAFE - properly sanitized
echo "<script>document.title = '" . esc_js($user_input_request) . "';</script>"; // SAFE - properly sanitized

// CSS output sinks - SAFE with sanitization
echo "<div style='color: " . esc_attr($user_input_get) . "'>Content</div>";        // SAFE - properly sanitized
echo "<span style='background: " . esc_attr($user_input_post) . "'>Text</span>";   // SAFE - properly sanitized

// WordPress output sinks - SAFE with sanitization
echo wp_json_encode(esc_html($user_input_get));    // SAFE - properly sanitized
wp_send_json_success(esc_html($user_input_post));  // SAFE - properly sanitized

// =============================================================================
// XSS TAINT FLOW DETECTION - Complete safe flows
// =============================================================================

// Direct user input to output flows - SAFE with sanitization
$user_input = $_GET['input'];
// ... some processing ...
echo esc_html($user_input);                        // SAFE - properly sanitized

$user_input = $_POST['data'];
// ... some processing ...
echo "<div>" . esc_html($user_input) . "</div>";   // SAFE - properly sanitized

$user_input = $_REQUEST['content'];
// ... some processing ...
echo "<input value='" . esc_attr($user_input) . "'>"; // SAFE - properly sanitized

$user_input = $_GET['data'];
// ... some processing ...
echo "<script>var data = '" . esc_js($user_input) . "';</script>"; // SAFE - properly sanitized

// Database content to output flows - SAFE with sanitization
$result = $wpdb->get_row("SELECT * FROM posts WHERE id = 1");
// ... some processing ...
echo esc_html($result->content);                   // SAFE - properly sanitized

$meta_value = get_post_meta($post_id, 'custom_field', true);
// ... some processing ...
echo esc_html($meta_value);                        // SAFE - properly sanitized

$option_value = get_option('site_description');
// ... some processing ...
echo esc_html($option_value);                      // SAFE - properly sanitized

// AJAX response flows - SAFE with sanitization
$user_input = $_POST['ajax_data'];
// ... some processing ...
wp_send_json_success(esc_html($user_input));       // SAFE - properly sanitized

// REST API response flows - SAFE with sanitization
$user_input = $_POST['rest_data'];
// ... some processing ...
return new WP_REST_Response(['data' => esc_html($user_input)], 200); // SAFE - properly sanitized

// =============================================================================
// COMPLEX SAFE PATTERNS
// =============================================================================

// Nested variable assignment - SAFE with sanitization
$temp_var = $_GET['input'];
$processed_var = esc_html($temp_var);
echo $processed_var;                     // SAFE - properly sanitized

// Array access - SAFE with sanitization
$user_data = $_POST['user_data'];
$name = esc_html($user_data['name']);
echo "<h1>" . $name . "</h1>";           // SAFE - properly sanitized

// Object property access - SAFE with sanitization
$user_input = $_REQUEST['input'];
$user_obj = new stdClass();
$user_obj->data = esc_html($user_input);
echo $user_obj->data;                    // SAFE - properly sanitized

// Function parameter passing - SAFE with sanitization
function display_content($content) {
    echo esc_html($content);             // SAFE - properly sanitized
}

$user_input = $_GET['content'];
display_content($user_input);            // SAFE - properly sanitized

// Class method - SAFE with sanitization
class ContentDisplay {
    public function display($content) {
        echo "<div>" . esc_html($content) . "</div>"; // SAFE - properly sanitized
    }
}

$display = new ContentDisplay();
$user_input = $_POST['content'];
$display->display($user_input);          // SAFE - properly sanitized

// =============================================================================
// WORDPRESS-SPECIFIC SAFE PATTERNS
// =============================================================================

// Widget output - SAFE with sanitization
function safe_widget_output($instance) {
    $title = esc_html($instance['title']);
    echo "<h2>" . $title . "</h2>";      // SAFE - properly sanitized
}

// Shortcode output - SAFE with sanitization
function safe_shortcode($atts) {
    $content = wp_kses_post($atts['content']);
    echo "<div>" . $content . "</div>";  // SAFE - properly sanitized
}

// AJAX handler - SAFE with sanitization
add_action('wp_ajax_safe_handler', 'safe_ajax_handler');
function safe_ajax_handler() {
    $user_input = sanitize_text_field($_POST['data']);
    wp_send_json_success($user_input);   // SAFE - properly sanitized
}

// REST API endpoint - SAFE with sanitization
add_action('rest_api_init', function () {
    register_rest_route('safe/v1', '/data', array(
        'methods' => 'POST',
        'callback' => 'safe_rest_callback'
    ));
});

function safe_rest_callback($request) {
    $user_input = sanitize_text_field($request->get_param('data'));
    return new WP_REST_Response(['result' => $user_input], 200); // SAFE - properly sanitized
}

// Template tag - SAFE with sanitization
function safe_template_tag($post_id) {
    $custom_field = esc_html(get_post_meta($post_id, 'custom_content', true));
    echo $custom_field;                  // SAFE - properly sanitized
}

// =============================================================================
// ADVANCED SAFE PATTERNS
// =============================================================================

// Conditional output - SAFE with sanitization
$user_input = $_GET['input'];
if (!empty($user_input)) {
    echo esc_html($user_input);          // SAFE - properly sanitized
}

// Loop output - SAFE with sanitization
$user_inputs = $_POST['inputs'];
foreach ($user_inputs as $input) {
    echo "<li>" . esc_html($input) . "</li>";      // SAFE - properly sanitized
}

// Ternary operator - SAFE with sanitization
$user_input = $_REQUEST['input'];
echo $user_input ? esc_html($user_input) : 'default'; // SAFE - properly sanitized

// String concatenation - SAFE with sanitization
$user_input = $_GET['input'];
$output = "User said: " . esc_html($user_input);
echo $output;                            // SAFE - properly sanitized

// Heredoc syntax - SAFE with sanitization
$user_input = esc_html($_POST['input']);
echo <<<HTML
<div class="content">
    $user_input
</div>
HTML;                                    // SAFE - properly sanitized

// =============================================================================
// MIXED CONTENT PATTERNS (SAFE)
// =============================================================================

// Mixed safe and unsafe content - SAFE with sanitization
$user_input = $_GET['input'];
$safe_content = "This is safe content";
echo "<div>" . $safe_content . " " . esc_html($user_input) . "</div>"; // SAFE - properly sanitized

// Multiple user inputs - SAFE with sanitization
$input1 = $_POST['input1'];
$input2 = $_POST['input2'];
echo "<form><input value='" . esc_attr($input1) . "'><input value='" . esc_attr($input2) . "'></form>"; // SAFE - properly sanitized

// =============================================================================
// ALTERNATIVE SANITIZATION METHODS
// =============================================================================

// Using WordPress sanitization functions
$user_input = $_GET['input'];
echo sanitize_text_field($user_input);   // SAFE - using sanitize_text_field

$user_input = $_POST['input'];
echo sanitize_email($user_input);        // SAFE - using sanitize_email

$user_input = $_REQUEST['input'];
echo sanitize_url($user_input);          // SAFE - using sanitize_url

// Using WordPress content filtering
$user_input = $_POST['input'];
echo wp_kses_post($user_input);          // SAFE - using wp_kses_post

$user_input = $_GET['input'];
echo wp_kses($user_input, array('p' => array())); // SAFE - using wp_kses with allowed tags

// Using type casting
$user_input = $_GET['input'];
echo (int)$user_input;                   // SAFE - using integer casting

$user_input = $_POST['input'];
echo (float)$user_input;                 // SAFE - using float casting

// Using validation functions
$user_input = $_REQUEST['input'];
if (is_numeric($user_input)) {
    echo $user_input;                    // SAFE - validated as numeric
}

$user_input = $_GET['input'];
if (is_email($user_input)) {
    echo esc_html($user_input);          // SAFE - validated as email
}

// =============================================================================
// CONTEXT-SPECIFIC SANITIZATION
// =============================================================================

// HTML context - use esc_html
$user_input = $_GET['input'];
echo "<div>" . esc_html($user_input) . "</div>";

// Attribute context - use esc_attr
$user_input = $_POST['input'];
echo "<input value='" . esc_attr($user_input) . "'>";

// JavaScript context - use esc_js
$user_input = $_REQUEST['input'];
echo "<script>var data = '" . esc_js($user_input) . "';</script>";

// URL context - use esc_url
$user_input = $_GET['input'];
echo "<a href='" . esc_url($user_input) . "'>Link</a>";

// JSON context - use wp_json_encode
$user_input = $_POST['input'];
echo wp_json_encode($user_input);

// =============================================================================
// CONFIGURATION COMMENTS FOR TESTING
// =============================================================================

// XSS_TAINT_SOURCES: $_GET, $_POST, $_REQUEST, $_COOKIE, $wpdb->get_*, get_post_meta, get_option, file_get_contents
// XSS_TAINT_SINKS: echo, print, printf, HTML output, JavaScript output, wp_send_json, wp_json_encode
// XSS_TAINT_SANITIZERS: esc_html, esc_attr, esc_js, esc_url, sanitize_text_field, wp_kses_post, wp_json_encode
