# Advanced Test Cases Documentation

## Overview

This document describes the advanced test cases created for Task 17 of the WordPress Semgrep Rules project. These test cases cover sophisticated attack patterns, edge cases, and complex vulnerability scenarios that go beyond basic security testing.

## Test Files Structure

### 1. Advanced Vulnerabilities (`tests/vulnerable-examples/advanced-vulnerabilities.php`)

This file contains 20 categories of advanced vulnerability patterns:

#### 1.1 Advanced Obfuscation Techniques
- **Purpose**: Test detection of vulnerabilities hidden through encoding/decoding
- **Examples**: Base64 encoding, URL encoding, string manipulation
- **Vulnerabilities**: XSS, SQL injection via obfuscation

#### 1.2 Token Leakage Patterns
- **Purpose**: Test detection of sensitive information exposure
- **Examples**: API keys in error logs, debug headers, admin notices
- **Vulnerabilities**: Information disclosure, credential exposure

#### 1.3 Nonce Confusion Patterns
- **Purpose**: Test detection of incorrect nonce usage
- **Examples**: Wrong action names, generic actions, cross-plugin nonces
- **Vulnerabilities**: CSRF, authorization bypass

#### 1.4 Advanced Deserialization
- **Purpose**: Test detection of dangerous deserialization patterns
- **Examples**: Object injection, code execution via deserialization
- **Vulnerabilities**: Remote code execution, object injection

#### 1.5 Advanced Path Traversal
- **Purpose**: Test detection of sophisticated path traversal attacks
- **Examples**: Weak normalization, URL encoding bypass
- **Vulnerabilities**: File inclusion, directory traversal

#### 1.6 Advanced SSRF Patterns
- **Purpose**: Test detection of complex SSRF scenarios
- **Examples**: Obfuscated URLs, redirect chains
- **Vulnerabilities**: Server-side request forgery

#### 1.7 Advanced XSS via DOM Manipulation
- **Purpose**: Test detection of XSS in JavaScript contexts
- **Examples**: Template injection, DOM manipulation
- **Vulnerabilities**: Cross-site scripting

#### 1.8 Advanced SQL Injection
- **Purpose**: Test detection of complex SQL injection patterns
- **Examples**: Stored procedures, UNION attacks
- **Vulnerabilities**: SQL injection

#### 1.9 Advanced File Upload Vulnerabilities
- **Purpose**: Test detection of file upload bypass techniques
- **Examples**: Extension bypass, MIME type spoofing
- **Vulnerabilities**: File upload, code execution

#### 1.10 Advanced Authentication Bypass
- **Purpose**: Test detection of authentication weaknesses
- **Examples**: Weak comparisons, timing attacks
- **Vulnerabilities**: Authentication bypass

#### 1.11 Advanced CSRF Patterns
- **Purpose**: Test detection of CSRF protection weaknesses
- **Examples**: Weak origin checks, missing referer validation
- **Vulnerabilities**: Cross-site request forgery

#### 1.12 Advanced Information Disclosure
- **Purpose**: Test detection of sensitive information exposure
- **Examples**: Debug information, error messages
- **Vulnerabilities**: Information disclosure

#### 1.13 Advanced Command Injection
- **Purpose**: Test detection of command execution vulnerabilities
- **Examples**: Obfuscated commands, encoding bypass
- **Vulnerabilities**: Command injection

#### 1.14 Advanced Session Vulnerabilities
- **Purpose**: Test detection of session management issues
- **Examples**: Session fixation, session hijacking
- **Vulnerabilities**: Session management

#### 1.15 Advanced Encryption Vulnerabilities
- **Purpose**: Test detection of weak cryptographic practices
- **Examples**: Weak encryption, poor randomness
- **Vulnerabilities**: Cryptographic weaknesses

#### 1.16 Advanced Business Logic Vulnerabilities
- **Purpose**: Test detection of logical flaws
- **Examples**: Race conditions, privilege escalation
- **Vulnerabilities**: Business logic flaws

#### 1.17 Advanced API Vulnerabilities
- **Purpose**: Test detection of API security issues
- **Examples**: Mass assignment, insecure object references
- **Vulnerabilities**: API security

#### 1.18 Advanced WordPress-Specific Vulnerabilities
- **Purpose**: Test detection of WordPress-specific issues
- **Examples**: Hook injection, option injection
- **Vulnerabilities**: WordPress-specific attacks

#### 1.19 Advanced Taint Analysis Edge Cases
- **Purpose**: Test taint analysis accuracy
- **Examples**: Complex taint flows, conditional sanitization
- **Vulnerabilities**: Taint analysis bypass

#### 1.20 Advanced Evasion Techniques
- **Purpose**: Test detection of attack evasion
- **Examples**: Unicode normalization, encoding chains
- **Vulnerabilities**: Evasion techniques

