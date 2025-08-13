# Quality Benchmarks for WordPress Semgrep Rules

This document outlines the quality benchmarks and standards that all WordPress Semgrep rules must meet before being promoted to production.

## Overview

The WordPress Semgrep Rules project maintains strict quality standards to ensure high accuracy, low false positives, and comprehensive vulnerability detection. All rules must meet specific benchmarks before being promoted from experimental to core, and from core to production.

## Global Quality Targets

All rules must meet these minimum standards:

| Metric | Target | Description |
|--------|--------|-------------|
| **Precision** | ≥95% | True positive percentage (TP/(TP+FP)) |
| **False Positive Rate** | ≤5% | False positives relative to all findings |
| **Recall (Detection Rate)** | ≥95% | True positives relative to all actual vulnerabilities |
| **False Negative Rate** | ≤5% | Missed vulnerabilities relative to all actual vulnerabilities |
| **Test Coverage** | 100% | At least one positive and one negative test |
| **Baseline Stability** | ≥99% | No regressions across versions |
| **Autofix Safety Rate** | ≥95% | Only when autofix exists |
| **Rule Confidence** | high | Must be high before promotion |

## Vulnerability Class-Specific Targets

Different vulnerability classes have specific targets based on their inherent complexity:

### XSS (Cross-Site Scripting)
- **Precision**: ≥95%
- **Recall**: ≥90%
- **Context Awareness**: Must distinguish HTML, attribute, JS, URL contexts
- **Escaping**: Enforce proper escaping per sink

### SQL Injection
- **Precision**: ≥95%
- **Recall**: ≥95%
- **Coverage**: Catch `$wpdb->prepare` misuse, raw queries, concatenation
- **Taint Analysis**: Taint to query sinks

### CSRF / Nonce
- **Precision**: ≥95%
- **Recall**: ≥95%
- **Lifecycle**: Full nonce lifecycle (create, print, verify)
- **Coverage**: admin-post, AJAX, REST endpoints

### Authorization (AuthZ)
- **Precision**: ≥92%
- **Recall**: ≥90%
- **Checks**: `current_user_can` checks, REST `permission_callback`
- **Capability Mapping**: Proper capability validation

### File Upload / Path Traversal
- **Precision**: ≥95%
- **Recall**: ≥90%
- **Functions**: `move_uploaded_file`, `unzip_file`
- **Validation**: Extension/MIME validation, canonicalization

### Deserialization / Dynamic Exec
- **Precision**: ≥95%
- **Recall**: ≥95%
- **Functions**: `unserialize`, `maybe_unserialize`, `eval`, `create_function`

### Secrets in Options/Meta
- **Precision**: ≥95%
- **Recall**: ≥95%
- **Storage**: `add_option`/`update_option` on sensitive keys
- **Transport**: Secure storage and transport

### REST/AJAX Endpoint Hardening
- **Precision**: ≥95%
- **Recall**: ≥95%
- **Handlers**: `wp_ajax_*`/`wp_ajax_nopriv_*`, `register_rest_route`
- **Permissions**: Proper permission validation

## Quality Metrics Formulas

### Precision
```
Precision = TP / (TP + FP)
```
- **Target**: ≥95% (≥92% for authz)

### Recall (Detection Rate)
```
Recall = TP / (TP + FN)
```
- **Target**: ≥95% (≥90% for xss, authz, file_upload)

### False Positive Rate
```
FPR = FP / (TP + FP)
```
- **Target**: ≤5%

### False Negative Rate
```
FNR = FN / (TP + FN)
```
- **Target**: ≤5%

### Baseline Stability
```
Stability = (Consistent Results) / (Total Scans)
```
- **Target**: ≥99%

### Autofix Safety Rate
```
Safety Rate = (Safe Autofixes) / (Total Autofixes)
```
- **Target**: ≥95%

## Rule Promotion Criteria

### Experimental → Core
- [ ] Minimum 10 test cases
- [ ] Minimum 5 corpus findings
- [ ] Maximum 2 false positives
- [ ] High confidence required
- [ ] Code review completed

### Core → Production
- [ ] Minimum 20 test cases
- [ ] Minimum 10 corpus findings
- [ ] Maximum 1 false positive
- [ ] High confidence required
- [ ] Performance benchmark passed
- [ ] Documentation complete
- [ ] Security review completed

## Rule Metadata Requirements

All rules must include the following metadata:

```yaml
metadata:
  confidence: "high"           # Required: low, medium, high
  cwe: "CWE-79"               # Required: CWE identifier
  category: "xss-prevention"   # Required: rule category
  vuln_class: "xss"           # Required: vulnerability class
  tags: ["xss", "security"]   # Required: relevant tags
  description: "..."          # Optional: detailed description
```

