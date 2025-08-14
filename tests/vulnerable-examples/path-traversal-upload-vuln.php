<?php
// Minimal vulnerable sample for user-controlled upload directory

if (!function_exists('add_action')) { function add_action($hook, $cb){ /* no-op */ } }

// Simulate a simple upload handler with user-controlled directory
function upload_to_user_dir() {
    $dir = $_POST['dir'];
    $file = $_FILES['file'];
    move_uploaded_file($file['tmp_name'], $dir . '/' . $file['name']); // vulnerable: user-controlled dir
}


