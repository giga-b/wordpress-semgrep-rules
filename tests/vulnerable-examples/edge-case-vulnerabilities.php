<?php
// Edge Case Security Vulnerabilities - Test Cases
// This file contains sophisticated edge cases and complex attack patterns

// 1. Complex Taint Flow Patterns
function complex_taint_flow_1() {
    $input = $_GET['data'];
    $temp1 = $input;
    $temp2 = $temp1;
    $temp3 = $temp2;
    $final = $temp3;
    echo $final; // XSS via complex taint flow
}

function complex_taint_flow_2() {
    $user_data = $_POST['user_data'];
    $processed = array_map('trim', explode(',', $user_data));
    $filtered = array_filter($processed, function($item) {
        return strlen($item) > 0;
    });
    $result = implode('', $filtered);
    echo $result; // XSS via array processing
}

// 2. Chained Vulnerabilities
function chained_sql_xss() {
    global $wpdb;
    $search = $_GET['search'];
    $query = "SELECT * FROM {$wpdb->posts} WHERE title LIKE '%$search%'";
    $results = $wpdb->get_results($query); // SQL injection
    
    foreach ($results as $result) {
        echo "<div>" . $result->title . "</div>"; // XSS from SQL injection
    }
}

function chained_file_include() {
    $file = $_GET['file'];
    $path = '/var/www/html/' . $file;
    include($path); // Path traversal + file inclusion
}

// 3. Conditional Vulnerabilities
function conditional_xss() {
    $user_input = $_POST['data'];
    $is_admin = current_user_can('manage_options');
    
    if ($is_admin) {
        echo $user_input; // XSS only for admins (still vulnerable)
    } else {
        echo esc_html($user_input);
    }
}

function conditional_sql_injection() {
    global $wpdb;
    $user_id = $_GET['id'];
    $is_authorized = wp_verify_nonce($_POST['nonce'], 'action');
    
    if ($is_authorized) {
        $query = "SELECT * FROM {$wpdb->users} WHERE ID = $user_id";
        return $wpdb->get_results($query); // SQL injection only if authorized
    }
}

// 4. Recursive Vulnerabilities
function recursive_xss($data, $depth = 0) {
    if ($depth > 3) return;
    
    if (is_array($data)) {
        foreach ($data as $item) {
            recursive_xss($item, $depth + 1);
        }
    } else {
        echo $data; // XSS in recursive function
    }
}

// 5. Callback Vulnerabilities
function callback_xss() {
    $user_data = $_POST['data'];
    $callback = function($data) {
        echo $data; // XSS in callback
    };
    $callback($user_data);
}

function callback_sql_injection() {
    global $wpdb;
    $user_input = $_GET['input'];
    $callback = function($input) use ($wpdb) {
        $query = "SELECT * FROM {$wpdb->posts} WHERE title = '$input'";
        return $wpdb->get_results($query); // SQL injection in callback
    };
    return $callback($user_input);
}

// 6. Object Property Vulnerabilities
function object_property_xss() {
    $user_data = $_POST['data'];
    $obj = new stdClass();
    $obj->content = $user_data;
    echo $obj->content; // XSS via object property
}

function object_property_sql() {
    global $wpdb;
    $user_input = $_GET['input'];
    $obj = new stdClass();
    $obj->query = "SELECT * FROM {$wpdb->posts} WHERE title = '$user_input'";
    return $wpdb->get_results($obj->query); // SQL injection via object property
}

// 7. Array Key Vulnerabilities
function array_key_xss() {
    $user_key = $_GET['key'];
    $data = ['safe' => 'safe data', 'unsafe' => $_POST['data']];
    echo $data[$user_key]; // XSS via array key manipulation
}

function array_key_sql() {
    global $wpdb;
    $user_key = $_GET['key'];
    $queries = [
        'safe' => "SELECT * FROM {$wpdb->posts} WHERE status = 'publish'",
        'unsafe' => "SELECT * FROM {$wpdb->posts} WHERE title = '{$_POST['title']}'"
    ];
    return $wpdb->get_results($queries[$user_key]); // SQL injection via array key
}

// 8. String Concatenation Vulnerabilities
function string_concat_xss() {
    $prefix = '<div class="user-data">';
    $user_data = $_POST['data'];
    $suffix = '</div>';
    echo $prefix . $user_data . $suffix; // XSS via string concatenation
}

function string_concat_sql() {
    global $wpdb;
    $base_query = "SELECT * FROM {$wpdb->posts} WHERE title LIKE '%";
    $user_input = $_GET['search'];
    $end_query = "%'";
    $full_query = $base_query . $user_input . $end_query;
    return $wpdb->get_results($full_query); // SQL injection via string concatenation
}

// 9. Variable Variable Vulnerabilities
function variable_variable_xss() {
    $user_var = $_GET['var'];
    $$user_var = $_POST['data'];
    echo $$user_var; // XSS via variable variables
}

function variable_variable_sql() {
    global $wpdb;
    $user_var = $_GET['var'];
    $$user_var = $_POST['data'];
    $query = "SELECT * FROM {$wpdb->posts} WHERE title = '$$user_var'";
    return $wpdb->get_results($query); // SQL injection via variable variables
}

// 10. Reflection Vulnerabilities
function reflection_xss() {
    $user_method = $_GET['method'];
    $user_data = $_POST['data'];
    
    if (method_exists($this, $user_method)) {
        $this->$user_method($user_data); // XSS via reflection
    }
}

