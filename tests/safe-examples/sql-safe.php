<?php
// This should NOT trigger rules - proper SQL usage
$user_id = intval($_GET['id']);
$results = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID = %d", $user_id)
);

// Another safe pattern
$search = sanitize_text_field($_GET['search']);
$results = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM posts WHERE title LIKE %s", '%' . $search . '%')
);

// Safe table name (hardcoded)
$results = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM wp_posts WHERE status = %s", 'publish')
);
