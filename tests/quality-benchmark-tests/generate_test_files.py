#!/usr/bin/env python3
"""
Generate 100 test files for quality benchmarking with varying security vulnerabilities.
"""

import os
import random

def create_safe_file(filename, description):
    """Create a safe file with no security vulnerabilities."""
    content = f"""<?php
/**
 * {description}
 * Status: Safe (No vulnerabilities)
 * Expected Findings: 0
 */

// Prevent direct access
if (!defined('ABSPATH')) {{
    exit;
}}

// Basic WordPress functions with proper security practices
function {filename.replace('.php', '').replace('-', '_')}_safe_function() {{
    // Proper capability check
    if (!current_user_can('manage_options')) {{
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }}
    
    // Safe output with proper escaping
    $safe_data = sanitize_text_field($_POST['data'] ?? '');
    echo esc_html($safe_data);
    
    // Safe database query
    global $wpdb;
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {{$wpdb->prefix}}posts WHERE post_status = %s",
            'publish'
        )
    );
    
    // Safe file operations
    $upload_dir = wp_upload_dir();
    $safe_path = realpath($upload_dir['basedir']);
    
    // Safe redirect
    $redirect_url = esc_url_raw($_GET['redirect'] ?? '');
    if ($redirect_url) {{
        wp_redirect($redirect_url);
        exit;
    }}
    
    return true;
}}

// Hook registration with proper priority
add_action('init', '{filename.replace('.php', '').replace('-', '_')}_safe_function', 10);

// Safe AJAX handler
add_action('wp_ajax_safe_action', function() {{
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'safe_action_nonce')) {{
        wp_die('Invalid nonce');
    }}
    
    // Process safely
    $result = sanitize_text_field($_POST['data']);
    wp_send_json_success($result);
}});
"""
    return content

