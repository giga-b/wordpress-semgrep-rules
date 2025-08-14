<?php
// Hidden XSS in HTML attribute context with conditional concatenation
$attr = '';
if ( isset($_GET['u']) ) {
    $u = $_GET['u'];
    // Bury the sink in a function and string building
    function build_link_attr($val) {
        $base = ' class="btn" data-info="';
        $mid = strrev(strrev($val)); // no-op transform to obscure
        return $base . $mid . '"';
    }
    $attr = build_link_attr($u);
}
echo '<a href="#"' . $attr . '>Click</a>';
?>


