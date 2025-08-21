<?php
// Non-Upload File Operations - Safe Examples
// These should NOT be detected by the tightened $TMP source rules

// 1. Moving a temporary file that is NOT from $_FILES
$temp_file = '/tmp/processed_data.txt';
$destination = '/var/www/html/data/processed_data.txt';
move_uploaded_file($temp_file, $destination); // This should NOT be detected (not from $_FILES)

// 2. Moving a cache file
$cache_file = sys_get_temp_dir() . '/cache_' . uniqid() . '.tmp';
$cache_dest = '/var/www/html/cache/' . basename($cache_file);
move_uploaded_file($cache_file, $cache_dest); // This should NOT be detected (not from $_FILES)

// 3. Moving a generated file
$generated_file = '/tmp/generated_' . time() . '.pdf';
$output_dir = '/var/www/html/output/';
move_uploaded_file($generated_file, $output_dir . basename($generated_file)); // This should NOT be detected

// 4. Moving a downloaded file (not uploaded)
$downloaded_file = '/tmp/downloaded_' . md5(uniqid()) . '.zip';
$extract_dir = '/var/www/html/extract/';
move_uploaded_file($downloaded_file, $extract_dir . basename($downloaded_file)); // This should NOT be detected

// 5. Moving a backup file
$backup_file = '/tmp/backup_' . date('Y-m-d') . '.sql';
$backup_dir = '/var/www/html/backups/';
move_uploaded_file($backup_file, $backup_dir . basename($backup_file)); // This should NOT be detected

// 6. Moving a log file
$log_file = '/tmp/application_' . date('Y-m-d_H-i-s') . '.log';
$log_dir = '/var/www/html/logs/';
move_uploaded_file($log_file, $log_dir . basename($log_file)); // This should NOT be detected

// 7. Moving a session file
$session_file = '/tmp/sess_' . session_id();
$session_backup = '/var/www/html/sessions/' . basename($session_file);
move_uploaded_file($session_file, $session_backup); // This should NOT be detected

// 8. Moving a temporary image (not from upload)
$temp_image = '/tmp/processed_' . uniqid() . '.jpg';
$image_dest = '/var/www/html/images/' . basename($temp_image);
move_uploaded_file($temp_image, $image_dest); // This should NOT be detected
?>