### 2. Edge Case Vulnerabilities (`tests/vulnerable-examples/edge-case-vulnerabilities.php`)

This file contains 20 categories of edge case vulnerability patterns:

#### 2.1 Complex Taint Flow Patterns
- **Purpose**: Test detection of multi-step taint propagation
- **Examples**: Variable chaining, array processing
- **Vulnerabilities**: Complex XSS, SQL injection

#### 2.2 Chained Vulnerabilities
- **Purpose**: Test detection of vulnerability chains
- **Examples**: SQL injection leading to XSS
- **Vulnerabilities**: Vulnerability chains

#### 2.3 Conditional Vulnerabilities
- **Purpose**: Test detection of context-dependent vulnerabilities
- **Examples**: Admin-only vulnerabilities, authorized-only flaws
- **Vulnerabilities**: Conditional security issues

#### 2.4 Recursive Vulnerabilities
- **Purpose**: Test detection in recursive functions
- **Examples**: Recursive XSS, recursive SQL injection
- **Vulnerabilities**: Recursive security issues

#### 2.5 Callback Vulnerabilities
- **Purpose**: Test detection in callback functions
- **Examples**: Anonymous functions, closures
- **Vulnerabilities**: Callback-based attacks

#### 2.6 Object Property Vulnerabilities
- **Purpose**: Test detection via object properties
- **Examples**: Dynamic property access, object manipulation
- **Vulnerabilities**: Object-based attacks

#### 2.7 Array Key Vulnerabilities
- **Purpose**: Test detection via array key manipulation
- **Examples**: Dynamic array access, key-based attacks
- **Vulnerabilities**: Array-based attacks

#### 2.8 String Concatenation Vulnerabilities
- **Purpose**: Test detection in string building
- **Examples**: Dynamic query building, template construction
- **Vulnerabilities**: String-based attacks

#### 2.9 Variable Variable Vulnerabilities
- **Purpose**: Test detection of dynamic variable usage
- **Examples**: Variable variables, dynamic property access
- **Vulnerabilities**: Dynamic variable attacks

#### 2.10 Reflection Vulnerabilities
- **Purpose**: Test detection of reflection-based attacks
- **Examples**: Dynamic method calls, reflection APIs
- **Vulnerabilities**: Reflection-based attacks

#### 2.11 Serialization Vulnerabilities
- **Purpose**: Test detection of serialization issues
- **Examples**: Object serialization, data persistence
- **Vulnerabilities**: Serialization attacks

#### 2.12 Encoding Bypass Vulnerabilities
- **Purpose**: Test detection of encoding bypass techniques
- **Examples**: Multiple encoding layers, decoding chains
- **Vulnerabilities**: Encoding bypass attacks

#### 2.13 Context Switching Vulnerabilities
- **Purpose**: Test detection across different contexts
- **Examples**: HTML, JavaScript, CSS contexts
- **Vulnerabilities**: Context-based attacks

#### 2.14 State-Based Vulnerabilities
- **Purpose**: Test detection of state-dependent issues
- **Examples**: Static variables, persistent state
- **Vulnerabilities**: State-based attacks

#### 2.15 Closure Vulnerabilities
- **Purpose**: Test detection in closure functions
- **Examples**: Anonymous functions, lexical scoping
- **Vulnerabilities**: Closure-based attacks

#### 2.16 Magic Method Vulnerabilities
- **Purpose**: Test detection via PHP magic methods
- **Examples**: __toString, __get methods
- **Vulnerabilities**: Magic method attacks

#### 2.17 Exception Handling Vulnerabilities
- **Purpose**: Test detection in exception handling
- **Examples**: Exception messages, error handling
- **Vulnerabilities**: Exception-based attacks

#### 2.18 Recursive Deserialization
- **Purpose**: Test detection of recursive deserialization
- **Examples**: Object chains, recursive structures
- **Vulnerabilities**: Recursive deserialization attacks

#### 2.19 Template Injection Edge Cases
- **Purpose**: Test detection of complex template injection
- **Examples**: Multi-context templates, complex structures
- **Vulnerabilities**: Template injection attacks

#### 2.20 Advanced Evasion Techniques
- **Purpose**: Test detection of sophisticated evasion
- **Examples**: Multiple encoding layers, complex chains
- **Vulnerabilities**: Advanced evasion attacks

### 3. Safe Examples (`tests/safe-examples/advanced-vulnerabilities-safe.php`)

This file contains secure implementations for all the advanced vulnerability patterns, demonstrating proper security practices and serving as false positive tests.

## Test Runner

### Advanced Test Runner (`tests/run-advanced-tests.php`)

A comprehensive test runner that:

