<?php
// XSS Prevention Rules - Safe Examples
// This file contains examples that should NOT trigger XSS prevention rules

// 1. HTML Context - Safe Output with Escaping
$user_input = $_GET['input'];
echo esc_html($user_input);

$post_data = $_POST['data'];
print esc_html($post_data);

$request_data = $_REQUEST['content'];
echo "<div>" . esc_html($request_data) . "</div>";

// 2. HTML Attribute Context - Safe Attribute Values
$value = $_GET['value'];
echo "<input value='" . esc_attr($value) . "'>";

$title = $_POST['title'];
echo "<div title='" . esc_attr($title) . "'>Content</div>";

$class = $_REQUEST['class'];
echo "<span class='" . esc_attr($class) . "'>Text</span>";

// 3. URL Context - Safe URLs
$url = $_GET['url'];
echo "<a href='" . esc_url($url) . "'>Link</a>";

$redirect = $_POST['redirect'];
echo "<meta http-equiv='refresh' content='0;url=" . esc_url_raw($redirect) . "'>";

$image_url = $_REQUEST['image'];
echo "<img src='" . esc_url($image_url) . "'>";

// 4. JavaScript Context - Safe JavaScript
$data = $_GET['data'];
echo "<script>var data = '" . esc_js($data) . "';</script>";

$message = $_POST['message'];
echo "<script>alert(" . wp_json_encode($message) . ");</script>";

$value = $_REQUEST['value'];
echo "<script>document.getElementById('input').value = '" . esc_js($value) . "';</script>";

// 5. CSS Context - Safe CSS
$color = $_GET['color'];
echo "<div style='color: " . sanitize_hex_color($color) . "'>Content</div>";

$background = $_POST['background'];
echo "<span style='background-color: " . esc_attr($background) . "'>Text</span>";

$size = $_REQUEST['size'];
echo "<p style='font-size: " . esc_attr($size) . "'>Text</p>";

// 6. Form Input Context - Safe Form Values
$form_value = $_POST['form_value'];
echo "<input type='text' value='" . esc_attr($form_value) . "'>";

$selected = $_GET['selected'];
echo "<option value='1' " . ($selected == '1' ? 'selected' : '') . ">Option</option>";

// 7. Content Context - Safe Content Output
$content = $_POST['content'];
echo "<div class='content'>" . wp_kses_post($content) . "</div>";

$description = $_GET['description'];
echo "<p>" . esc_html($description) . "</p>";

// 8. Database Output - Safe Database Output
global $wpdb;
$result = $wpdb->get_row("SELECT * FROM posts WHERE id = 1");
echo "<h1>" . esc_html($result->title) . "</h1>";

$posts = $wpdb->get_results("SELECT * FROM posts");
foreach ($posts as $post) {
    echo "<div>" . wp_kses_post($post->content) . "</div>";
}

// 9. AJAX Response - Safe AJAX Output
$ajax_data = sanitize_text_field($_POST['ajax_data']);
wp_send_json_success($ajax_data);

$ajax_message = esc_html($_GET['ajax_message']);
echo wp_json_encode(['message' => $ajax_message]);

// 10. REST API Response - Safe REST Output
$rest_content = wp_kses_post($_POST['rest_content']);
return new WP_REST_Response(['content' => $rest_content], 200);

$rest_title = esc_html($_GET['rest_title']);
return rest_ensure_response(['title' => $rest_title]);

// 11. Template Context - Safe Template Variables
$template_title = esc_html(get_post_meta($post_id, 'custom_title', true));
echo "<h1>" . $template_title . "</h1>";

$template_description = esc_attr(get_option('site_description'));
echo "<meta name='description' content='" . $template_description . "'>";

// 12. Widget Output - Safe Widget Content
$widget_text = wp_kses_post($instance['text']);
echo "<div class='widget-content'>" . $widget_text . "</div>";

$widget_title = esc_html($instance['title']);
echo "<h3>" . $widget_title . "</h3>";

