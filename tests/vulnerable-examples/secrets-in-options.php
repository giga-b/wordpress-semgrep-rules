<?php
// Storing secrets directly in options without protection
if ( isset($_POST['api_key']) ) {
    add_option('third_party_api_key', $_POST['api_key']);
}
if ( isset($_POST['secret']) ) {
    update_option('my_service_secret', $_POST['secret']);
}
?>


