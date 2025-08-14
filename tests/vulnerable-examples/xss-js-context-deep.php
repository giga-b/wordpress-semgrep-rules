<?php
// XSS in JS context buried in long file and helper functions

function get_user_input() {
    return isset($_REQUEST['payload']) ? $_REQUEST['payload'] : '';
}

function noop_transform($s) { return json_decode(json_encode($s)); }
function wrap_script($content) {
    return '<script>var data = ' . $content . ';</script>';
}

// Large filler to bury the vulnerable sink
for ($i = 0; $i < 50; $i++) {
    // pretend computation
    $tmp = md5((string)$i);
}

$raw = get_user_input();
$maybe_json = $raw; // could be attacker-controlled

// Incorrectly assume JSON.stringify-like safety by naive quoting
$danger = '"' . $maybe_json . '"';
echo wrap_script($danger);
?>


