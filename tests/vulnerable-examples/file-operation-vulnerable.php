<?php
// This should trigger path traversal rule
$file = $_GET['file'];
$content = file_get_contents($file);

// Another file operation vulnerability
$path = $_POST['path'];
include($path);

// File upload without validation
$uploaded_file = $_FILES['file'];
move_uploaded_file($uploaded_file['tmp_name'], $uploaded_file['name']);

// ZIP extraction without path validation
$zip_file = $_GET['zip'];
unzip_file($zip_file, '/var/www/html/');

// Directory traversal
$user_path = $_GET['path'];
$full_path = '/var/www/html/' . $user_path;
file_get_contents($full_path);
