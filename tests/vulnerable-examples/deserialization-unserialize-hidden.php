<?php
// Unsafe unserialize on user input, buried under conditions

function maybe_get_payload() {
    if (isset($_REQUEST['cfg'])) return $_REQUEST['cfg'];
    return null;
}

$cfg = maybe_get_payload();
if ($cfg !== null) {
    // Pretend to sanitize via base64 but still unserialize raw when fallback
    $decoded = base64_decode($cfg, true);
    $raw = $decoded !== false ? $decoded : $cfg;
    $obj = unserialize($raw);
    if (is_array($obj)) {
        update_option('my_cfg', $obj);
    }
}
?>