def create_single_vulnerability_file(filename, vuln_type, description):
    """Create a file with exactly one security vulnerability."""
    vulnerabilities = {
        'xss': f"""<?php
/**
 * {description}
 * Status: Vulnerable (XSS)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {{
    exit;
}}

function {filename.replace('.php', '').replace('-', '_')}_vulnerable_function() {{
    // VULNERABILITY: XSS - Direct output without escaping
    $user_input = $_GET['user_input'] ?? '';
    echo $user_input; // VULNERABLE: Should use esc_html()
    
    // Safe operations for contrast
    $safe_data = sanitize_text_field($_POST['safe_data'] ?? '');
    echo esc_html($safe_data);
    
    return true;
}}

add_action('init', '{filename.replace('.php', '').replace('-', '_')}_vulnerable_function');
""",
        
        'sqli': f"""<?php
/**
 * {description}
 * Status: Vulnerable (SQL Injection)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {{
    exit;
}}

function {filename.replace('.php', '').replace('-', '_')}_vulnerable_function() {{
    global $wpdb;
    
    // VULNERABILITY: SQL Injection - Direct concatenation
    $user_id = $_GET['user_id'] ?? '';
    $query = "SELECT * FROM {{$wpdb->prefix}}users WHERE ID = " . $user_id; // VULNERABLE
    $results = $wpdb->get_results($query);
    
    // Safe operations for contrast
    $safe_user_id = intval($_GET['safe_user_id'] ?? 0);
    $safe_results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {{$wpdb->prefix}}users WHERE ID = %d", $safe_user_id)
    );
    
    return $results;
}}

add_action('init', '{filename.replace('.php', '').replace('-', '_')}_vulnerable_function');
""",
        
        'csrf': f"""<?php
/**
 * {description}
 * Status: Vulnerable (CSRF)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {{
    exit;
}}

function {filename.replace('.php', '').replace('-', '_')}_vulnerable_function() {{
    // VULNERABILITY: CSRF - No nonce verification
    if (isset($_POST['delete_post'])) {{
        $post_id = intval($_POST['post_id']);
        wp_delete_post($post_id); // VULNERABLE: No nonce check
    }}
    
    // Safe operations for contrast
    if (isset($_POST['safe_action']) && wp_verify_nonce($_POST['nonce'], 'safe_action_nonce')) {{
        $safe_post_id = intval($_POST['safe_post_id']);
        wp_delete_post($safe_post_id);
    }}
    
    return true;
}}

add_action('init', '{filename.replace('.php', '').replace('-', '_')}_vulnerable_function');
""",
        
        'authz': f"""<?php
/**
 * {description}
 * Status: Vulnerable (Authorization)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {{
    exit;
}}

function {filename.replace('.php', '').replace('-', '_')}_vulnerable_function() {{
    // VULNERABILITY: Authorization - No capability check
    if (isset($_POST['admin_action'])) {{
        // VULNERABLE: No capability check before admin action
        update_option('admin_setting', $_POST['admin_setting']);
    }}
    
    // Safe operations for contrast
    if (isset($_POST['safe_admin_action']) && current_user_can('manage_options')) {{
        $safe_setting = sanitize_text_field($_POST['safe_admin_setting']);
        update_option('safe_admin_setting', $safe_setting);
    }}
    
    return true;
}}

add_action('init', '{filename.replace('.php', '').replace('-', '_')}_vulnerable_function');
""",
        
        'file_upload': f"""<?php
/**
 * {description}
 * Status: Vulnerable (File Upload)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {{
    exit;
}}

function {filename.replace('.php', '').replace('-', '_')}_vulnerable_function() {{
    // VULNERABILITY: File Upload - No validation
    if (isset($_FILES['upload_file'])) {{
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/' . $_FILES['upload_file']['name'];
        move_uploaded_file($_FILES['upload_file']['tmp_name'], $file_path); // VULNERABLE: No validation
    }}
    
    // Safe operations for contrast
    if (isset($_FILES['safe_upload_file'])) {{
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_info = wp_check_filetype($_FILES['safe_upload_file']['name']);
        if (in_array($file_info['ext'], $allowed_types)) {{
            $safe_file_path = $upload_dir['basedir'] . '/' . sanitize_file_name($_FILES['safe_upload_file']['name']);
            move_uploaded_file($_FILES['safe_upload_file']['tmp_name'], $safe_file_path);
        }}
    }}
    
    return true;
}}

add_action('init', '{filename.replace('.php', '').replace('-', '_')}_vulnerable_function');
""",
        
        'deserialization': f"""<?php
/**
 * {description}
 * Status: Vulnerable (Deserialization)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {{
    exit;
}}

function {filename.replace('.php', '').replace('-', '_')}_vulnerable_function() {{
    // VULNERABILITY: Deserialization - Unsafe unserialize
    $serialized_data = $_POST['serialized_data'] ?? '';
    $data = unserialize($serialized_data); // VULNERABLE: Should validate input
    
    // Safe operations for contrast
    $safe_data = maybe_unserialize($_POST['safe_data'] ?? '');
    if (is_array($safe_data)) {{
        $safe_data = array_map('sanitize_text_field', $safe_data);
    }}
    
    return $data;
}}

add_action('init', '{filename.replace('.php', '').replace('-', '_')}_vulnerable_function');
""",
        
        'secrets': f"""<?php
/**
 * {description}
 * Status: Vulnerable (Secrets Storage)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {{
    exit;
}}

function {filename.replace('.php', '').replace('-', '_')}_vulnerable_function() {{
    // VULNERABILITY: Secrets Storage - API key in options
    $api_key = $_POST['api_key'] ?? '';
    update_option('my_plugin_api_key', $api_key); // VULNERABLE: Storing sensitive data in options
    
    // Safe operations for contrast
    $safe_setting = sanitize_text_field($_POST['safe_setting'] ?? '');
    update_option('my_plugin_safe_setting', $safe_setting);
    
    return true;
}}

add_action('init', '{filename.replace('.php', '').replace('-', '_')}_vulnerable_function');
""",
        
        'rest_ajax': f"""<?php
/**
 * {description}
 * Status: Vulnerable (REST/AJAX Security)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {{
    exit;
}}

function {filename.replace('.php', '').replace('-', '_')}_vulnerable_function() {{
    // VULNERABILITY: REST endpoint without permissions
    add_action('rest_api_init', function() {{
        register_rest_route('my-plugin/v1', '/data', [
            'methods' => 'POST',
            'callback' => function($request) {{
                return rest_ensure_response(['status' => 'success']);
            }},
            // VULNERABLE: No permission_callback
        ]);
    }});
    
    // Safe operations for contrast
    add_action('rest_api_init', function() {{
        register_rest_route('my-plugin/v1', '/safe-data', [
            'methods' => 'POST',
            'callback' => function($request) {{
                return rest_ensure_response(['status' => 'success']);
            }},
            'permission_callback' => function() {{
                return current_user_can('edit_posts');
            }}
        ]);
    }});
    
    return true;
}}

add_action('init', '{filename.replace('.php', '').replace('-', '_')}_vulnerable_function');
""",
        
        'path_traversal': f"""<?php
/**
 * {description}
 * Status: Vulnerable (Path Traversal)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {{
    exit;
}}

function {filename.replace('.php', '').replace('-', '_')}_vulnerable_function() {{
    // VULNERABILITY: Path Traversal - Direct file access
    $file_path = $_GET['file'] ?? '';
    $full_path = ABSPATH . $file_path; // VULNERABLE: Path traversal possible
    $content = file_get_contents($full_path);
    
    // Safe operations for contrast
    $safe_file = sanitize_file_name($_GET['safe_file'] ?? '');
    $safe_full_path = ABSPATH . 'wp-content/uploads/' . $safe_file;
    if (strpos(realpath($safe_full_path), ABSPATH . 'wp-content/uploads/') === 0) {{
        $safe_content = file_get_contents($safe_full_path);
    }}
    
    return $content;
}}

add_action('init', '{filename.replace('.php', '').replace('-', '_')}_vulnerable_function');
""",
        
        'eval': f"""<?php
/**
 * {description}
 * Status: Vulnerable (Dynamic Execution)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {{
    exit;
}}

function {filename.replace('.php', '').replace('-', '_')}_vulnerable_function() {{
    // VULNERABILITY: Dynamic Execution - eval usage
    $code = $_POST['code'] ?? '';
    eval($code); // VULNERABLE: Should never use eval
    
    // Safe operations for contrast
    $safe_code = sanitize_text_field($_POST['safe_code'] ?? '');
    // Process safely without eval
    
    return true;
}}

add_action('init', '{filename.replace('.php', '').replace('-', '_')}_vulnerable_function');
"""
    }
    
    return vulnerabilities.get(vuln_type, vulnerabilities['xss'])