function reflection_sql() {
    global $wpdb;
    $user_method = $_GET['method'];
    $user_input = $_POST['input'];
    
    if (method_exists($this, $user_method)) {
        return $this->$user_method($user_input); // SQL injection via reflection
    }
}

// 11. Serialization Vulnerabilities
function serialization_xss() {
    $user_data = $_POST['data'];
    $serialized = serialize(['content' => $user_data]);
    $unserialized = unserialize($serialized);
    echo $unserialized['content']; // XSS via serialization
}

function serialization_sql() {
    global $wpdb;
    $user_input = $_POST['input'];
    $serialized = serialize(['query' => "SELECT * FROM {$wpdb->posts} WHERE title = '$user_input'"]);
    $unserialized = unserialize($serialized);
    return $wpdb->get_results($unserialized['query']); // SQL injection via serialization
}

// 12. Encoding Bypass Vulnerabilities
function encoding_bypass_xss() {
    $user_input = $_GET['data'];
    $decoded = urldecode(base64_decode($user_input));
    $html_entities = html_entity_decode($decoded);
    echo $html_entities; // XSS via encoding bypass
}

function encoding_bypass_sql() {
    global $wpdb;
    $user_input = $_GET['input'];
    $decoded = urldecode(base64_decode($user_input));
    $query = "SELECT * FROM {$wpdb->posts} WHERE title = '$decoded'";
    return $wpdb->get_results($query); // SQL injection via encoding bypass
}

// 13. Context Switching Vulnerabilities
function context_switch_xss() {
    $user_data = $_POST['data'];
    $context = $_GET['context'];
    
    switch ($context) {
        case 'html':
            echo $user_data; // XSS in HTML context
            break;
        case 'js':
            echo "<script>var data = '$user_data';</script>"; // XSS in JS context
            break;
        case 'css':
            echo "<style>.user-data:before { content: '$user_data'; }</style>"; // XSS in CSS context
            break;
    }
}

// 14. State-Based Vulnerabilities
function state_based_xss() {
    static $user_data = null;
    
    if ($user_data === null) {
        $user_data = $_POST['data'];
    }
    
    echo $user_data; // XSS via static variable
}

function state_based_sql() {
    global $wpdb;
    static $user_input = null;
    
    if ($user_input === null) {
        $user_input = $_GET['input'];
    }
    
    $query = "SELECT * FROM {$wpdb->posts} WHERE title = '$user_input'";
    return $wpdb->get_results($query); // SQL injection via static variable
}

// 15. Closure Vulnerabilities
function closure_xss() {
    $user_data = $_POST['data'];
    $closure = function() use ($user_data) {
        echo $user_data; // XSS in closure
    };
    $closure();
}

function closure_sql() {
    global $wpdb;
    $user_input = $_GET['input'];
    $closure = function() use ($wpdb, $user_input) {
        $query = "SELECT * FROM {$wpdb->posts} WHERE title = '$user_input'";
        return $wpdb->get_results($query); // SQL injection in closure
    };
    return $closure();
}

// 16. Magic Method Vulnerabilities
class VulnerableClass {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function __toString() {
        return $this->data; // XSS via __toString
    }
    
    public function __get($name) {
        return $this->data; // XSS via __get
    }
}

function magic_method_xss() {
    $user_data = $_POST['data'];
    $obj = new VulnerableClass($user_data);
    echo $obj; // XSS via magic method
}

// 17. Exception Handling Vulnerabilities
function exception_xss() {
    try {
        $user_data = $_POST['data'];
        throw new Exception($user_data);
    } catch (Exception $e) {
        echo $e->getMessage(); // XSS via exception message
    }
}

function exception_sql() {
    global $wpdb;
    try {
        $user_input = $_GET['input'];
        $query = "SELECT * FROM {$wpdb->posts} WHERE title = '$user_input'";
        return $wpdb->get_results($query);
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

// 18. Recursive Deserialization
function recursive_deserialization() {
    $user_data = $_POST['data'];
    $decoded = base64_decode($user_data);
    $unserialized = unserialize($decoded);
    
    if (is_object($unserialized)) {
        $unserialized->process(); // Dangerous recursive deserialization
    }
}

// 19. Template Injection Edge Cases
function template_injection_edge() {
    $template = $_GET['template'];
    $data = $_POST['data'];
    
    // Complex template with multiple contexts
    $html = "<div class='{$template}'>";
    $html .= "<script>var data = '{$data}';</script>";
    $html .= "<style>.data:before { content: '{$data}'; }</style>";
    $html .= "<div class='content'>{$data}</div>";
    $html .= "</div>";
    
    echo $html; // Multiple XSS contexts
}

// 20. Advanced Evasion Techniques
function advanced_evasion() {
    $user_input = $_GET['data'];
    
    // Multiple encoding layers
    $encoded1 = base64_encode($user_input);
    $encoded2 = urlencode($encoded1);
    $encoded3 = bin2hex($encoded2);
    $decoded3 = hex2bin($encoded3);
    $decoded2 = urldecode($decoded3);
    $decoded1 = base64_decode($decoded2);
    
    echo $decoded1; // XSS via complex encoding chain
}

// Helper functions
function process_data($data) {
    return $data;
}

function some_risky_operation() {
    throw new Exception("Operation failed");
}
?>
