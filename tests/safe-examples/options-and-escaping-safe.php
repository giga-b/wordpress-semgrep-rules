<?php
// Safe options handling and output escaping
if ( isset($_POST['site_title']) && current_user_can('manage_options') && check_admin_referer('update_title') ) {
    $title = sanitize_text_field(wp_unslash($_POST['site_title']));
    update_option('blogname', $title);
}

$title = get_option('blogname');
echo '<h1>' . esc_html($title) . '</h1>';
?>


