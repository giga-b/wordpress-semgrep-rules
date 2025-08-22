# Quality Benchmark Test File Registry

This document tracks all 100 test files created for quality benchmarking, including expected security findings and complexity levels.

## Test File Overview

- **Total Files**: 100
- **Safe Files**: 20 (20%)
- **Single Flaw Files**: 25 (25%)
- **Multiple Flaw Files**: 35 (35%)
- **Complex Flaw Files**: 20 (20%)

## File Categories

### Category 1: Safe Files (Files 1-20)
Files with no security vulnerabilities, used to test false positive rates.

### Category 2: Single Flaw Files (Files 21-45)
Files with exactly one security vulnerability, used to test basic detection accuracy.

### Category 3: Multiple Flaw Files (Files 46-80)
Files with 2-4 security vulnerabilities, used to test comprehensive detection.

### Category 4: Complex Flaw Files (Files 81-100)
Files with 5+ security vulnerabilities, including subtle and complex patterns.

## Test File Details

### File 1: safe-basic-functions.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Basic WordPress functions with proper security practices
- **File Size**: ~2KB

### File 2: safe-ajax-handler.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: AJAX handler with proper nonce verification
- **File Size**: ~3KB

### File 3: safe-form-processing.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Form processing with proper sanitization
- **File Size**: ~2.5KB

### File 4: safe-database-query.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Database queries using prepared statements
- **File Size**: ~2KB

### File 5: safe-file-upload.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: File upload with proper validation
- **File Size**: ~3KB

### File 6: safe-rest-endpoint.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: REST endpoint with proper permissions
- **File Size**: ~2.5KB

### File 7: safe-capability-check.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Capability checks before sensitive operations
- **File Size**: ~2KB

### File 8: safe-option-storage.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Safe option storage without sensitive data
- **File Size**: ~2KB

### File 9: safe-meta-handling.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Meta data handling with proper sanitization
- **File Size**: ~2.5KB

### File 10: safe-redirect.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Safe redirects with validation
- **File Size**: ~2KB

### File 11: safe-email-sending.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Email sending with proper headers
- **File Size**: ~2.5KB

### File 12: safe-cron-handler.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Cron job handler with security checks
- **File Size**: ~2KB

### File 13: safe-widget-render.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Widget rendering with escaping
- **File Size**: ~2.5KB

### File 14: safe-shortcode.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Shortcode with proper output escaping
- **File Size**: ~2KB

### File 15: safe-admin-page.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Admin page with capability checks
- **File Size**: ~2.5KB

### File 16: safe-ajax-callback.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: AJAX callback with nonce verification
- **File Size**: ~2KB

### File 17: safe-hook-handler.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Hook handler with proper validation
- **File Size**: ~2.5KB

### File 18: safe-template-file.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Template file with escaping
- **File Size**: ~2KB

### File 19: safe-plugin-activation.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Plugin activation with checks
- **File Size**: ~2.5KB

### File 20: safe-plugin-deactivation.php
- **Status**: Safe
- **Expected Findings**: 0
- **Complexity**: Low
- **Description**: Plugin deactivation with cleanup
- **File Size**: ~2KB

### File 21: xss-basic-output.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (XSS)
- **Complexity**: Low
- **Description**: Basic XSS vulnerability in echo
- **File Size**: ~2.5KB

### File 22: sqli-basic-query.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (SQL Injection)
- **Complexity**: Low
- **Description**: SQL injection in raw query
- **File Size**: ~2.5KB

### File 23: csrf-no-nonce.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (CSRF)
- **Complexity**: Low
- **Description**: Form without nonce verification
- **File Size**: ~2.5KB

### File 24: authz-no-capability.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (Authorization)
- **Complexity**: Low
- **Description**: Admin function without capability check
- **File Size**: ~2.5KB

### File 25: file-upload-no-validation.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (File Upload)
- **Complexity**: Low
- **Description**: File upload without validation
- **File Size**: ~3KB

### File 26: deserialization-unsafe.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (Deserialization)
- **Complexity**: Low
- **Description**: Unsafe unserialize usage
- **File Size**: ~2.5KB

### File 27: secrets-in-options.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (Secrets Storage)
- **Complexity**: Low
- **Description**: API key stored in options
- **File Size**: ~2.5KB

