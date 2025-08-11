# Cross-File Analysis Usage Guide

## Overview

This guide explains how to use cross-file analysis with the WordPress Semgrep Rules project.

## Basic Usage

### Running Cross-File Analysis Scans

**Scan entire project:**
```bash
semgrep ci --config packs/wp-core-security/nonce-lifecycle-detection.yaml
```

**Scan specific files:**
```bash
semgrep ci --config packs/wp-core-security/nonce-lifecycle-detection.yaml path/to/file.php
```

**Scan with custom output:**
```bash
semgrep ci --config packs/wp-core-security/nonce-lifecycle-detection.yaml --json --output results.json
```

### Understanding Results

Cross-file analysis provides enhanced detection by:

1. **Cross-File Pattern Matching**: Detects patterns that span multiple files
2. **Semantic Analysis**: Understands relationships between functions and variables
3. **Framework Awareness**: Better understanding of WordPress patterns
4. **Reduced False Positives**: More accurate detection through context

### Rule Categories

The nonce lifecycle detection rules are categorized by:

- **Creation**: Nonce generation patterns
- **Inclusion**: Nonce field placement in forms/AJAX
- **Verification**: Nonce validation patterns
- **Expiration**: Error handling for expired/invalid nonces
- **Cross-File**: Patterns that span multiple files

## Advanced Usage

### Custom Rule Development

Create custom cross-file rules:

```yaml
- id: custom.cross-file.rule
  message: "Custom cross-file vulnerability detected"
  severity: ERROR
  languages: [php]
  options:
    interfile: true
  pattern: |
    # Your pattern here
```

### Integration with CI/CD

Add to your CI pipeline:

```yaml
# GitHub Actions example
- name: Run Semgrep Cross-File Analysis
  run: |
    semgrep ci --config packs/wp-core-security/nonce-lifecycle-detection.yaml
```

### Performance Optimization

For large codebases:

```bash
# Increase timeout for large files
semgrep ci --timeout 45 --config packs/wp-core-security/nonce-lifecycle-detection.yaml

# Use parallel processing
semgrep ci -j 4 --config packs/wp-core-security/nonce-lifecycle-detection.yaml
```

## Comparison: Cross-File vs Single-File Analysis

| Feature | Single-File | Cross-File |
|---------|-------------|------------|
| **Detection Scope** | Within single file | Across multiple files |
| **False Positives** | Higher | Lower |
| **Performance** | Faster | Slower but more accurate |
| **Setup Complexity** | Simple | Requires account setup |
| **Framework Support** | Basic | Advanced |

## Best Practices

1. **Start with Single-File**: Use single-file analysis for quick scans
2. **Use Cross-File for Deep Analysis**: Use cross-file analysis for comprehensive security audits
3. **Combine Both Approaches**: Use both for maximum coverage
4. **Regular Testing**: Run cross-file analysis regularly in your CI/CD pipeline
5. **Custom Rules**: Develop custom rules for your specific security needs

## Troubleshooting

### Performance Issues

- Increase timeout values for large files
- Use parallel processing with `-j` flag
- Consider scanning specific directories instead of entire project

### Accuracy Issues

- Review false positives and adjust rule patterns
- Use the Python test scripts to validate rule behavior
- Check Semgrep documentation for pattern optimization

### Integration Issues

- Ensure proper authentication with `semgrep login`
- Verify cross-file analysis is enabled in web interface
- Check network connectivity for cloud-based analysis