// 13. Shortcode Output - Safe Shortcode Content
$shortcode_content = wp_kses_post($atts['content']);
return "<div class='shortcode'>" . $shortcode_content . "</div>";

$shortcode_title = esc_html($atts['title']);
return "<h2>" . $shortcode_title . "</h2>";

// 14. Admin Output - Safe Admin Content
$admin_message = esc_html($_GET['admin_message']);
echo "<div class='notice'>" . $admin_message . "</div>";

$error_msg = wp_kses_post($_POST['error_msg']);
echo "<div class='error'>" . $error_msg . "</div>";

// 15. Email Content - Safe Email Output
$email_content = esc_html($_POST['email_content']);
wp_mail($to, $subject, $email_content);

$email_message = wp_kses_post($_GET['email_message']);
$headers = "Content-Type: text/html; charset=UTF-8";
wp_mail($to, $subject, $email_message, $headers);

// 16. Logging - Safe Log Output
$log_data = esc_html($_POST['log_data']);
error_log("User submitted: " . $log_data);

$log_input = sanitize_text_field($_GET['log_input']);
trigger_error("Invalid input: " . $log_input, E_USER_WARNING);

// 17. File Content - Safe File Output
$file_content = esc_html(file_get_contents($file_path));
echo "<pre>" . $file_content . "</pre>";

$log_content = esc_html(file_get_contents($log_file));
echo "<div class='log'>" . $log_content . "</div>";

// 18. Comment Content - Safe Comment Output
$comment_content = wp_kses_post($comment->comment_content);
echo "<div class='comment'>" . $comment_content . "</div>";

$comment_author = esc_html($comment->comment_author);
echo "<span class='author'>" . $comment_author . "</span>";

// 19. Meta Data - Safe Meta Output
$meta_description = esc_attr(get_post_meta($post_id, 'description', true));
echo "<meta name='description' content='" . $meta_description . "'>";

$meta_keywords = esc_attr(get_option('site_keywords'));
echo "<meta name='keywords' content='" . $meta_keywords . "'>";

// 20. JSON Output - Safe JSON
$json_data = sanitize_text_field($_POST['json_data']);
echo wp_json_encode(['result' => $json_data]);

$json_message = esc_html($_GET['json_message']);
echo wp_json_encode(['message' => $json_message]);

// 21. XML Output - Safe XML
$xml_title = esc_html($_POST['xml_title']);
echo "<title>" . $xml_title . "</title>";

$xml_content = esc_html($_GET['xml_content']);
echo "<content>" . $xml_content . "</content>";

// 22. RSS Feed - Safe RSS Output
$rss_title = esc_html($_POST['rss_title']);
echo "<title>" . $rss_title . "</title>";

$rss_description = esc_html($_GET['rss_description']);
echo "<description>" . $rss_description . "</description>";

// 23. API Response Headers - Safe Headers
$header_value = esc_html($_GET['header_value']);
header("X-Custom-Header: " . $header_value);

$cookie_value = sanitize_text_field($_POST['cookie_value']);
setcookie("custom_cookie", $cookie_value);

// 24. Debug Output - Safe Debug
$debug_data = esc_html($_POST['debug_data']);
var_dump($debug_data);

$debug_info = esc_html($_GET['debug_info']);
print_r($debug_info);

// 25. Complex Safe Patterns
$user_input = $_GET['user_input'];
echo "<div class='user-content'>" . esc_html($user_input) . "</div>";
echo "<script>var userData = '" . esc_js($user_input) . "';</script>";
echo "<a href='" . esc_url($user_input) . "'>Click here</a>";

// 26. Nested Safe Patterns
$nested_data = $_POST['nested_data'];
echo "<div style='background: " . esc_attr($nested_data) . "'>";
echo "<span title='" . esc_attr($nested_data) . "'>" . esc_html($nested_data) . "</span>";
echo "</div>";