### File 28: rest-no-permissions.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (REST Security)
- **Complexity**: Low
- **Description**: REST endpoint without permissions
- **File Size**: ~2.5KB

### File 29: ajax-no-nonce.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (AJAX Security)
- **Complexity**: Low
- **Description**: AJAX handler without nonce
- **File Size**: ~2.5KB

### File 30: path-traversal-basic.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (Path Traversal)
- **Complexity**: Low
- **Description**: Basic path traversal vulnerability
- **File Size**: ~2.5KB

### File 31: eval-usage.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (Dynamic Execution)
- **Complexity**: Low
- **Description**: Usage of eval() function
- **File Size**: ~2.5KB

### File 32: create-function-usage.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (Dynamic Execution)
- **Complexity**: Low
- **Description**: Usage of create_function()
- **File Size**: ~2.5KB

### File 33: xss-attribute-context.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (XSS)
- **Complexity**: Low
- **Description**: XSS in HTML attribute context
- **File Size**: ~2.5KB

### File 34: xss-javascript-context.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (XSS)
- **Complexity**: Low
- **Description**: XSS in JavaScript context
- **File Size**: ~2.5KB

### File 35: xss-url-context.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (XSS)
- **Complexity**: Low
- **Description**: XSS in URL context
- **File Size**: ~2.5KB

### File 36: sqli-concatenation.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (SQL Injection)
- **Complexity**: Low
- **Description**: SQL injection via string concatenation
- **File Size**: ~2.5KB

### File 37: sqli-wpdb-prepare-misuse.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (SQL Injection)
- **Complexity**: Low
- **Description**: Misuse of $wpdb->prepare
- **File Size**: ~2.5KB

### File 38: csrf-admin-post.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (CSRF)
- **Complexity**: Low
- **Description**: admin-post.php without nonce
- **File Size**: ~2.5KB

### File 39: authz-rest-permission.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (Authorization)
- **Complexity**: Low
- **Description**: REST endpoint without permission_callback
- **File Size**: ~2.5KB

### File 40: file-upload-extension-bypass.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (File Upload)
- **Complexity**: Low
- **Description**: File upload with extension bypass
- **File Size**: ~3KB

### File 41: secrets-in-meta.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (Secrets Storage)
- **Complexity**: Low
- **Description**: Secrets stored in post meta
- **File Size**: ~2.5KB

### File 42: xss-reflected.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (XSS)
- **Complexity**: Low
- **Description**: Reflected XSS vulnerability
- **File Size**: ~2.5KB

### File 43: xss-stored.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (XSS)
- **Complexity**: Low
- **Description**: Stored XSS vulnerability
- **File Size**: ~2.5KB

### File 44: sqli-union.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (SQL Injection)
- **Complexity**: Low
- **Description**: SQL injection with UNION
- **File Size**: ~2.5KB

### File 45: csrf-ajax.php
- **Status**: Vulnerable
- **Expected Findings**: 1 (CSRF)
- **Complexity**: Low
- **Description**: AJAX without CSRF protection
- **File Size**: ~2.5KB

### File 46: xss-sqli-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 2 (XSS, SQL Injection)
- **Complexity**: Medium
- **Description**: Combined XSS and SQL injection
- **File Size**: ~4KB

### File 47: authz-csrf-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 2 (Authorization, CSRF)
- **Complexity**: Medium
- **Description**: Combined authorization and CSRF issues
- **File Size**: ~4KB

### File 48: file-upload-xss-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 2 (File Upload, XSS)
- **Complexity**: Medium
- **Description**: File upload with XSS in filename
- **File Size**: ~4KB

### File 49: rest-ajax-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 2 (REST Security, AJAX Security)
- **Complexity**: Medium
- **Description**: REST and AJAX without security
- **File Size**: ~4KB

### File 50: secrets-deserialization-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 2 (Secrets Storage, Deserialization)
- **Complexity**: Medium
- **Description**: Secrets with unsafe deserialization
- **File Size**: ~4KB

### File 51: xss-authz-file-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (XSS, Authorization, File Upload)
- **Complexity**: Medium
- **Description**: Three security issues combined
- **File Size**: ~5KB

