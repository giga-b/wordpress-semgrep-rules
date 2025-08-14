<?php
// Simple direct XSS via unsanitized GET parameter output
// Should be flagged by XSS rules
if ( isset($_GET['msg']) ) {
    echo $_GET['msg'];
}
?>


