<?php
// Unsafe unserialize directly on user input
if ( isset($_REQUEST['cfg']) ) {
    $obj = unserialize($_REQUEST['cfg']);
}
?>


