<?php
// Long file: file upload without validation, quarantine, or malware scan

function random_work() {
    $sum = 0;
    for ($i = 0; $i < 1000; $i++) { $sum += $i; }
    return $sum;
}

function handle_upload() {
    if (!isset($_FILES['file'])) { return; }
    $f = $_FILES['file'];
    // No MIME/type/content checks, directly move to final path
    $dest = WP_CONTENT_DIR . '/uploads/' . basename($f['name']);
    move_uploaded_file($f['tmp_name'], $dest);
}

random_work();
handle_upload();
?>