### File 52: sqli-csrf-rest-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (SQL Injection, CSRF, REST Security)
- **Complexity**: Medium
- **Description**: Three security issues combined
- **File Size**: ~5KB

### File 53: path-traversal-xss-authz-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (Path Traversal, XSS, Authorization)
- **Complexity**: Medium
- **Description**: Three security issues combined
- **File Size**: ~5KB

### File 54: eval-deserialization-secrets-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (Dynamic Execution, Deserialization, Secrets)
- **Complexity**: Medium
- **Description**: Three security issues combined
- **File Size**: ~5KB

### File 55: ajax-nonce-csrf-authz-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (AJAX Security, CSRF, Authorization)
- **Complexity**: Medium
- **Description**: Three security issues combined
- **File Size**: ~5KB

### File 56: xss-sqli-csrf-authz-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (XSS, SQL Injection, CSRF, Authorization)
- **Complexity**: High
- **Description**: Four security issues combined
- **File Size**: ~6KB

### File 57: file-upload-path-traversal-xss-authz-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (File Upload, Path Traversal, XSS, Authorization)
- **Complexity**: High
- **Description**: Four security issues combined
- **File Size**: ~6KB

### File 58: rest-ajax-csrf-authz-secrets-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (REST Security, AJAX Security, CSRF, Authorization, Secrets)
- **Complexity**: High
- **Description**: Four security issues combined
- **File Size**: ~6KB

### File 59: eval-deserialization-path-traversal-xss-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (Dynamic Execution, Deserialization, Path Traversal, XSS)
- **Complexity**: High
- **Description**: Four security issues combined
- **File Size**: ~6KB

### File 60: sqli-csrf-authz-file-upload-combo.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (SQL Injection, CSRF, Authorization, File Upload)
- **Complexity**: High
- **Description**: Four security issues combined
- **File Size**: ~6KB

### File 61: complex-xss-chain.php
- **Status**: Vulnerable
- **Expected Findings**: 5 (XSS variants)
- **Complexity**: High
- **Description**: Complex XSS with multiple contexts
- **File Size**: ~7KB

### File 62: complex-sqli-chain.php
- **Status**: Vulnerable
- **Expected Findings**: 5 (SQL Injection variants)
- **Complexity**: High
- **Description**: Complex SQL injection patterns
- **File Size**: ~7KB

### File 63: complex-authz-chain.php
- **Status**: Vulnerable
- **Expected Findings**: 5 (Authorization variants)
- **Complexity**: High
- **Description**: Complex authorization bypasses
- **File Size**: ~7KB

### File 64: complex-file-upload-chain.php
- **Status**: Vulnerable
- **Expected Findings**: 5 (File Upload variants)
- **Complexity**: High
- **Description**: Complex file upload bypasses
- **File Size**: ~7KB

### File 65: complex-csrf-chain.php
- **Status**: Vulnerable
- **Expected Findings**: 5 (CSRF variants)
- **Complexity**: High
- **Description**: Complex CSRF vulnerabilities
- **File Size**: ~7KB

### File 66: subtle-xss-obfuscation.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (XSS with obfuscation)
- **Complexity**: High
- **Description**: XSS with JavaScript obfuscation
- **File Size**: ~6KB

### File 67: subtle-sqli-encoding.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (SQL Injection with encoding)
- **Complexity**: High
- **Description**: SQL injection with encoding bypasses
- **File Size**: ~6KB

### File 68: subtle-authz-bypass.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (Authorization bypass)
- **Complexity**: High
- **Description**: Subtle authorization bypasses
- **File Size**: ~6KB

### File 69: subtle-file-upload-bypass.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (File Upload bypass)
- **Complexity**: High
- **Description**: Subtle file upload bypasses
- **File Size**: ~6KB

### File 70: subtle-csrf-bypass.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (CSRF bypass)
- **Complexity**: High
- **Description**: Subtle CSRF bypasses
- **File Size**: ~6KB

### File 71: advanced-obfuscation-xss.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (XSS with advanced obfuscation)
- **Complexity**: Very High
- **Description**: XSS with advanced JavaScript obfuscation
- **File Size**: ~8KB