def create_multiple_vulnerability_file(filename, vuln_types, description):
    """Create a file with multiple security vulnerabilities."""
    vuln_code = []
    safe_code = []
    
    for vuln_type in vuln_types:
        if vuln_type == 'xss':
            vuln_code.append("    // VULNERABILITY: XSS - Direct output without escaping")
            vuln_code.append("    echo $_GET['user_input']; // VULNERABLE")
        elif vuln_type == 'sqli':
            vuln_code.append("    // VULNERABILITY: SQL Injection - Direct concatenation")
            vuln_code.append("    $query = \"SELECT * FROM {$wpdb->prefix}users WHERE ID = \" . $_GET['user_id']; // VULNERABLE")
            vuln_code.append("    $results = $wpdb->get_results($query);")
        elif vuln_type == 'csrf':
            vuln_code.append("    // VULNERABILITY: CSRF - No nonce verification")
            vuln_code.append("    if (isset($_POST['action'])) { wp_delete_post($_POST['post_id']); } // VULNERABLE")
        elif vuln_type == 'authz':
            vuln_code.append("    // VULNERABILITY: Authorization - No capability check")
            vuln_code.append("    update_option('admin_setting', $_POST['setting']); // VULNERABLE")
        elif vuln_type == 'file_upload':
            vuln_code.append("    // VULNERABILITY: File Upload - No validation")
            vuln_code.append("    move_uploaded_file($_FILES['file']['tmp_name'], ABSPATH . $_FILES['file']['name']); // VULNERABLE")
        elif vuln_type == 'deserialization':
            vuln_code.append("    // VULNERABILITY: Deserialization - Unsafe unserialize")
            vuln_code.append("    $data = unserialize($_POST['data']); // VULNERABLE")
        elif vuln_type == 'secrets':
            vuln_code.append("    // VULNERABILITY: Secrets Storage - API key in options")
            vuln_code.append("    update_option('api_key', $_POST['api_key']); // VULNERABLE")
        elif vuln_type == 'rest_ajax':
            vuln_code.append("    // VULNERABILITY: REST endpoint without permissions")
            vuln_code.append("    register_rest_route('v1', '/data', ['methods' => 'POST', 'callback' => 'process_data']); // VULNERABLE")
        elif vuln_type == 'path_traversal':
            vuln_code.append("    // VULNERABILITY: Path Traversal - Direct file access")
            vuln_code.append("    $content = file_get_contents(ABSPATH . $_GET['file']); // VULNERABLE")
        elif vuln_type == 'eval':
            vuln_code.append("    // VULNERABILITY: Dynamic Execution - eval usage")
            vuln_code.append("    eval($_POST['code']); // VULNERABLE")
    
    # Add safe operations for contrast
    safe_code.extend([
        "    // Safe operations for contrast",
        "    $safe_input = sanitize_text_field($_POST['safe_input'] ?? '');",
        "    echo esc_html($safe_input);",
        "    $safe_results = $wpdb->get_results($wpdb->prepare('SELECT * FROM {$wpdb->prefix}users WHERE ID = %d', intval($_GET['safe_id'])));",
        "    if (wp_verify_nonce($_POST['safe_nonce'], 'safe_action') && current_user_can('manage_options')) {",
        "        update_option('safe_setting', sanitize_text_field($_POST['safe_setting']));",
        "    }"
    ])
    
    content = f"""<?php
/**
 * {description}
 * Status: Vulnerable ({', '.join(vuln_types)})
 * Expected Findings: {len(vuln_types)}
 */

// Prevent direct access
if (!defined('ABSPATH')) {{
    exit;
}}

function {filename.replace('.php', '').replace('-', '_')}_vulnerable_function() {{
    global $wpdb;
    
{chr(10).join(vuln_code)}
    
{chr(10).join(safe_code)}
    
    return true;
}}

add_action('init', '{filename.replace('.php', '').replace('-', '_')}_vulnerable_function');
"""
    return content

