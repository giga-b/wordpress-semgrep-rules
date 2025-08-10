# Troubleshooting Guide

This guide helps you resolve common issues when using the WordPress Semgrep Security Rules.

## 🔍 Common Issues

### Issue: No files are being scanned

**Symptoms**: Semgrep reports "Nothing to scan" or "0 files scanned"

**Possible Causes**:
1. Files are being ignored by `.semgrepignore`
2. Files are not tracked by git (when using `--only-git-tracked`)
3. File extensions are not supported
4. Path is incorrect

**Solutions**:
```bash
# Check if files are being ignored
semgrep scan --config=packs/wp-core-security/ --verbose

# Scan without git tracking restrictions
semgrep scan --config=packs/wp-core-security/ --no-git-ignore

# Check file extensions
semgrep scan --config=packs/wp-core-security/ --include="*.php"

# Verify path
ls -la /path/to/your/wordpress-project
```

### Issue: Too many false positives

**Symptoms**: Rules are flagging legitimate WordPress code as vulnerable

**Common False Positive Sources**:
1. WordPress core functions used correctly
2. Third-party library calls
3. Custom sanitization functions
4. Well-known safe patterns

**Solutions**:
```bash
# Use basic configuration for fewer false positives
semgrep scan --config=configs/basic.yaml /path/to/code

# Exclude specific directories
semgrep scan --config=packs/wp-core-security/ --exclude="vendor/" --exclude="node_modules/"

# Add custom ignore patterns to .semgrepignore
echo "vendor/" >> .semgrepignore
echo "node_modules/" >> .semgrepignore
```

### Issue: Rules not detecting expected vulnerabilities

**Symptoms**: Known vulnerable code is not being flagged

**Possible Causes**:
1. Using wrong configuration
2. Rule patterns need updating
3. Code pattern doesn't match rule
4. Rule is disabled in configuration

**Solutions**:
```bash
# Use strict configuration for maximum coverage
semgrep scan --config=configs/strict.yaml /path/to/code

# Test against known vulnerable examples
semgrep scan --config=packs/wp-core-security/ tests/vulnerable-examples/

# Check rule status
semgrep scan --config=packs/wp-core-security/ --verbose
```

### Issue: Performance problems

**Symptoms**: Scanning takes too long or uses too much memory

**Solutions**:
```bash
# Use basic configuration for faster scanning
semgrep scan --config=configs/basic.yaml /path/to/code

# Limit file size
semgrep scan --config=packs/wp-core-security/ --max-target-bytes=1000000

# Exclude large directories
semgrep scan --config=packs/wp-core-security/ --exclude="vendor/" --exclude="node_modules/"

# Use parallel processing
semgrep scan --config=packs/wp-core-security/ --jobs=4
```

## 🛠️ Configuration Issues

### Issue: Configuration file not found

**Error**: `Could not find config file: configs/basic.yaml`

**Solutions**:
```bash
# Check if configuration files exist
ls -la configs/

# Use absolute path
semgrep scan --config=/full/path/to/configs/basic.yaml /path/to/code

# Use pack directly
semgrep scan --config=packs/wp-core-security/ /path/to/code
```

### Issue: Invalid configuration format

**Error**: `Invalid YAML format in configuration file`

**Solutions**:
```bash
# Validate YAML syntax
python -c "import yaml; yaml.safe_load(open('configs/basic.yaml'))"

# Check for syntax errors
yamllint configs/basic.yaml

# Use a different configuration
semgrep scan --config=packs/wp-core-security/ /path/to/code
```

## 🔧 Rule-Specific Issues

### Nonce Verification Issues

**Issue**: Nonce rules not working as expected

**Common Problems**:
1. Nonce field name is different from expected
2. Action name doesn't match pattern
3. Nonce verification is in different function

**Solutions**:
```bash
# Check nonce rule patterns
cat packs/wp-core-security/nonce-verification.yaml

# Test with specific nonce rule
semgrep scan --config=packs/wp-core-security/nonce-verification.yaml /path/to/code
```