1. **Tests Vulnerable Examples**: Validates that security rules detect advanced vulnerabilities
2. **Tests Safe Examples**: Ensures rules don't generate false positives on secure code
3. **Tests Edge Cases**: Validates detection of complex attack patterns
4. **Generates Reports**: Provides detailed analysis of rule performance
5. **Exports Results**: Saves test results to JSON format

#### Usage

```bash
cd tests
php run-advanced-tests.php
```

#### Output

The test runner provides:

- **Test Statistics**: Total tests, detections, false positives, missed vulnerabilities
- **Performance Metrics**: Detection rate, false positive rate
- **Rule Performance**: Individual rule performance analysis
- **Vulnerability Distribution**: Breakdown by vulnerability type
- **Recommendations**: Suggestions for rule improvements

## Test Categories

### Vulnerability Types Covered

1. **Cross-Site Scripting (XSS)**
   - DOM-based XSS
   - Template injection
   - Context-based XSS
   - Encoding bypass XSS

2. **SQL Injection**
   - Union attacks
   - Stored procedures
   - Obfuscated SQL injection
   - Chained SQL injection

3. **File Upload Vulnerabilities**
   - Extension bypass
   - MIME type spoofing
   - Path traversal
   - File inclusion

4. **Authentication & Authorization**
   - Authentication bypass
   - Privilege escalation
   - Session vulnerabilities
   - Nonce confusion

5. **Information Disclosure**
   - Token leakage
   - Debug information
   - Error messages
   - Sensitive data exposure

6. **Business Logic Vulnerabilities**
   - Race conditions
   - Mass assignment
   - Insecure object references
   - Logical flaws

7. **Advanced Attack Patterns**
   - Obfuscation techniques
   - Evasion methods
   - Chained vulnerabilities
   - Edge cases

### Test Scenarios

#### 1. Obfuscation Detection
Tests the ability to detect vulnerabilities hidden through:
- Base64 encoding/decoding
- URL encoding/decoding
- String manipulation
- Variable chaining

#### 2. Context-Aware Detection
Tests detection across different contexts:
- HTML context
- JavaScript context
- CSS context
- SQL context

#### 3. Taint Flow Analysis
Tests complex taint propagation:
- Multi-step taint flows
- Conditional taint flows
- Recursive taint flows
- Object-based taint flows

#### 4. Edge Case Detection
Tests detection of unusual patterns:
- Variable variables
- Reflection APIs
- Magic methods
- Closures and callbacks

## Integration with Existing Tests

### Relationship to Basic Tests

The advanced test cases complement the existing basic test files:

- **Basic Tests**: Cover fundamental vulnerability patterns
- **Advanced Tests**: Cover sophisticated attack techniques
- **Edge Case Tests**: Cover unusual and complex scenarios

### Test Coverage

The advanced test cases provide:

- **Comprehensive Coverage**: 40+ vulnerability categories
- **Real-world Scenarios**: Based on actual attack patterns
- **False Positive Testing**: Secure implementations for validation
- **Performance Testing**: Complex scenarios for performance validation

## Usage Guidelines

### For Rule Developers

1. **Test New Rules**: Use advanced test cases to validate new security rules
2. **Improve Existing Rules**: Identify gaps in current rule coverage
3. **Reduce False Positives**: Validate rules against safe examples
4. **Performance Testing**: Test rule performance on complex scenarios

### For Security Researchers

1. **Vulnerability Research**: Study advanced attack patterns
2. **Tool Validation**: Test security tools against sophisticated scenarios
3. **Education**: Learn about complex vulnerability patterns
4. **Benchmarking**: Compare different security tools

### For WordPress Developers

1. **Security Awareness**: Understand advanced attack techniques
2. **Code Review**: Learn to identify sophisticated vulnerabilities
3. **Best Practices**: Study secure implementation patterns
4. **Testing**: Validate custom security implementations

## Maintenance

### Updating Test Cases

1. **Add New Patterns**: Include emerging attack techniques
2. **Update Existing Tests**: Reflect changes in attack patterns
3. **Remove Obsolete Tests**: Remove outdated vulnerability patterns
4. **Validate Accuracy**: Ensure test cases remain relevant

### Version Control

- **Test Versioning**: Version test cases with rule updates
- **Change Documentation**: Document test case modifications
- **Backward Compatibility**: Maintain compatibility with existing rules
- **Regression Testing**: Ensure updates don't break existing functionality

## Conclusion

The advanced test cases provide comprehensive coverage of sophisticated WordPress security vulnerabilities. They serve as a valuable resource for:

- **Rule Development**: Validating and improving security rules
- **Security Research**: Understanding advanced attack patterns
- **Tool Evaluation**: Testing security tool effectiveness
- **Education**: Learning about complex vulnerability scenarios

These test cases ensure that the WordPress Semgrep Rules project can detect and prevent even the most sophisticated security threats.