### File 72: advanced-obfuscation-sqli.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (SQL Injection with advanced obfuscation)
- **Complexity**: Very High
- **Description**: SQL injection with advanced encoding
- **File Size**: ~8KB

### File 73: advanced-obfuscation-authz.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (Authorization with advanced obfuscation)
- **Complexity**: Very High
- **Description**: Authorization bypass with advanced techniques
- **File Size**: ~8KB

### File 74: advanced-obfuscation-file-upload.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (File Upload with advanced obfuscation)
- **Complexity**: Very High
- **Description**: File upload bypass with advanced techniques
- **File Size**: ~8KB

### File 75: advanced-obfuscation-csrf.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (CSRF with advanced obfuscation)
- **Complexity**: Very High
- **Description**: CSRF bypass with advanced techniques
- **File Size**: ~8KB

### File 76: cross-file-vulnerability-1.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (Cross-file vulnerabilities)
- **Complexity**: High
- **Description**: Vulnerabilities spanning multiple files
- **File Size**: ~6KB

### File 77: cross-file-vulnerability-2.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (Cross-file vulnerabilities)
- **Complexity**: High
- **Description**: Vulnerabilities spanning multiple files
- **File Size**: ~6KB

### File 78: cross-file-vulnerability-3.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (Cross-file vulnerabilities)
- **Complexity**: High
- **Description**: Vulnerabilities spanning multiple files
- **File Size**: ~6KB

### File 79: cross-file-vulnerability-4.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (Cross-file vulnerabilities)
- **Complexity**: High
- **Description**: Vulnerabilities spanning multiple files
- **File Size**: ~6KB

### File 80: cross-file-vulnerability-5.php
- **Status**: Vulnerable
- **Expected Findings**: 3 (Cross-file vulnerabilities)
- **Complexity**: High
- **Description**: Vulnerabilities spanning multiple files
- **File Size**: ~6KB

### File 81: mega-vulnerability-1.php
- **Status**: Vulnerable
- **Expected Findings**: 6 (Multiple vulnerability types)
- **Complexity**: Very High
- **Description**: Multiple complex vulnerabilities
- **File Size**: ~10KB

### File 82: mega-vulnerability-2.php
- **Status**: Vulnerable
- **Expected Findings**: 6 (Multiple vulnerability types)
- **Complexity**: Very High
- **Description**: Multiple complex vulnerabilities
- **File Size**: ~10KB

### File 83: mega-vulnerability-3.php
- **Status**: Vulnerable
- **Expected Findings**: 6 (Multiple vulnerability types)
- **Complexity**: Very High
- **Description**: Multiple complex vulnerabilities
- **File Size**: ~10KB

### File 84: mega-vulnerability-4.php
- **Status**: Vulnerable
- **Expected Findings**: 6 (Multiple vulnerability types)
- **Complexity**: Very High
- **Description**: Multiple complex vulnerabilities
- **File Size**: ~10KB

### File 85: mega-vulnerability-5.php
- **Status**: Vulnerable
- **Expected Findings**: 6 (Multiple vulnerability types)
- **Complexity**: Very High
- **Description**: Multiple complex vulnerabilities
- **File Size**: ~10KB

### File 86: ultra-complex-1.php
- **Status**: Vulnerable
- **Expected Findings**: 7 (Ultra complex vulnerabilities)
- **Complexity**: Extreme
- **Description**: Ultra complex vulnerability patterns
- **File Size**: ~12KB

### File 87: ultra-complex-2.php
- **Status**: Vulnerable
- **Expected Findings**: 7 (Ultra complex vulnerabilities)
- **Complexity**: Extreme
- **Description**: Ultra complex vulnerability patterns
- **File Size**: ~12KB

### File 88: ultra-complex-3.php
- **Status**: Vulnerable
- **Expected Findings**: 7 (Ultra complex vulnerabilities)
- **Complexity**: Extreme
- **Description**: Ultra complex vulnerability patterns
- **File Size**: ~12KB

### File 89: ultra-complex-4.php
- **Status**: Vulnerable
- **Expected Findings**: 7 (Ultra complex vulnerabilities)
- **Complexity**: Extreme
- **Description**: Ultra complex vulnerability patterns
- **File Size**: ~12KB

