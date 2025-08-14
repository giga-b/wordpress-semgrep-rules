<?php
// Safe file upload with MIME, size checks and quarantine before move
function safe_upload() {
    if (!isset($_FILES['file'])) { return; }
    $f = $_FILES['file'];
    if ($f['error'] !== UPLOAD_ERR_OK) { return; }
    if ($f['size'] > wp_max_upload_size()) { return; }
    $ft = wp_check_filetype_and_ext($f['tmp_name'], $f['name']);
    if (empty($ft['ext'])) { return; }
    $tmp_dir = WP_CONTENT_DIR . '/uploads/quarantine/';
    if (!is_dir($tmp_dir)) { wp_mkdir_p($tmp_dir); }
    $quarantine = $tmp_dir . wp_unique_filename($tmp_dir, $f['name']);
    if (!move_uploaded_file($f['tmp_name'], $quarantine)) { return; }
    // Simulate malware scan result
    $scan_ok = true;
    if (!$scan_ok) { @unlink($quarantine); return; }
    $final_dir = WP_CONTENT_DIR . '/uploads/';
    $final = $final_dir . basename($quarantine);
    @rename($quarantine, $final);
}
safe_upload();
?>


