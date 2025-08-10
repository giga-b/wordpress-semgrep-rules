<?php
/**
 * Vulnerable Capability Check Examples
 * These examples demonstrate common capability check security vulnerabilities
 */

// VULNERABLE: Missing capability check for user deletion
function vulnerable_delete_user() {
    if (isset($_POST['delete_user'])) {
        wp_delete_user($_POST['user_id']); // Missing capability check
    }
}

// VULNERABLE: Missing admin capability check for settings
function vulnerable_update_settings() {
    if (isset($_POST['update_settings'])) {
        update_option('sensitive_setting', $_POST['value']); // Missing admin check
    }
}

// VULNERABLE: Weak capability check
function vulnerable_weak_check() {
    if (current_user_can('read')) { // Too permissive for sensitive operation
        wp_delete_user($_POST['user_id']);
    }
}

// VULNERABLE: Capability check without nonce verification
function vulnerable_capability_no_nonce() {
    if (current_user_can('manage_options')) {
        if (isset($_POST['submit'])) {
            update_option('setting', $_POST['value']); // Missing nonce check
        }
    }
}

// VULNERABLE: AJAX handler missing capability check
function vulnerable_ajax_handler() {
    add_action('wp_ajax_delete_user', 'delete_user_ajax');
}

function delete_user_ajax() {
    wp_delete_user($_POST['user_id']); // Missing capability check
}

// VULNERABLE: Weak AJAX capability check
function vulnerable_weak_ajax() {
    if (current_user_can('read')) { // Too permissive
        // Perform sensitive operation
        wp_delete_user($_POST['user_id']);
    }
}

// VULNERABLE: REST API missing capability check
function vulnerable_rest_endpoint() {
    register_rest_route('my-namespace/v1', '/users', array(
        'methods' => 'DELETE',
        'callback' => 'delete_user_rest',
        'permission_callback' => '__return_true' // Allows anyone
    ));
}

function delete_user_rest($request) {
    wp_delete_user($request->get_param('user_id'));
    return new WP_REST_Response('User deleted', 200);
}

// VULNERABLE: Weak REST API capability check
function vulnerable_weak_rest() {
    register_rest_route('my-namespace/v1', '/admin', array(
        'methods' => 'POST',
        'callback' => 'admin_action_rest',
        'permission_callback' => function() {
            return current_user_can('read'); // Too permissive for admin action
        }
    ));
}

// VULNERABLE: Using role check instead of capability check
function vulnerable_role_check() {
    if (current_user_can('administrator')) { // Should use specific capability
        // Admin operation
        update_option('admin_setting', $_POST['value']);
    }
}

// VULNERABLE: Direct role comparison
function vulnerable_role_comparison() {
    $user = wp_get_current_user();
    if ($user->roles[0] === 'administrator') { // Insecure role check
        // Admin operation
        wp_delete_user($_POST['user_id']);
    }
}

// VULNERABLE: Direct role array check
function vulnerable_role_array_check() {
    $user = wp_get_current_user();
    if (in_array('administrator', $user->roles)) { // Insecure role check
        // Admin operation
        update_option('admin_setting', $_POST['value']);
    }
}

// VULNERABLE: Missing capability check in conditional logic
function vulnerable_conditional_missing() {
    $action = $_POST['action'];
    if ($action === 'delete') {
        wp_delete_user($_POST['user_id']); // Missing capability check
    } elseif ($action === 'update') {
        wp_update_user($_POST['user_data']); // Missing capability check
    }
}

// VULNERABLE: File operation missing capability check
function vulnerable_file_operation() {
    if (isset($_POST['upload_file'])) {
        move_uploaded_file($_FILES['file']['tmp_name'], $destination); // Missing capability check
    }
}

// VULNERABLE: Database operation missing capability check
function vulnerable_db_operation() {
    if (isset($_POST['delete_option'])) {
        delete_option($_POST['option_name']); // Missing capability check
    }
}

// VULNERABLE: Plugin management missing capability check
function vulnerable_plugin_management() {
    if (isset($_POST['activate_plugin'])) {
        activate_plugin($_POST['plugin_file']); // Missing capability check
    }
}

// VULNERABLE: User management missing capability check
function vulnerable_user_management() {
    if (isset($_POST['create_user'])) {
        wp_insert_user($_POST['user_data']); // Missing capability check
    }
}

// VULNERABLE: Content management missing capability check
function vulnerable_content_management() {
    if (isset($_POST['publish_post'])) {
        wp_publish_post($_POST['post_id']); // Missing capability check
    }
}

// VULNERABLE: Settings management missing capability check
function vulnerable_settings_management() {
    if (isset($_POST['update_settings'])) {
        update_option('site_title', $_POST['title']);
        update_option('site_description', $_POST['description']); // Missing capability check
    }
}

// VULNERABLE: Multisite operation missing capability check
function vulnerable_multisite_operation() {
    if (isset($_POST['create_site'])) {
        wpmu_create_blog($_POST['domain'], $_POST['path'], $_POST['title']); // Missing capability check
    }
}

// VULNERABLE: Hardcoded capability strings
function vulnerable_hardcoded_capabilities() {
    if (current_user_can("manage_options")) { // Hardcoded string
        // Admin operation
    }
    if (current_user_can("delete_users")) { // Hardcoded string
        // Delete operation
    }
}

// VULNERABLE: Multiple capability checks that could be optimized
function vulnerable_multiple_checks() {
    if (current_user_can('edit_posts') && current_user_can('publish_posts')) { // Could be optimized
        // Operation
    }
}

// VULNERABLE: Overly permissive capability for sensitive operation
function vulnerable_overly_permissive() {
    if (current_user_can('edit_posts')) { // Too permissive for user deletion
        wp_delete_user($_POST['user_id']);
    }
    if (current_user_can('publish_posts')) { // Too permissive for admin settings
        update_option('admin_setting', $_POST['value']);
    }
}

// VULNERABLE: Missing capability check for file upload
function vulnerable_file_upload() {
    if (isset($_FILES['file'])) {
        $upload_dir = wp_upload_dir();
        move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir['path'] . '/' . $_FILES['file']['name']); // Missing capability check
    }
}

// VULNERABLE: Missing capability check for theme activation
function vulnerable_theme_activation() {
    if (isset($_POST['activate_theme'])) {
        switch_theme($_POST['theme_name']); // Missing capability check
    }
}

// VULNERABLE: Missing capability check for plugin installation
function vulnerable_plugin_installation() {
    if (isset($_POST['install_plugin'])) {
        // Plugin installation logic without capability check
        $plugin_file = $_POST['plugin_file'];
        // Install plugin...
    }
}

// VULNERABLE: Missing capability check for user role changes
function vulnerable_user_role_change() {
    if (isset($_POST['change_role'])) {
        $user = get_user_by('id', $_POST['user_id']);
        $user->set_role($_POST['new_role']); // Missing capability check
    }
}

// VULNERABLE: Missing capability check for site options
function vulnerable_site_options() {
    if (isset($_POST['update_site_option'])) {
        update_site_option($_POST['option_name'], $_POST['option_value']); // Missing capability check
    }
}

// VULNERABLE: Missing capability check for network operations
function vulnerable_network_operations() {
    if (isset($_POST['create_network_user'])) {
        wpmu_create_user($_POST['username'], $_POST['password'], $_POST['email']); // Missing capability check
    }
}
