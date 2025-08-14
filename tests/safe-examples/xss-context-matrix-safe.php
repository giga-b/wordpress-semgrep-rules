<?php
// XSS Context Matrix - Safe Examples
// These patterns should NOT trigger XSS rules when scanned.

// Minimal WordPress function stubs for linter/static analysis in isolated test files
if (!function_exists('esc_html')) { function esc_html($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('wp_kses_post')) { function wp_kses_post($s) { return $s; } }
if (!function_exists('esc_attr')) { function esc_attr($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('esc_js')) { function esc_js($s) { return addslashes($s); } }
if (!function_exists('esc_url')) { function esc_url($s) { return $s; } }
if (!function_exists('sanitize_text_field')) { function sanitize_text_field($s) { return is_string($s) ? trim($s) : $s; } }
if (!function_exists('wp_send_json_success')) { function wp_send_json_success($d) { echo json_encode(['success' => true, 'data' => $d]); } }
if (!function_exists('wp_json_encode')) { function wp_json_encode($d) { return json_encode($d); } }
if (!function_exists('add_action')) { function add_action($hook, $cb) { /* no-op */ } }
if (!function_exists('register_rest_route')) { function register_rest_route($ns, $route, $args) { /* no-op */ } }
if (!class_exists('WP_REST_Response')) { class WP_REST_Response { public $data; public $status; public function __construct($d, $s = 200) { $this->data = $d; $this->status = $s; } } }

// 1. HTML Content Context
$html_get = $_GET['html'];
echo esc_html($html_get);

$html_post = $_POST['html'];
echo "<div>" . wp_kses_post($html_post) . "</div>";

// 2. HTML Attribute Context (quoted and unquoted inputs → always escape into quoted attrs)
$attr_val = $_GET['val'];
echo "<input value='" . esc_attr($attr_val) . "'>";

$attr_unquoted = $_POST['q'];
echo "<input value='" . esc_attr($attr_unquoted) . "'>";

$class_name = $_REQUEST['cls'];
echo "<span class='" . esc_attr($class_name) . "'>Item</span>";

// 3. JavaScript Context (inline script and event handlers)
$js_var = $_GET['js'];
echo "<script>var msg = '" . esc_js($js_var) . "';</script>";

$js_event = $_POST['onclick'];
// Prefer avoiding inline handlers; if present, escape attribute context
echo "<button onclick='" . esc_attr($js_event) . "'>Click</button>";

// 4. URL/HREF/SRC Context
$href = $_GET['href'];
echo "<a href='" . esc_url($href) . "'>link</a>";

$src = $_POST['src'];
echo "<img src='" . esc_url($src) . "'>";

// 5. CSS Inline Style Context
$color = $_GET['color'];
echo "<div style='color: " . esc_attr($color) . "'>text</div>";

$bg = $_POST['bg'];
// If restricted to colors, use sanitize_hex_color; otherwise attribute-escape
$bg_safe = esc_attr($bg);
echo "<div style=\"background-image:url('" . $bg_safe . "')\">x</div>";

// 6. Data Attributes
$data_info = $_REQUEST['info'];
echo "<div data-info='" . esc_attr($data_info) . "'>data</div>";

// 7. JSON/AJAX/REST
$ajax_payload = sanitize_text_field($_POST['ajax_payload']);
wp_send_json_success($ajax_payload);

$json_out = sanitize_text_field($_GET['json_out']);
echo wp_json_encode(['msg' => $json_out]);

add_action('rest_api_init', function () {
    register_rest_route('xss/v1', '/echo', array(
        'methods' => 'POST',
        'callback' => function ($request) {
            $v = sanitize_text_field($request->get_param('v'));
            return new WP_REST_Response(['v' => $v], 200);
        }
    ));
});

// 8. HTTP Headers
$hdr = sanitize_text_field($_GET['hdr']);
header('X-Debug: ' . $hdr);

// 9. Mixed Flows (arrays, objects, function params, class props)
$arr = $_POST['arr'];
$val = isset($arr['key']) ? esc_html($arr['key']) : '';
echo "<p>" . $val . "</p>";

class XssDisplaySafe {
    public $content;
    public function render() {
        echo "<div>" . esc_html($this->content) . "</div>";
    }
}
$xds = new XssDisplaySafe();
$xds->content = isset($_REQUEST['content']) ? $_REQUEST['content'] : '';
$xds->render();

function echo_safely($x) {
    echo esc_html($x);
}
echo_safely(isset($_GET['p']) ? $_GET['p'] : '');

// 10. Template Injection via Heredoc / Script
$heredoc = esc_js(isset($_POST['heredoc']) ? $_POST['heredoc'] : '');
echo <<<HTML
<script>
    const payload = "$heredoc";
    console.log(payload);
</script>
HTML;


