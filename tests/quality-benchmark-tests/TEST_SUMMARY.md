# Test Files Summary

Total files generated: 100
Total expected findings: 255

## File Breakdown

| File | Type | Expected Findings | Description |
|------|------|------------------|-------------|
| safe-basic-functions.php | safe | 0 | Basic WordPress functions with proper security practices |
| safe-ajax-handler.php | safe | 0 | AJAX handler with proper nonce verification |
| safe-form-processing.php | safe | 0 | Form processing with proper sanitization |
| safe-database-query.php | safe | 0 | Database queries using prepared statements |
| safe-file-upload.php | safe | 0 | File upload with proper validation |
| safe-rest-endpoint.php | safe | 0 | REST endpoint with proper permissions |
| safe-capability-check.php | safe | 0 | Capability checks before sensitive operations |
| safe-option-storage.php | safe | 0 | Safe option storage without sensitive data |
| safe-meta-handling.php | safe | 0 | Meta data handling with proper sanitization |
| safe-redirect.php | safe | 0 | Safe redirects with validation |
| safe-email-sending.php | safe | 0 | Email sending with proper headers |
| safe-cron-handler.php | safe | 0 | Cron job handler with security checks |
| safe-widget-render.php | safe | 0 | Widget rendering with escaping |
| safe-shortcode.php | safe | 0 | Shortcode with proper output escaping |
| safe-admin-page.php | safe | 0 | Admin page with capability checks |
| safe-ajax-callback.php | safe | 0 | AJAX callback with nonce verification |
| safe-hook-handler.php | safe | 0 | Hook handler with proper validation |
| safe-template-file.php | safe | 0 | Template file with escaping |
| safe-plugin-activation.php | safe | 0 | Plugin activation with checks |
| safe-plugin-deactivation.php | safe | 0 | Plugin deactivation with cleanup |
| xss-basic-output.php | xss | 1 | Basic XSS vulnerability in echo |
| sqli-basic-query.php | sqli | 1 | SQL injection in raw query |
| csrf-no-nonce.php | csrf | 1 | Form without nonce verification |
| authz-no-capability.php | authz | 1 | Admin function without capability check |
| file-upload-no-validation.php | file_upload | 1 | File upload without validation |
| deserialization-unsafe.php | deserialization | 1 | Unsafe unserialize usage |
| secrets-in-options.php | secrets | 1 | API key stored in options |
| rest-no-permissions.php | rest_ajax | 1 | REST endpoint without permissions |
| ajax-no-nonce.php | rest_ajax | 1 | AJAX handler without nonce |
| path-traversal-basic.php | path_traversal | 1 | Basic path traversal vulnerability |
| eval-usage.php | eval | 1 | Usage of eval() function |
| create-function-usage.php | eval | 1 | Usage of create_function() |
| xss-attribute-context.php | xss | 1 | XSS in HTML attribute context |
| xss-javascript-context.php | xss | 1 | XSS in JavaScript context |
| xss-url-context.php | xss | 1 | XSS in URL context |
| sqli-concatenation.php | sqli | 1 | SQL injection via string concatenation |
| sqli-wpdb-prepare-misuse.php | sqli | 1 | Misuse of $wpdb->prepare |
| csrf-admin-post.php | csrf | 1 | admin-post.php without nonce |
| authz-rest-permission.php | authz | 1 | REST endpoint without permission_callback |
| file-upload-extension-bypass.php | file_upload | 1 | File upload with extension bypass |
| secrets-in-meta.php | secrets | 1 | Secrets stored in post meta |
| xss-reflected.php | xss | 1 | Reflected XSS vulnerability |
| xss-stored.php | xss | 1 | Stored XSS vulnerability |
| sqli-union.php | sqli | 1 | SQL injection with UNION |
| csrf-ajax.php | csrf | 1 | AJAX without CSRF protection |
| xss-sqli-combo.php | xss, sqli | 2 | Combined XSS and SQL injection |
| authz-csrf-combo.php | authz, csrf | 2 | Combined authorization and CSRF issues |
| file-upload-xss-combo.php | file_upload, xss | 2 | File upload with XSS in filename |
| rest-ajax-combo.php | rest_ajax, rest_ajax | 2 | REST and AJAX without security |
| secrets-deserialization-combo.php | secrets, deserialization | 2 | Secrets with unsafe deserialization |
| xss-authz-file-combo.php | xss, authz, file_upload | 3 | Three security issues combined |
| sqli-csrf-rest-combo.php | sqli, csrf, rest_ajax | 3 | Three security issues combined |
| path-traversal-xss-authz-combo.php | path_traversal, xss, authz | 3 | Three security issues combined |
| eval-deserialization-secrets-combo.php | eval, deserialization, secrets | 3 | Three security issues combined |
| ajax-nonce-csrf-authz-combo.php | rest_ajax, csrf, authz | 3 | Three security issues combined |
| xss-sqli-csrf-authz-combo.php | xss, sqli, csrf, authz | 4 | Four security issues combined |
| file-upload-path-traversal-xss-authz-combo.php | file_upload, path_traversal, xss, authz | 4 | Four security issues combined |
| rest-ajax-csrf-authz-secrets-combo.php | rest_ajax, rest_ajax, csrf, authz, secrets | 4 | Four security issues combined |
| eval-deserialization-path-traversal-xss-combo.php | eval, deserialization, path_traversal, xss | 4 | Four security issues combined |
| sqli-csrf-authz-file-upload-combo.php | sqli, csrf, authz, file_upload | 4 | Four security issues combined |
| complex-xss-chain.php | complex | 5 | Complex XSS with multiple contexts |
| complex-sqli-chain.php | complex | 5 | Complex SQL injection patterns |
| complex-authz-chain.php | complex | 5 | Complex authorization bypasses |
| complex-file-upload-chain.php | complex | 5 | Complex file upload bypasses |
| complex-csrf-chain.php | complex | 5 | Complex CSRF vulnerabilities |
| subtle-xss-obfuscation.php | complex | 3 | XSS with JavaScript obfuscation |
| subtle-sqli-encoding.php | complex | 3 | SQL injection with encoding bypasses |
| subtle-authz-bypass.php | complex | 3 | Subtle authorization bypasses |
| subtle-file-upload-bypass.php | complex | 3 | Subtle file upload bypasses |
| subtle-csrf-bypass.php | complex | 3 | Subtle CSRF bypasses |
| advanced-obfuscation-xss.php | complex | 4 | XSS with advanced JavaScript obfuscation |
| advanced-obfuscation-sqli.php | complex | 4 | SQL injection with advanced encoding |
| advanced-obfuscation-authz.php | complex | 4 | Authorization bypass with advanced techniques |
| advanced-obfuscation-file-upload.php | complex | 4 | File upload bypass with advanced techniques |
| advanced-obfuscation-csrf.php | complex | 4 | CSRF bypass with advanced techniques |
| cross-file-vulnerability-1.php | complex | 3 | Vulnerabilities spanning multiple files |
| cross-file-vulnerability-2.php | complex | 3 | Vulnerabilities spanning multiple files |
| cross-file-vulnerability-3.php | complex | 3 | Vulnerabilities spanning multiple files |
| cross-file-vulnerability-4.php | complex | 3 | Vulnerabilities spanning multiple files |
| cross-file-vulnerability-5.php | complex | 3 | Vulnerabilities spanning multiple files |
| mega-vulnerability-1.php | complex | 6 | Multiple complex vulnerabilities |
| mega-vulnerability-2.php | complex | 6 | Multiple complex vulnerabilities |
| mega-vulnerability-3.php | complex | 6 | Multiple complex vulnerabilities |
| mega-vulnerability-4.php | complex | 6 | Multiple complex vulnerabilities |
| mega-vulnerability-5.php | complex | 6 | Multiple complex vulnerabilities |
| ultra-complex-1.php | complex | 7 | Ultra complex vulnerability patterns |
| ultra-complex-2.php | complex | 7 | Ultra complex vulnerability patterns |
| ultra-complex-3.php | complex | 7 | Ultra complex vulnerability patterns |
| ultra-complex-4.php | complex | 7 | Ultra complex vulnerability patterns |
| ultra-complex-5.php | complex | 7 | Ultra complex vulnerability patterns |
| edge-case-vulnerability-1.php | complex | 4 | Edge case vulnerability patterns |
| edge-case-vulnerability-2.php | complex | 4 | Edge case vulnerability patterns |
| edge-case-vulnerability-3.php | complex | 4 | Edge case vulnerability patterns |
| edge-case-vulnerability-4.php | complex | 4 | Edge case vulnerability patterns |
| edge-case-vulnerability-5.php | complex | 4 | Edge case vulnerability patterns |
| performance-test-large-1.php | complex | 5 | Large file with multiple vulnerabilities for performance testing |
| performance-test-large-2.php | complex | 5 | Large file with multiple vulnerabilities for performance testing |
| performance-test-large-3.php | complex | 5 | Large file with multiple vulnerabilities for performance testing |
| performance-test-large-4.php | complex | 5 | Large file with multiple vulnerabilities for performance testing |
| performance-test-large-5.php | complex | 5 | Large file with multiple vulnerabilities for performance testing |
