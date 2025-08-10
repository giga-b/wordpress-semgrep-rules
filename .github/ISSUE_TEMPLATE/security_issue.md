---
name: Security issue
about: Report a security vulnerability in the WordPress Semgrep Rules project
title: '[SECURITY] '
labels: ['security', 'confidential']
assignees: ''
---

**⚠️ IMPORTANT: Security Issue Reporting**

This template is for reporting security vulnerabilities in the WordPress Semgrep Rules project itself. For security issues in WordPress plugins/themes that you want to detect with our rules, please use the [Rule proposal](?template=rule_proposal.md) template instead.

**Vulnerability Type**
What type of security issue is this?
- [ ] Rule bypass (rule can be evaded)
- [ ] False negative (rule misses vulnerabilities)
- [ ] False positive (rule triggers on safe code)
- [ ] Configuration vulnerability
- [ ] Tooling vulnerability
- [ ] Documentation vulnerability
- [ ] Other (please specify)

**Severity**
What is the severity of this vulnerability?
- [ ] Critical (immediate fix required)
- [ ] High (fix needed soon)
- [ ] Medium (fix needed)
- [ ] Low (fix when convenient)

**Affected Components**
Which parts of the project are affected?
- [ ] Security rules
- [ ] Configuration files
- [ ] Testing infrastructure
- [ ] Documentation
- [ ] Tooling scripts
- [ ] VS Code extension
- [ ] Cursor extension
- [ ] Other (please specify)

**Vulnerability Description**
Describe the security vulnerability:

**Impact**
What is the potential impact of this vulnerability?

**Steps to Reproduce**
1. Step 1
2. Step 2
3. Step 3

**Proof of Concept**
Provide a minimal example that demonstrates the vulnerability:

```php
// Example code that demonstrates the vulnerability
<?php
// Your PoC code here
?>
```

**Expected Behavior**
What should happen (secure behavior):

**Actual Behavior**
What actually happens (vulnerable behavior):

**Environment**
- Semgrep Version: [e.g., 1.45.0]
- Configuration: [e.g., basic.yaml, strict.yaml]
- Operating System: [e.g., Windows 10, Ubuntu 20.04]
- Python Version: [e.g., 3.9.7]

**Affected Versions**
Which versions of the project are affected?
- [ ] All versions
- [ ] Specific version(s): [list versions]
- [ ] Version range: [e.g., 1.0.0 to 1.2.0]

**Mitigation**
If known, describe any workarounds or mitigations:

**Suggested Fix**
If you have suggestions for fixing this issue:

**Disclosure Timeline**
- [ ] I agree to responsible disclosure
- [ ] I can wait for a coordinated fix
- [ ] I need immediate public disclosure

**Additional Information**
Any other relevant information about this security issue:

**Contact Information**
If you prefer private communication, provide your contact information:
- Email: [optional]
- PGP Key: [optional]

---

**⚠️ Security Response Process**

1. **Acknowledgment**: You'll receive acknowledgment within 48 hours
2. **Assessment**: Security team will assess the issue
3. **Fix Development**: Fix will be developed if confirmed
4. **Testing**: Fix will be thoroughly tested
5. **Release**: Fix will be released with appropriate disclosure
6. **Credit**: You'll be credited in security advisories

**For Immediate Security Issues**
If this is a critical security issue requiring immediate attention, please also email: security@project-domain.com
