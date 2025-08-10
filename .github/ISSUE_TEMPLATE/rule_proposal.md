---
name: Rule proposal
about: Propose a new security rule for WordPress
title: '[RULE] '
labels: ['rule-proposal', 'enhancement']
assignees: ''
---

**Rule Category**
What type of security rule is this?
- [ ] Authorization (capability checks, role verification)
- [ ] CSRF (nonce verification, token validation)
- [ ] XSS (output escaping, input sanitization)
- [ ] SQL Injection (query preparation, input validation)
- [ ] File Operations (path validation, upload security)
- [ ] SSRF (URL validation, external request security)
- [ ] Deserialization (object injection prevention)
- [ ] REST API (endpoint security, authentication)
- [ ] AJAX (request validation, response security)
- [ ] Options/Settings (configuration security)
- [ ] Secrets Management (API key protection, credential security)
- [ ] Other (please specify)

**Vulnerability Description**
Describe the security vulnerability this rule would detect:

**WordPress Context**
How does this vulnerability specifically relate to WordPress?
- WordPress version(s) affected:
- WordPress functions/APIs involved:
- Common WordPress patterns that could be vulnerable:

**Attack Vector**
Describe how an attacker could exploit this vulnerability:

**Impact**
What is the potential impact of this vulnerability?
- [ ] Critical (remote code execution, data breach)
- [ ] High (privilege escalation, data manipulation)
- [ ] Medium (information disclosure, denial of service)
- [ ] Low (minor security issue)

**Detection Pattern**
Describe the code pattern that should trigger this rule:

```php
// Example vulnerable code pattern
<?php
// Your vulnerable code example here
?>
```

**Safe Pattern**
Describe the safe code pattern that should NOT trigger this rule:

```php
// Example safe code pattern
<?php
// Your safe code example here
?>
```

**CWE Mapping**
What CWE (Common Weakness Enumeration) does this relate to?
- Primary CWE: [e.g., CWE-79]
- Secondary CWE(s): [e.g., CWE-89]

**WordPress References**
Link to relevant WordPress documentation:
- [WordPress Developer Documentation](https://developer.wordpress.org/...)
- [WordPress Security Best Practices](https://developer.wordpress.org/plugins/security/)

**OWASP References**
Link to relevant OWASP resources:
- [OWASP Top Ten](https://owasp.org/www-project-top-ten/)
- [OWASP Cheat Sheet](https://cheatsheetseries.owasp.org/...)

**Test Cases**
Describe test cases that should be created:
- Vulnerable examples:
- Safe examples:
- Edge cases:

**Implementation Notes**
Any specific implementation considerations:
- Performance considerations:
- False positive risks:
- WordPress version compatibility:
- Plugin/theme specific considerations:

**Additional Context**
Any other relevant information about this rule proposal:

**Priority**
How important is this rule?
- [ ] Critical (should be implemented immediately)
- [ ] High (important for security coverage)
- [ ] Medium (good to have)
- [ ] Low (nice to have)

**Related Issues**
Link to any related issues or discussions:
