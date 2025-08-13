# Rule Completion Checklist

## ✅ Rule Completion Checklist: `rules/<class>/<rule-id>.yaml`

### Metadata Requirements
- [ ] **Complete metadata**:
  - [ ] `id` - Unique rule identifier
  - [ ] `message` - Clear, actionable message
  - [ ] `severity` - Appropriate level (INFO, WARNING, ERROR)
  - [ ] `confidence: high` - Must be high for production rules
  - [ ] `cwe` - Relevant CWE identifier
  - [ ] `category` - Security category
  - [ ] `tags` - Relevant tags for classification
  - [ ] `vuln_class` - Vulnerability class (xss, sqli, csrf, authz, file_upload, deserialization, secrets_storage, rest_ajax, other)

### Testing Requirements
- [ ] **Tests present and passing**:
  - [ ] ≥1 positive test (should trigger the rule)
  - [ ] ≥1 negative test (should not trigger the rule)
  - [ ] ≥1 edge case test (boundary conditions)
  - [ ] `semgrep --test` returns green
  - [ ] All test cases documented with expected behavior

### Quality Targets (Ideal Goals)
- [ ] **Precision ≥ 95%** (true-positive percentage)
- [ ] **False positive rate ≤ 5%**
- [ ] **Recall (detection rate) ≥ 95%** (or class-specific target if higher/lower)
- [ ] **False negative rate ≤ 5%**
- [ ] **Baseline stability ≥ 99%** (no regressions across versions)
- [ ] **Autofix safety ≥ 95%** (if autofix exists)

### Corpus Validation
- [ ] **Corpus testing completed**:
  - [ ] Rule tested against attack corpus
  - [ ] Minimum corpus findings met (≥5 for experimental, ≥10 for core)
  - [ ] False positive analysis completed
  - [ ] Performance impact acceptable

### Documentation
- [ ] **Documentation complete**:
  - [ ] Rule purpose clearly documented
  - [ ] Vulnerability class explained
  - [ ] False positive scenarios documented
  - [ ] Mitigation strategies provided
  - [ ] Examples of vulnerable and safe code

### Performance Requirements
- [ ] **Performance benchmarks met**:
  - [ ] Rule execution time within acceptable limits
  - [ ] Memory usage optimized
  - [ ] No significant impact on overall scan performance

### Promotion Criteria
- [ ] **Ready for promotion**:
  - [ ] Rule is categorized (`wp-core` / `project-specific`)
  - [ ] All quality gates passed
  - [ ] Code review completed
  - [ ] Security review completed (if applicable)

---

## Class-Specific Targets

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

---

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

---

## Rule Promotion Workflow

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

---

## Automated Quality Gates

The project includes automated quality gates that enforce these benchmarks:

```bash
# Run quality gates on all rules
python tests/quality-gates.py

# Run quality gates on specific rules
python tests/quality-gates.py --rules packs/wp-core-security/xss-prevention.yaml

# Run with custom project root
python tests/quality-gates.py --project-root /path/to/project
```

Quality gates check:
- [ ] Precision and recall targets
- [ ] False positive/negative rates
- [ ] Test coverage requirements
- [ ] Corpus validation
- [ ] Confidence levels
- [ ] Performance benchmarks

---

## Continuous Integration

Quality gates are automatically enforced in CI/CD:

```yaml
# .github/workflows/quality-gates.yml
name: Quality Gates
on: [push, pull_request]
jobs:
  quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Run Quality Gates
        run: python tests/quality-gates.py
```

This ensures all rules meet the benchmark requirements before merging.
