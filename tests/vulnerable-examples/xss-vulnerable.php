<?php
// XSS Prevention Rules - Vulnerable Examples
// This file contains examples that SHOULD trigger XSS prevention rules

// 1. HTML Context - Direct Output Without Escaping
$user_input = $_GET['input'];
echo $user_input;

$post_data = $_POST['data'];
print $post_data;

$request_data = $_REQUEST['content'];
echo "<div>" . $request_data . "</div>";

// 2. HTML Attribute Context - Unsafe Attribute Values
$value = $_GET['value'];
echo "<input value='" . $value . "'>";

$title = $_POST['title'];
echo "<div title='" . $title . "'>Content</div>";

$class = $_REQUEST['class'];
echo "<span class='" . $class . "'>Text</span>";

// 3. URL Context - Unsafe URLs
$url = $_GET['url'];
echo "<a href='" . $url . "'>Link</a>";

$redirect = $_POST['redirect'];
echo "<meta http-equiv='refresh' content='0;url=" . $redirect . "'>";

$image_url = $_REQUEST['image'];
echo "<img src='" . $image_url . "'>";

// 4. JavaScript Context - Unsafe JavaScript
$data = $_GET['data'];
echo "<script>var data = '" . $data . "';</script>";

$message = $_POST['message'];
echo "<script>alert('" . $message . "');</script>";

$value = $_REQUEST['value'];
echo "<script>document.getElementById('input').value = '" . $value . "';</script>";

// 5. CSS Context - Unsafe CSS
$color = $_GET['color'];
echo "<div style='color: " . $color . "'>Content</div>";

$background = $_POST['background'];
echo "<span style='background-color: " . $background . "'>Text</span>";

$size = $_REQUEST['size'];
echo "<p style='font-size: " . $size . "'>Text</p>";

// 6. Form Input Context - Unsafe Form Values
$form_value = $_POST['form_value'];
echo "<input type='text' value='" . $form_value . "'>";

$selected = $_GET['selected'];
echo "<option value='1' " . ($selected == '1' ? 'selected' : '') . ">Option</option>";

// 7. Content Context - Unsafe Content Output
$content = $_POST['content'];
echo "<div class='content'>" . $content . "</div>";

$description = $_GET['description'];
echo "<p>" . $description . "</p>";

// 8. Database Output - Unsafe Database Output
global $wpdb;
$result = $wpdb->get_row("SELECT * FROM posts WHERE id = 1");
echo "<h1>" . $result->title . "</h1>";

$posts = $wpdb->get_results("SELECT * FROM posts");
foreach ($posts as $post) {
    echo "<div>" . $post->content . "</div>";
}

// 9. AJAX Response - Unsafe AJAX Output
$ajax_data = $_POST['ajax_data'];
wp_send_json_success($ajax_data);

$ajax_message = $_GET['ajax_message'];
echo json_encode(['message' => $ajax_message]);

// 10. REST API Response - Unsafe REST Output
$rest_content = $_POST['rest_content'];
return new WP_REST_Response(['content' => $rest_content], 200);

$rest_title = $_GET['rest_title'];
return rest_ensure_response(['title' => $rest_title]);

// 11. Template Context - Unsafe Template Variables
$template_title = get_post_meta($post_id, 'custom_title', true);
echo "<h1>" . $template_title . "</h1>";

$template_description = get_option('site_description');
echo "<meta name='description' content='" . $template_description . "'>";

// 12. Widget Output - Unsafe Widget Content
$widget_text = $instance['text'];
echo "<div class='widget-content'>" . $widget_text . "</div>";

$widget_title = $instance['title'];
echo "<h3>" . $widget_title . "</h3>";

// 13. Shortcode Output - Unsafe Shortcode Content
$shortcode_content = $atts['content'];
return "<div class='shortcode'>" . $shortcode_content . "</div>";

$shortcode_title = $atts['title'];
return "<h2>" . $shortcode_title . "</h2>";