def create_complex_vulnerability_file(filename, vuln_count, description):
    """Create a file with complex security vulnerabilities."""
    vuln_code = []
    
    # Generate multiple instances of each vulnerability type
    for i in range(vuln_count):
        vuln_type = i % 5  # Cycle through different types
        
        if vuln_type == 0:  # XSS variants
            vuln_code.extend([
                f"    // VULNERABILITY {i+1}: XSS variant {i+1}",
                f"    echo $_GET['input_{i}']; // VULNERABLE",
                f"    echo '<div>' . $_POST['data_{i}'] . '</div>'; // VULNERABLE",
                f"    echo \"<script>var data = '\" . $_REQUEST['var_{i}'] . \"';</script>\"; // VULNERABLE"
            ])
        elif vuln_type == 1:  # SQL Injection variants
            vuln_code.extend([
                f"    // VULNERABILITY {i+1}: SQL Injection variant {i+1}",
                f"    $query_{i} = \"SELECT * FROM users WHERE id = \" . $_GET['id_{i}']; // VULNERABLE",
                f"    $wpdb->query($query_{i}); // VULNERABLE",
                f"    $results_{i} = $wpdb->get_results(\"SELECT * FROM posts WHERE title LIKE '%\" . $_POST['search_{i}'] . \"%'\"); // VULNERABLE"
            ])
        elif vuln_type == 2:  # CSRF variants
            vuln_code.extend([
                f"    // VULNERABILITY {i+1}: CSRF variant {i+1}",
                f"    if (isset($_POST['action_{i}'])) {{ wp_delete_post($_POST['post_{i}']); }} // VULNERABLE",
                f"    if ($_POST['delete_{i}']) {{ wp_delete_user($_POST['user_{i}']); }} // VULNERABLE"
            ])
        elif vuln_type == 3:  # Authorization variants
            vuln_code.extend([
                f"    // VULNERABILITY {i+1}: Authorization variant {i+1}",
                f"    update_option('setting_{i}', $_POST['value_{i}']); // VULNERABLE",
                f"    wp_delete_post($_GET['post_{i}']); // VULNERABLE"
            ])
        elif vuln_type == 4:  # File Upload variants
            vuln_code.extend([
                f"    // VULNERABILITY {i+1}: File Upload variant {i+1}",
                f"    move_uploaded_file($_FILES['file_{i}']['tmp_name'], ABSPATH . $_FILES['file_{i}']['name']); // VULNERABLE",
                f"    copy($_FILES['upload_{i}']['tmp_name'], $_POST['path_{i}'] . $_FILES['upload_{i}']['name']); // VULNERABLE"
            ])
    
    content = f"""<?php
/**
 * {description}
 * Status: Vulnerable (Complex vulnerabilities)
 * Expected Findings: {vuln_count}
 */

// Prevent direct access
if (!defined('ABSPATH')) {{
    exit;
}}

function {filename.replace('.php', '').replace('-', '_')}_complex_vulnerable_function() {{
    global $wpdb;
    
{chr(10).join(vuln_code)}
    
    // Safe operations for contrast
    $safe_input = sanitize_text_field($_POST['safe_input'] ?? '');
    echo esc_html($safe_input);
    
    return true;
}}

add_action('init', '{filename.replace('.php', '').replace('-', '_')}_complex_vulnerable_function');
"""
    return content

