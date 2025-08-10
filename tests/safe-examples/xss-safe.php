<?php
// This should NOT trigger rules - proper escaping
$user_input = $_GET['message'];
echo "<div class='message'>" . esc_html($user_input) . "</div>";

// Another safe pattern
$comment = wp_kses_post($_POST['comment']);
echo "<p>" . $comment . "</p>";

// Safe attribute usage
$url = esc_url($_GET['url']);
echo "<a href='" . $url . "'>Click here</a>";

// Safe JavaScript
$data = esc_js($_POST['data']);
echo "<script>var data = '" . $data . "';</script>";

// Safe CSS
$color = sanitize_hex_color($_GET['color']);
echo "<div style='color: " . $color . "'>Content</div>";
