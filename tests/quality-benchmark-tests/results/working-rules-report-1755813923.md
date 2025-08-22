# Working Rules Quality Benchmark Report

**Generated**: 2025-08-21 18:05:23

## Summary

- **Total vulnerabilities detected**: 28
- **Expected vulnerabilities**: 255
- **Detection rate**: 11.0%
- **Quality status**: NEEDS IMPROVEMENT
- **Scan time**: 16.55 seconds
- **Files scanned**: 102

## Vulnerability Breakdown

- **quality-benchmark-tests**: 28

## Detailed Findings

### tests.quality-benchmark-tests.wordpress.execution.eval
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\eval-deserialization-path-traversal-xss-combo.php
- **Line**: 17
- **Message**: Unsafe eval usage with user input
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.deserialization.unsafe
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\eval-deserialization-path-traversal-xss-combo.php
- **Line**: 19
- **Message**: Unsafe deserialization of user input
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo-get
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\eval-deserialization-path-traversal-xss-combo.php
- **Line**: 23
- **Message**: Unsafe echo of GET parameter without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.execution.eval
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\eval-deserialization-secrets-combo.php
- **Line**: 17
- **Message**: Unsafe eval usage with user input
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.deserialization.unsafe
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\eval-deserialization-secrets-combo.php
- **Line**: 19
- **Message**: Unsafe deserialization of user input
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo-get
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\file-upload-path-traversal-xss-authz-combo.php
- **Line**: 21
- **Message**: Unsafe echo of GET parameter without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo-get
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\file-upload-xss-combo.php
- **Line**: 19
- **Message**: Unsafe echo of GET parameter without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo-get
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\path-traversal-xss-authz-combo.php
- **Line**: 19
- **Message**: Unsafe echo of GET parameter without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.deserialization.unsafe
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\secrets-deserialization-combo.php
- **Line**: 19
- **Message**: Unsafe deserialization of user input
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\simple-test.php
- **Line**: 3
- **Message**: Unsafe echo of user input without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.sqli.unsafe-concatenation
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\sqli-basic-query.php
- **Line**: 18
- **Message**: Unsafe SQL query with string concatenation
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.sqli.unsafe-concatenation
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\sqli-concatenation.php
- **Line**: 18
- **Message**: Unsafe SQL query with string concatenation
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.sqli.unsafe-concatenation-get
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\sqli-csrf-authz-file-upload-combo.php
- **Line**: 17
- **Message**: Unsafe SQL query with GET parameter concatenation
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.sqli.unsafe-concatenation-get
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\sqli-csrf-rest-combo.php
- **Line**: 17
- **Message**: Unsafe SQL query with GET parameter concatenation
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.sqli.unsafe-concatenation
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\sqli-union.php
- **Line**: 18
- **Message**: Unsafe SQL query with string concatenation
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.sqli.unsafe-concatenation
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\sqli-wpdb-prepare-misuse.php
- **Line**: 18
- **Message**: Unsafe SQL query with string concatenation
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\xss-attribute-context.php
- **Line**: 16
- **Message**: Unsafe echo of user input without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo-get
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\xss-authz-file-combo.php
- **Line**: 17
- **Message**: Unsafe echo of GET parameter without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\xss-basic-output-simple.php
- **Line**: 3
- **Message**: Unsafe echo of user input without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\xss-basic-output.php
- **Line**: 16
- **Message**: Unsafe echo of user input without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\xss-javascript-context.php
- **Line**: 16
- **Message**: Unsafe echo of user input without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\xss-reflected.php
- **Line**: 16
- **Message**: Unsafe echo of user input without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo-get
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\xss-sqli-combo.php
- **Line**: 17
- **Message**: Unsafe echo of GET parameter without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.sqli.unsafe-concatenation-get
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\xss-sqli-combo.php
- **Line**: 19
- **Message**: Unsafe SQL query with GET parameter concatenation
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo-get
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\xss-sqli-csrf-authz-combo.php
- **Line**: 17
- **Message**: Unsafe echo of GET parameter without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.sqli.unsafe-concatenation-get
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\xss-sqli-csrf-authz-combo.php
- **Line**: 19
- **Message**: Unsafe SQL query with GET parameter concatenation
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\xss-stored.php
- **Line**: 16
- **Message**: Unsafe echo of user input without escaping
- **Severity**: ERROR

### tests.quality-benchmark-tests.wordpress.xss.unsafe-echo
- **File**: C:\Users\mobet\DevProjects\wordpress-semgrep-rules\tests\quality-benchmark-tests\xss-url-context.php
- **Line**: 16
- **Message**: Unsafe echo of user input without escaping
- **Severity**: ERROR