// 27. Conditional Safe Output
$conditional_input = $_GET['conditional'];
if ($conditional_input) {
    echo "<div>" . esc_html($conditional_input) . "</div>";
}

// 28. Loop Safe Output
$loop_data = $_POST['loop_data'];
foreach ($loop_data as $item) {
    echo "<li>" . esc_html($item) . "</li>";
}

// 29. Function Parameter Safe Output
function display_content($content) {
    echo "<div>" . esc_html($content) . "</div>";
}
display_content($_GET['function_content']);

// 30. Class Property Safe Output
class ContentDisplay {
    public $content;
    
    public function display() {
        echo "<div>" . esc_html($this->content) . "</div>";
    }
}
$display = new ContentDisplay();
$display->content = $_POST['class_content'];
$display->display();

// 31. Additional Safe Patterns
// Using WordPress sanitization functions
$sanitized_text = sanitize_text_field($_POST['text']);
echo "<p>" . $sanitized_text . "</p>";

$sanitized_email = sanitize_email($_POST['email']);
echo "<span>" . $sanitized_email . "</span>";

$sanitized_url = esc_url_raw($_POST['url']);
echo "<a href='" . $sanitized_url . "'>Link</a>";

// 32. Safe HTML with allowed tags
$allowed_html = array(
    'a' => array(
        'href' => array(),
        'title' => array()
    ),
    'br' => array(),
    'em' => array(),
    'strong' => array(),
);
$html_content = wp_kses($_POST['html_content'], $allowed_html);
echo "<div>" . $html_content . "</div>";

// 33. Safe database queries with prepared statements
$search_term = sanitize_text_field($_GET['search']);
$prepared_query = $wpdb->prepare("SELECT * FROM posts WHERE title LIKE %s", '%' . $wpdb->esc_like($search_term) . '%');
$results = $wpdb->get_results($prepared_query);
foreach ($results as $result) {
    echo "<h2>" . esc_html($result->title) . "</h2>";
    echo "<div>" . wp_kses_post($result->content) . "</div>";
}

// 34. Safe form handling
if (isset($_POST['submit'])) {
    $form_data = array(
        'name' => sanitize_text_field($_POST['name']),
        'email' => sanitize_email($_POST['email']),
        'message' => sanitize_textarea_field($_POST['message'])
    );
    
    echo "<div class='form-data'>";
    echo "<p><strong>Name:</strong> " . esc_html($form_data['name']) . "</p>";
    echo "<p><strong>Email:</strong> " . esc_html($form_data['email']) . "</p>";
    echo "<p><strong>Message:</strong> " . esc_html($form_data['message']) . "</p>";
    echo "</div>";
}

// 35. Safe cookie handling
$cookie_value = sanitize_text_field($_COOKIE['user_preference']);
echo "<div class='preference'>" . esc_html($cookie_value) . "</div>";

// 36. Safe session handling
session_start();
$session_data = sanitize_text_field($_SESSION['user_data']);
echo "<span>" . esc_html($session_data) . "</span>";

// 37. Safe file upload handling
if (isset($_FILES['upload'])) {
    $file_name = sanitize_file_name($_FILES['upload']['name']);
    echo "<p>Uploaded file: " . esc_html($file_name) . "</p>";
}

// 38. Safe array handling
$array_data = $_POST['array_data'];
if (is_array($array_data)) {
    foreach ($array_data as $key => $value) {
        echo "<div>" . esc_html($key) . ": " . esc_html($value) . "</div>";
    }
}

// 39. Safe object handling
$object_data = $_POST['object_data'];
if (is_object($object_data)) {
    echo "<div>" . esc_html($object_data->property) . "</div>";
}

// 40. Safe JSON handling
$json_string = $_POST['json_string'];
$decoded_data = json_decode($json_string, true);
if (is_array($decoded_data)) {
    echo "<div>" . esc_html($decoded_data['key']) . "</div>";
}
