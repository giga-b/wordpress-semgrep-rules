<?php
// XSS Context Matrix - Vulnerable Examples
// These patterns should trigger context-aware XSS rules.

// Minimal WordPress function stubs for linter/static analysis in isolated test files
if (!function_exists('wp_send_json_success')) {
	function wp_send_json_success($data) { echo json_encode(['success' => true, 'data' => $data]); }
}
if (!function_exists('wp_json_encode')) {
	function wp_json_encode($data) { return json_encode($data); }
}
if (!function_exists('add_action')) {
	function add_action($hook, $callback) { /* no-op in tests */ }
}
if (!function_exists('register_rest_route')) {
	function register_rest_route($namespace, $route, $args) { /* no-op in tests */ }
}
if (!class_exists('WP_REST_Response')) {
	class WP_REST_Response {
		public $data;
		public $status;
		public function __construct($data, $status = 200) { $this->data = $data; $this->status = $status; }
	}
}

// 1. HTML Content Context
$html_get = $_GET['html'];
echo $html_get; // vulnerable

$html_post = $_POST['html'];
echo "<div>" . $html_post . "</div>"; // vulnerable

// 2. HTML Attribute Context (quoted and unquoted)
$attr_val = $_GET['val'];
echo "<input value='" . $attr_val . "'>"; // vulnerable

$attr_unquoted = $_POST['q'];
echo "<input value=" . $attr_unquoted . ">"; // vulnerable unquoted attribute

$class_name = $_REQUEST['cls'];
echo "<span class='" . $class_name . "'>Item</span>"; // vulnerable

// 3. JavaScript Context (inline script and event handlers)
$js_var = $_GET['js'];
echo "<script>var msg = '" . $js_var . "';</script>"; // vulnerable

$js_event = $_POST['onclick'];
echo "<button onclick='" . $js_event . "'>Click</button>"; // vulnerable

// 4. URL/HREF/SRC Context
$href = $_GET['href'];
echo "<a href='" . $href . "'>link</a>"; // vulnerable

$src = $_POST['src'];
echo "<img src='" . $src . "'>"; // vulnerable

// 5. CSS Inline Style Context
$color = $_GET['color'];
echo "<div style='color: " . $color . "'>text</div>"; // vulnerable

$bg = $_POST['bg'];
echo "<div style=background-image:url(" . $bg . ")>x</div>"; // vulnerable

// 6. Data Attributes
$data_info = $_REQUEST['info'];
echo "<div data-info='" . $data_info . "'>data</div>"; // vulnerable

// 7. JSON/AJAX/REST
$ajax_payload = $_POST['ajax_payload'];
wp_send_json_success($ajax_payload); // vulnerable

$json_out = $_GET['json_out'];
echo wp_json_encode(['msg' => $json_out]); // vulnerable

add_action('rest_api_init', function () {
    register_rest_route('xss/v1', '/echo', array(
        'methods' => 'POST',
        'callback' => function ($request) {
            $v = $request->get_param('v');
            return new WP_REST_Response(['v' => $v], 200); // vulnerable
        }
    ));
});

// 8. HTTP Headers
$hdr = $_GET['hdr'];
header('X-Debug: ' . $hdr); // vulnerable (header injection / reflection)

// 9. Mixed Flows (arrays, objects, function params, class props)
$arr = $_POST['arr'];
$val = $arr['key'];
echo "<p>" . $val . "</p>"; // vulnerable

class XssDisplay {
    public $content;
    public function render() {
        echo "<div>" . $this->content . "</div>"; // vulnerable
    }
}
$xd = new XssDisplay();
$xd->content = $_REQUEST['content'];
$xd->render();

function echo_raw($x) {
    echo $x; // vulnerable
}
echo_raw($_GET['p']);

// 10. Template Injection via Heredoc / Script
$heredoc = $_POST['heredoc'];
echo <<<HTML
<script>
    const payload = "$heredoc";
    console.log(payload);
</script>
HTML;


