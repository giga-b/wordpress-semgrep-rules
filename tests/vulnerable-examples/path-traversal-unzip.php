<?php
// Path traversal via unzip to user-controlled directory
$dest = isset($_POST['dest']) ? $_POST['dest'] : (WP_CONTENT_DIR . '/uploads/tmp');
$zip = isset($_FILES['zip']) ? $_FILES['zip']['tmp_name'] : '';
if ($zip) {
    // No canonicalization/sanitization of $dest
    unzip_file($zip, $dest);
}
?>


