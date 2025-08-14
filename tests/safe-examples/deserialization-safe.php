<?php
// Safe: restrict classes when unserializing or avoid unserialize entirely
if ( isset($_POST['data']) ) {
    $data = $_POST['data'];
    // If unserialize needed, use allowed_classes=false (PHP 7) or array of allowed
    $allowed = ['stdClass'];
    @unserialize($data, ['allowed_classes' => $allowed]);
}
?>


