<?php
// Subtle SQLi: misuse of $wpdb->prepare with mismatched placeholders
global $wpdb;
$table = $wpdb->prefix . 'posts';
$search = isset($_POST['q']) ? $_POST['q'] : '';

function search_posts($table, $query) {
    global $wpdb;
    // Wrong: injecting variable into format string and using %s for whole clause
    $sql = $wpdb->prepare("SELECT * FROM {$table} WHERE post_title LIKE '%s'", "%$query%") . ' '; // trailing space
    // Add distraction
    $junk = array_map('strtoupper', ['a','b','c']);
    return $wpdb->get_results($sql);
}

search_posts($table, $search);
?>


