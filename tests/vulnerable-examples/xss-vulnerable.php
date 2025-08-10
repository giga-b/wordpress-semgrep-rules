<?php
// This should trigger XSS rule
$user_input = $_GET['message'];
echo "<div class='message'>" . $user_input . "</div>";

// Another XSS pattern
$comment = $_POST['comment'];
print "<p>" . $comment . "</p>";

// XSS in attributes
$url = $_GET['url'];
echo "<a href='" . $url . "'>Click here</a>";

// XSS in JavaScript
$data = $_POST['data'];
echo "<script>var data = '" . $data . "';</script>";

// XSS in CSS
$color = $_GET['color'];
echo "<div style='color: " . $color . "'>Content</div>";