def main():
    """Generate all 100 test files."""
    test_dir = os.path.dirname(os.path.abspath(__file__))
    
    # File definitions with expected findings
    files = [
        # Safe files (0 findings)
        ('safe-basic-functions.php', 'safe', 0, 'Basic WordPress functions with proper security practices'),
        ('safe-ajax-handler.php', 'safe', 0, 'AJAX handler with proper nonce verification'),
        ('safe-form-processing.php', 'safe', 0, 'Form processing with proper sanitization'),
        ('safe-database-query.php', 'safe', 0, 'Database queries using prepared statements'),
        ('safe-file-upload.php', 'safe', 0, 'File upload with proper validation'),
        ('safe-rest-endpoint.php', 'safe', 0, 'REST endpoint with proper permissions'),
        ('safe-capability-check.php', 'safe', 0, 'Capability checks before sensitive operations'),
        ('safe-option-storage.php', 'safe', 0, 'Safe option storage without sensitive data'),
        ('safe-meta-handling.php', 'safe', 0, 'Meta data handling with proper sanitization'),
        ('safe-redirect.php', 'safe', 0, 'Safe redirects with validation'),
        ('safe-email-sending.php', 'safe', 0, 'Email sending with proper headers'),
        ('safe-cron-handler.php', 'safe', 0, 'Cron job handler with security checks'),
        ('safe-widget-render.php', 'safe', 0, 'Widget rendering with escaping'),
        ('safe-shortcode.php', 'safe', 0, 'Shortcode with proper output escaping'),
        ('safe-admin-page.php', 'safe', 0, 'Admin page with capability checks'),
        ('safe-ajax-callback.php', 'safe', 0, 'AJAX callback with nonce verification'),
        ('safe-hook-handler.php', 'safe', 0, 'Hook handler with proper validation'),
        ('safe-template-file.php', 'safe', 0, 'Template file with escaping'),
        ('safe-plugin-activation.php', 'safe', 0, 'Plugin activation with checks'),
        ('safe-plugin-deactivation.php', 'safe', 0, 'Plugin deactivation with cleanup'),
        
        # Single vulnerability files (1 finding each)
        ('xss-basic-output.php', 'xss', 1, 'Basic XSS vulnerability in echo'),
        ('sqli-basic-query.php', 'sqli', 1, 'SQL injection in raw query'),
        ('csrf-no-nonce.php', 'csrf', 1, 'Form without nonce verification'),
        ('authz-no-capability.php', 'authz', 1, 'Admin function without capability check'),
        ('file-upload-no-validation.php', 'file_upload', 1, 'File upload without validation'),
        ('deserialization-unsafe.php', 'deserialization', 1, 'Unsafe unserialize usage'),
        ('secrets-in-options.php', 'secrets', 1, 'API key stored in options'),
        ('rest-no-permissions.php', 'rest_ajax', 1, 'REST endpoint without permissions'),
        ('ajax-no-nonce.php', 'rest_ajax', 1, 'AJAX handler without nonce'),
        ('path-traversal-basic.php', 'path_traversal', 1, 'Basic path traversal vulnerability'),
        ('eval-usage.php', 'eval', 1, 'Usage of eval() function'),
        ('create-function-usage.php', 'eval', 1, 'Usage of create_function()'),
        ('xss-attribute-context.php', 'xss', 1, 'XSS in HTML attribute context'),
        ('xss-javascript-context.php', 'xss', 1, 'XSS in JavaScript context'),
        ('xss-url-context.php', 'xss', 1, 'XSS in URL context'),
        ('sqli-concatenation.php', 'sqli', 1, 'SQL injection via string concatenation'),
        ('sqli-wpdb-prepare-misuse.php', 'sqli', 1, 'Misuse of $wpdb->prepare'),
        ('csrf-admin-post.php', 'csrf', 1, 'admin-post.php without nonce'),
        ('authz-rest-permission.php', 'authz', 1, 'REST endpoint without permission_callback'),
        ('file-upload-extension-bypass.php', 'file_upload', 1, 'File upload with extension bypass'),
        ('secrets-in-meta.php', 'secrets', 1, 'Secrets stored in post meta'),
        ('xss-reflected.php', 'xss', 1, 'Reflected XSS vulnerability'),
        ('xss-stored.php', 'xss', 1, 'Stored XSS vulnerability'),
        ('sqli-union.php', 'sqli', 1, 'SQL injection with UNION'),
        ('csrf-ajax.php', 'csrf', 1, 'AJAX without CSRF protection'),
        
        # Multiple vulnerability files (2-4 findings)
        ('xss-sqli-combo.php', ['xss', 'sqli'], 2, 'Combined XSS and SQL injection'),
        ('authz-csrf-combo.php', ['authz', 'csrf'], 2, 'Combined authorization and CSRF issues'),
        ('file-upload-xss-combo.php', ['file_upload', 'xss'], 2, 'File upload with XSS in filename'),
        ('rest-ajax-combo.php', ['rest_ajax', 'rest_ajax'], 2, 'REST and AJAX without security'),
        ('secrets-deserialization-combo.php', ['secrets', 'deserialization'], 2, 'Secrets with unsafe deserialization'),
        ('xss-authz-file-combo.php', ['xss', 'authz', 'file_upload'], 3, 'Three security issues combined'),
        ('sqli-csrf-rest-combo.php', ['sqli', 'csrf', 'rest_ajax'], 3, 'Three security issues combined'),
        ('path-traversal-xss-authz-combo.php', ['path_traversal', 'xss', 'authz'], 3, 'Three security issues combined'),
        ('eval-deserialization-secrets-combo.php', ['eval', 'deserialization', 'secrets'], 3, 'Three security issues combined'),
        ('ajax-nonce-csrf-authz-combo.php', ['rest_ajax', 'csrf', 'authz'], 3, 'Three security issues combined'),
        ('xss-sqli-csrf-authz-combo.php', ['xss', 'sqli', 'csrf', 'authz'], 4, 'Four security issues combined'),
        ('file-upload-path-traversal-xss-authz-combo.php', ['file_upload', 'path_traversal', 'xss', 'authz'], 4, 'Four security issues combined'),
        ('rest-ajax-csrf-authz-secrets-combo.php', ['rest_ajax', 'rest_ajax', 'csrf', 'authz', 'secrets'], 4, 'Four security issues combined'),
        ('eval-deserialization-path-traversal-xss-combo.php', ['eval', 'deserialization', 'path_traversal', 'xss'], 4, 'Four security issues combined'),
        ('sqli-csrf-authz-file-upload-combo.php', ['sqli', 'csrf', 'authz', 'file_upload'], 4, 'Four security issues combined'),
        
        # Complex vulnerability files (5+ findings)
        ('complex-xss-chain.php', 'complex', 5, 'Complex XSS with multiple contexts'),
        ('complex-sqli-chain.php', 'complex', 5, 'Complex SQL injection patterns'),
        ('complex-authz-chain.php', 'complex', 5, 'Complex authorization bypasses'),
        ('complex-file-upload-chain.php', 'complex', 5, 'Complex file upload bypasses'),
        ('complex-csrf-chain.php', 'complex', 5, 'Complex CSRF vulnerabilities'),
        ('subtle-xss-obfuscation.php', 'complex', 3, 'XSS with JavaScript obfuscation'),
        ('subtle-sqli-encoding.php', 'complex', 3, 'SQL injection with encoding bypasses'),
        ('subtle-authz-bypass.php', 'complex', 3, 'Subtle authorization bypasses'),
        ('subtle-file-upload-bypass.php', 'complex', 3, 'Subtle file upload bypasses'),
        ('subtle-csrf-bypass.php', 'complex', 3, 'Subtle CSRF bypasses'),
        ('advanced-obfuscation-xss.php', 'complex', 4, 'XSS with advanced JavaScript obfuscation'),
        ('advanced-obfuscation-sqli.php', 'complex', 4, 'SQL injection with advanced encoding'),
        ('advanced-obfuscation-authz.php', 'complex', 4, 'Authorization bypass with advanced techniques'),
        ('advanced-obfuscation-file-upload.php', 'complex', 4, 'File upload bypass with advanced techniques'),
        ('advanced-obfuscation-csrf.php', 'complex', 4, 'CSRF bypass with advanced techniques'),
        ('cross-file-vulnerability-1.php', 'complex', 3, 'Vulnerabilities spanning multiple files'),
        ('cross-file-vulnerability-2.php', 'complex', 3, 'Vulnerabilities spanning multiple files'),
        ('cross-file-vulnerability-3.php', 'complex', 3, 'Vulnerabilities spanning multiple files'),
        ('cross-file-vulnerability-4.php', 'complex', 3, 'Vulnerabilities spanning multiple files'),
        ('cross-file-vulnerability-5.php', 'complex', 3, 'Vulnerabilities spanning multiple files'),
        ('mega-vulnerability-1.php', 'complex', 6, 'Multiple complex vulnerabilities'),
        ('mega-vulnerability-2.php', 'complex', 6, 'Multiple complex vulnerabilities'),
        ('mega-vulnerability-3.php', 'complex', 6, 'Multiple complex vulnerabilities'),
        ('mega-vulnerability-4.php', 'complex', 6, 'Multiple complex vulnerabilities'),
        ('mega-vulnerability-5.php', 'complex', 6, 'Multiple complex vulnerabilities'),
        ('ultra-complex-1.php', 'complex', 7, 'Ultra complex vulnerability patterns'),
        ('ultra-complex-2.php', 'complex', 7, 'Ultra complex vulnerability patterns'),
        ('ultra-complex-3.php', 'complex', 7, 'Ultra complex vulnerability patterns'),
        ('ultra-complex-4.php', 'complex', 7, 'Ultra complex vulnerability patterns'),
        ('ultra-complex-5.php', 'complex', 7, 'Ultra complex vulnerability patterns'),
        ('edge-case-vulnerability-1.php', 'complex', 4, 'Edge case vulnerability patterns'),
        ('edge-case-vulnerability-2.php', 'complex', 4, 'Edge case vulnerability patterns'),
        ('edge-case-vulnerability-3.php', 'complex', 4, 'Edge case vulnerability patterns'),
        ('edge-case-vulnerability-4.php', 'complex', 4, 'Edge case vulnerability patterns'),
        ('edge-case-vulnerability-5.php', 'complex', 4, 'Edge case vulnerability patterns'),
        ('performance-test-large-1.php', 'complex', 5, 'Large file with multiple vulnerabilities for performance testing'),
        ('performance-test-large-2.php', 'complex', 5, 'Large file with multiple vulnerabilities for performance testing'),
        ('performance-test-large-3.php', 'complex', 5, 'Large file with multiple vulnerabilities for performance testing'),
        ('performance-test-large-4.php', 'complex', 5, 'Large file with multiple vulnerabilities for performance testing'),
        ('performance-test-large-5.php', 'complex', 5, 'Large file with multiple vulnerabilities for performance testing')
    ]
    
    print(f"Generating {len(files)} test files in {test_dir}...")
    
    for i, (filename, vuln_type, expected_findings, description) in enumerate(files, 1):
        filepath = os.path.join(test_dir, filename)
        
        if vuln_type == 'safe':
            content = create_safe_file(filename, description)
        elif isinstance(vuln_type, list):
            content = create_multiple_vulnerability_file(filename, vuln_type, description)
        elif isinstance(vuln_type, str) and expected_findings == 1:
            content = create_single_vulnerability_file(filename, vuln_type, description)
        else:
            content = create_complex_vulnerability_file(filename, expected_findings, description)
        
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        
        print(f"Generated {i:3d}/100: {filename} (Expected: {expected_findings} findings)")
    
    print(f"\nAll {len(files)} test files generated successfully!")
    print(f"Total expected findings: {sum(f[2] for f in files)}")
    
    # Create a summary file
    summary_file = os.path.join(test_dir, 'TEST_SUMMARY.md')
    with open(summary_file, 'w', encoding='utf-8') as f:
        f.write("# Test Files Summary\n\n")
        f.write(f"Total files generated: {len(files)}\n")
        f.write(f"Total expected findings: {sum(f[2] for f in files)}\n\n")
        
        f.write("## File Breakdown\n\n")
        f.write("| File | Type | Expected Findings | Description |\n")
        f.write("|------|------|------------------|-------------|\n")
        
        for filename, vuln_type, expected_findings, description in files:
            vuln_str = ', '.join(vuln_type) if isinstance(vuln_type, list) else vuln_type
            f.write(f"| {filename} | {vuln_str} | {expected_findings} | {description} |\n")
    
    print(f"Summary written to: {summary_file}")

if __name__ == '__main__':
    main()
