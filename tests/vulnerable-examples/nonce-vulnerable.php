<?php
/**
 * Vulnerable Nonce Examples
 * These examples demonstrate common nonce security vulnerabilities
 */

// VULNERABLE: Missing nonce creation in form
function vulnerable_form_without_nonce() {
    ?>
    <form method="post">
        <input type="text" name="user_data" />
        <input type="submit" value="Submit" />
    </form>
    <?php
}

// VULNERABLE: Missing nonce verification
function vulnerable_form_processing() {
    if (isset($_POST['submit'])) {
        // Process form data without nonce verification
        $user_data = $_POST['user_data'];
        update_option('user_data', $user_data);
    }
}

// VULNERABLE: Weak nonce creation
function vulnerable_weak_nonce() {
    $nonce = wp_create_nonce(""); // Empty action
    $nonce2 = wp_create_nonce("action"); // Generic action
    $nonce3 = wp_create_nonce($variable); // Variable action
}

// VULNERABLE: Missing nonce field
function vulnerable_form_missing_field() {
    ?>
    <form method="post">
        <input type="text" name="data" />
        <input type="submit" value="Submit" />
    </form>
    <?php
}

// VULNERABLE: Weak nonce verification
function vulnerable_weak_verification() {
    wp_verify_nonce($_POST['_wpnonce'], 'action_name');
    // Process data without checking return value
    $data = $_POST['data'];
}

// VULNERABLE: Wrong action name in verification
function vulnerable_wrong_action() {
    wp_create_nonce('save_post_action');
    // ... later in code ...
    wp_verify_nonce($_POST['_wpnonce'], 'delete_post_action'); // Wrong action
}

// VULNERABLE: AJAX without nonce verification
function vulnerable_ajax_handler() {
    add_action('wp_ajax_my_action', 'my_ajax_handler');
}

function my_ajax_handler() {
    // Process AJAX request without nonce check
    $data = $_POST['data'];
    wp_send_json_success($data);
}

// VULNERABLE: Weak AJAX nonce verification
function vulnerable_weak_ajax() {
    check_ajax_referer(""); // Empty action
    check_ajax_referer("action"); // Generic action
    check_ajax_referer($variable); // Variable action
}

// VULNERABLE: REST API without nonce verification
function vulnerable_rest_endpoint() {
    register_rest_route('my-namespace/v1', '/endpoint', array(
        'methods' => 'POST',
        'callback' => 'my_rest_callback',
        'permission_callback' => '__return_true'
    ));
}

function my_rest_callback($request) {
    // Process request without nonce verification
    $data = $request->get_param('data');
    return new WP_REST_Response($data, 200);
}

// VULNERABLE: Poor nonce expiration handling
function vulnerable_expiration_handling() {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {
        // Handle invalid nonce without checking expiration
        die('Invalid nonce');
    }
}

// VULNERABLE: Hardcoded action names
function vulnerable_hardcoded_actions() {
    wp_create_nonce("action");
    wp_verify_nonce($_POST['_wpnonce'], "action");
    wp_nonce_field("action");
}

// VULNERABLE: Multiple nonce actions in same form
function vulnerable_multiple_actions() {
    ?>
    <form method="post">
        <?php wp_nonce_field('action1'); ?>
        <input type="text" name="data1" />
        <?php wp_nonce_field('action2'); ?>
        <input type="text" name="data2" />
        <input type="submit" value="Submit" />
    </form>
    <?php
}
