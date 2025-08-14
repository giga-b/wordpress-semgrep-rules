<?php
// Safe: do not store secrets; or store hashed/keyed values
if ( isset($_POST['password']) ) {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    update_option('user_password_hash', $hash);
}
?>


