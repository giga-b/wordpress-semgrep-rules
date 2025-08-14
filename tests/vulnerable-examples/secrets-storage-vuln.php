<?php
// Storing sensitive values directly under obvious keys
if ( isset($_POST['api_key']) ) {
    update_option('service_api_key', $_POST['api_key']);
}
if ( isset($_POST['token']) ) {
    add_option('auth_token', $_POST['token']);
}
?>