### Valid Vulnerability Classes
- `xss` - Cross-site scripting
- `sqli` - SQL injection
- `csrf` - CSRF/nonce vulnerabilities
- `authz` - Authorization issues
- `file_upload` - File upload/path traversal
- `deserialization` - Unsafe deserialization
- `secrets_storage` - Secrets in options/meta
- `rest_ajax` - REST/AJAX endpoint security
- `other` - Other security issues

## Testing Requirements

### Test Coverage
- **Positive Tests**: At least 1 test that should trigger the rule
- **Negative Tests**: At least 1 test that should not trigger the rule
- **Edge Case Tests**: At least 1 test for boundary conditions
- **Performance Tests**: Required for all rules
- **Regression Tests**: Required for all rules

### Test Validation
```bash
# Run tests for a specific rule
semgrep --test packs/wp-core-security/xss-prevention.yaml

# Run all tests
semgrep --test packs/wp-core-security/
```

## Automated Quality Gates

The project includes automated quality gates that enforce these benchmarks:

### Running Quality Gates
```bash
# Run quality gates on all rules
python tests/quality-gates.py

# Run quality gates on specific rules
python tests/quality-gates.py --rules packs/wp-core-security/xss-prevention.yaml

# Run with custom project root
python tests/quality-gates.py --project-root /path/to/project
```

### Running Benchmarks
```bash
# Run benchmarks on all rules
python tests/benchmark-testing.py

# Run benchmarks on specific rules
python tests/benchmark-testing.py --rules packs/wp-core-security/xss-prevention.yaml
```

### Validating Rule Metadata
```bash
# Validate all rule metadata
python tests/validate-rule-metadata.py

# Validate specific rules
python tests/validate-rule-metadata.py --rules packs/wp-core-security/xss-prevention.yaml
```

## Continuous Integration

Quality gates are automatically enforced in CI/CD through GitHub Actions:

### Workflow Jobs
1. **Quality Gates**: Enforces benchmark requirements
2. **Rule Validation**: Validates rule metadata and structure
3. **Performance Benchmarks**: Measures rule performance
4. **Corpus Validation**: Tests rules against attack corpus
5. **Security Review**: Performs security analysis
6. **Final Validation**: Comprehensive validation and reporting

### CI Configuration
The quality gates are configured in `.github/workflows/quality-gates.yml` and automatically run on:
- All pushes to `main` and `develop` branches
- All pull requests to `main` and `develop` branches

## Quality Control Configuration

The quality benchmarks are defined in `.rule-quality.yml`:

```yaml
global_targets:
  precision_min: 0.95
  recall_min: 0.95
  fp_rate_max: 0.05
  fn_rate_max: 0.05
  test_coverage_min: 1.0
  baseline_stability_min: 0.99
  autofix_safety_min: 0.95
  require_confidence: "high"

class_targets:
  xss:
    precision_min: 0.95
    recall_min: 0.90
  sqli:
    precision_min: 0.95
    recall_min: 0.95
  # ... other classes
```

## Rule Completion Checklist

Use the checklist in `tests/rule-completion-checklist.md` to ensure all requirements are met:

- [ ] Metadata complete with all required fields
- [ ] Tests present and passing
- [ ] Quality targets met on corpus
- [ ] Baseline stability achieved
- [ ] Autofix safety verified (if applicable)
- [ ] Rule categorized and ready for promotion

## Performance Benchmarks

Rules must meet performance requirements:

- **Scan Time**: ≤30 seconds per rule on standard corpus
- **Memory Usage**: ≤100MB per rule
- **CPU Usage**: ≤50% on single core
- **Scalability**: Linear performance with corpus size

## Reporting and Monitoring

### Quality Reports
Quality gates generate comprehensive reports including:
- Overall pass/fail status
- Per-rule metrics and targets
- Performance statistics
- Recommendations for improvement

### Monitoring
- Track quality metrics over time
- Monitor for regressions
- Alert on quality degradation
- Generate trend analysis

## Best Practices

### Rule Development
1. Start with experimental rules
2. Test against diverse corpus
3. Iterate based on feedback
4. Document false positives/negatives
5. Optimize for performance

### Quality Assurance
1. Use automated testing
2. Validate against real-world code
3. Review findings manually
4. Document edge cases
5. Monitor performance impact

### Continuous Improvement
1. Regular benchmark reviews
2. Update targets based on findings
3. Improve test coverage
4. Optimize rule performance
5. Enhance documentation

## Troubleshooting

### Common Issues
- **Low Precision**: Too many false positives
- **Low Recall**: Missing actual vulnerabilities
- **Performance Issues**: Rule too complex or inefficient
- **Test Failures**: Incomplete test coverage

### Solutions
- Refine rule patterns
- Add more test cases
- Optimize rule logic
- Review corpus findings
- Update documentation

## Support

For questions about quality benchmarks:
- Review this documentation
- Check the rule completion checklist
- Run automated quality gates
- Consult the development team
- Review existing rule examples