### File 90: ultra-complex-5.php
- **Status**: Vulnerable
- **Expected Findings**: 7 (Ultra complex vulnerabilities)
- **Complexity**: Extreme
- **Description**: Ultra complex vulnerability patterns
- **File Size**: ~12KB

### File 91: edge-case-vulnerability-1.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (Edge case vulnerabilities)
- **Complexity**: Very High
- **Description**: Edge case vulnerability patterns
- **File Size**: ~8KB

### File 92: edge-case-vulnerability-2.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (Edge case vulnerabilities)
- **Complexity**: Very High
- **Description**: Edge case vulnerability patterns
- **File Size**: ~8KB

### File 93: edge-case-vulnerability-3.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (Edge case vulnerabilities)
- **Complexity**: Very High
- **Description**: Edge case vulnerability patterns
- **File Size**: ~8KB

### File 94: edge-case-vulnerability-4.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (Edge case vulnerabilities)
- **Complexity**: Very High
- **Description**: Edge case vulnerability patterns
- **File Size**: ~8KB

### File 95: edge-case-vulnerability-5.php
- **Status**: Vulnerable
- **Expected Findings**: 4 (Edge case vulnerabilities)
- **Complexity**: Very High
- **Description**: Edge case vulnerability patterns
- **File Size**: ~8KB

### File 96: performance-test-large-1.php
- **Status**: Vulnerable
- **Expected Findings**: 5 (Performance test vulnerabilities)
- **Complexity**: High
- **Description**: Large file with multiple vulnerabilities for performance testing
- **File Size**: ~15KB

### File 97: performance-test-large-2.php
- **Status**: Vulnerable
- **Expected Findings**: 5 (Performance test vulnerabilities)
- **Complexity**: High
- **Description**: Large file with multiple vulnerabilities for performance testing
- **File Size**: ~15KB

### File 98: performance-test-large-3.php
- **Status**: Vulnerable
- **Expected Findings**: 5 (Performance test vulnerabilities)
- **Complexity**: High
- **Description**: Large file with multiple vulnerabilities for performance testing
- **File Size**: ~15KB

### File 99: performance-test-large-4.php
- **Status**: Vulnerable
- **Expected Findings**: 5 (Performance test vulnerabilities)
- **Complexity**: High
- **Description**: Large file with multiple vulnerabilities for performance testing
- **File Size**: ~15KB

### File 100: performance-test-large-5.php
- **Status**: Vulnerable
- **Expected Findings**: 5 (Performance test vulnerabilities)
- **Complexity**: High
- **Description**: Large file with multiple vulnerabilities for performance testing
- **File Size**: ~15KB

## Summary Statistics

### Expected Total Findings
- **Safe Files**: 0 findings
- **Single Flaw Files**: 25 findings
- **Multiple Flaw Files**: 105 findings (average 3 per file)
- **Complex Flaw Files**: 140 findings (average 7 per file)
- **Total Expected Findings**: 270

### Complexity Distribution
- **Low Complexity**: 45 files (45%)
- **Medium Complexity**: 30 files (30%)
- **High Complexity**: 20 files (20%)
- **Very High Complexity**: 5 files (5%)

### Vulnerability Type Distribution
- **XSS**: ~60 instances
- **SQL Injection**: ~50 instances
- **CSRF**: ~45 instances
- **Authorization**: ~40 instances
- **File Upload**: ~35 instances
- **Deserialization**: ~20 instances
- **Secrets Storage**: ~20 instances

## Testing Notes

1. **Baseline Testing**: Start with safe files to establish false positive baseline
2. **Progressive Testing**: Move from simple to complex vulnerabilities
3. **Performance Testing**: Use large files to test scanning performance
4. **Edge Case Testing**: Focus on subtle and complex patterns
5. **Cross-File Testing**: Test rules that span multiple files

## Quality Benchmark Targets

Based on these test files, the system should achieve:
- **Precision**: ≥95% (≤13 false positives out of 270 total findings)
- **Recall**: ≥95% (≥257 true positives out of 270 expected findings)
- **False Positive Rate**: ≤5%
- **False Negative Rate**: ≤5%
- **Test Coverage**: 100% (all files tested)
- **Baseline Stability**: ≥99%