// 14. Admin Output - Unsafe Admin Content
$admin_message = $_GET['admin_message'];
echo "<div class='notice'>" . $admin_message . "</div>";

$error_msg = $_POST['error_msg'];
echo "<div class='error'>" . $error_msg . "</div>";

// 15. Email Content - Unsafe Email Output
$email_content = $_POST['email_content'];
wp_mail($to, $subject, $email_content);

$email_message = $_GET['email_message'];
$headers = "Content-Type: text/html; charset=UTF-8";
wp_mail($to, $subject, $email_message, $headers);

// 16. Logging - Unsafe Log Output
$log_data = $_POST['log_data'];
error_log("User submitted: " . $log_data);

$log_input = $_GET['log_input'];
trigger_error("Invalid input: " . $log_input, E_USER_WARNING);

// 17. File Content - Unsafe File Output
$file_content = file_get_contents($file_path);
echo "<pre>" . $file_content . "</pre>";

$log_content = file_get_contents($log_file);
echo "<div class='log'>" . $log_content . "</div>";

// 18. Comment Content - Unsafe Comment Output
$comment_content = $comment->comment_content;
echo "<div class='comment'>" . $comment_content . "</div>";

$comment_author = $comment->comment_author;
echo "<span class='author'>" . $comment_author . "</span>";

// 19. Meta Data - Unsafe Meta Output
$meta_description = get_post_meta($post_id, 'description', true);
echo "<meta name='description' content='" . $meta_description . "'>";

$meta_keywords = get_option('site_keywords');
echo "<meta name='keywords' content='" . $meta_keywords . "'>";

// 20. JSON Output - Unsafe JSON
$json_data = $_POST['json_data'];
echo json_encode(['result' => $json_data]);

$json_message = $_GET['json_message'];
echo '{"message": "' . $json_message . '"}';

// 21. XML Output - Unsafe XML
$xml_title = $_POST['xml_title'];
echo "<title>" . $xml_title . "</title>";

$xml_content = $_GET['xml_content'];
echo "<content>" . $xml_content . "</content>";

// 22. RSS Feed - Unsafe RSS Output
$rss_title = $_POST['rss_title'];
echo "<title>" . $rss_title . "</title>";

$rss_description = $_GET['rss_description'];
echo "<description>" . $rss_description . "</description>";

// 23. API Response Headers - Unsafe Headers
$header_value = $_GET['header_value'];
header("X-Custom-Header: " . $header_value);

$cookie_value = $_POST['cookie_value'];
setcookie("custom_cookie", $cookie_value);

// 24. Debug Output - Unsafe Debug
$debug_data = $_POST['debug_data'];
var_dump($debug_data);

$debug_info = $_GET['debug_info'];
print_r($debug_info);

// 25. Complex XSS Patterns
$user_input = $_GET['user_input'];
echo "<div class='user-content'>" . $user_input . "</div>";
echo "<script>var userData = '" . $user_input . "';</script>";
echo "<a href='" . $user_input . "'>Click here</a>";

// 26. Nested XSS Patterns
$nested_data = $_POST['nested_data'];
echo "<div style='background: " . $nested_data . "'>";
echo "<span title='" . $nested_data . "'>" . $nested_data . "</span>";
echo "</div>";

// 27. Conditional XSS
$conditional_input = $_GET['conditional'];
if ($conditional_input) {
    echo "<div>" . $conditional_input . "</div>";
}

// 28. Loop XSS
$loop_data = $_POST['loop_data'];
foreach ($loop_data as $item) {
    echo "<li>" . $item . "</li>";
}

// 29. Function Parameter XSS
function display_content($content) {
    echo "<div>" . $content . "</div>";
}
display_content($_GET['function_content']);

// 30. Class Property XSS
class ContentDisplay {
    public $content;
    
    public function display() {
        echo "<div>" . $this->content . "</div>";
    }
}
$display = new ContentDisplay();
$display->content = $_POST['class_content'];
$display->display();