### Capability Check Issues

**Issue**: Capability rules flagging legitimate admin code

**Common Problems**:
1. Admin user checks not recognized
2. Custom capability names not understood
3. Role checks instead of capability checks

**Solutions**:
```bash
# Check capability rule patterns
cat packs/wp-core-security/capability-checks.yaml

# Use more specific capability checks
if (current_user_can('manage_options')) {
    // Admin operation
}
```

### Sanitization Issues

**Issue**: Sanitization rules not detecting unsafe code

**Common Problems**:
1. Custom sanitization functions not recognized
2. Output escaping in different functions
3. Database queries using custom methods

**Solutions**:
```bash
# Check sanitization rule patterns
cat packs/wp-core-security/sanitization-functions.yaml

# Use WordPress sanitization functions
$data = sanitize_text_field($_POST['input']);
echo esc_html($data);
```

## 📊 Performance Optimization

### Scanning Large Projects

**Best Practices**:
```bash
# Use incremental scanning
semgrep scan --config=configs/basic.yaml --baseline-commit=HEAD~1

# Exclude unnecessary directories
semgrep scan --config=packs/wp-core-security/ \
  --exclude="vendor/" \
  --exclude="node_modules/" \
  --exclude=".git/" \
  --exclude="tests/"

# Use parallel processing
semgrep scan --config=packs/wp-core-security/ --jobs=$(nproc)
```

### CI/CD Optimization

**GitHub Actions Example**:
```yaml
- name: Security Scan
  run: |
    semgrep scan \
      --config=configs/basic.yaml \
      --json \
      --output semgrep-results.json \
      --error-on-findings
```

## 🐛 Debugging

### Enable Verbose Output

```bash
# Get detailed scanning information
semgrep scan --config=packs/wp-core-security/ --verbose

# Check rule parsing
semgrep scan --config=packs/wp-core-security/ --debug

# Show skipped files
semgrep scan --config=packs/wp-core-security/ --verbose | grep "Skipped"
```

### Check Rule Status

```bash
# List all rules in configuration
semgrep scan --config=packs/wp-core-security/ --list-rules

# Check rule metadata
semgrep scan --config=packs/wp-core-security/ --list-rules --json
```

### Validate Test Cases

```bash
# Test vulnerable examples
semgrep scan --config=packs/wp-core-security/ tests/vulnerable-examples/ --json

# Test safe examples
semgrep scan --config=packs/wp-core-security/ tests/safe-examples/ --json

# Compare results
diff <(semgrep scan --config=packs/wp-core-security/ tests/vulnerable-examples/ --json | jq '.results | length') <(echo "35")
```

## 📞 Getting Help

### Before Asking for Help

1. **Check this troubleshooting guide**
2. **Test with known examples**:
   ```bash
   semgrep scan --config=packs/wp-core-security/ tests/vulnerable-examples/
   ```
3. **Check Semgrep version**:
   ```bash
   semgrep --version
   ```
4. **Verify configuration**:
   ```bash
   semgrep scan --config=packs/wp-core-security/ --list-rules
   ```

### When Reporting Issues

Include the following information:
- Semgrep version
- Configuration used
- Command executed
- Expected vs actual results
- Code examples (if applicable)
- Error messages (if any)

### Useful Commands for Debugging

```bash
# Check Semgrep installation
semgrep --version

# Validate configuration
semgrep scan --config=packs/wp-core-security/ --list-rules

# Test with minimal configuration
semgrep scan --config=packs/wp-core-security/nonce-verification.yaml tests/vulnerable-examples/

# Check file parsing
semgrep scan --config=packs/wp-core-security/ --verbose tests/vulnerable-examples/nonce-vulnerable.php
```

---

**Last Updated**: January 2025  
**Semgrep Version**: 1.45.0+  
**WordPress Version**: 6.0+
