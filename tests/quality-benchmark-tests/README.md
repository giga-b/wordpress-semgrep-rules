# WordPress Semgrep Rules Quality Benchmarks

This directory contains a comprehensive testing system for validating the quality and accuracy of WordPress Semgrep security rules against industry-standard benchmarks.

## Overview

The quality benchmark system consists of **100 test files** with varying levels of security vulnerabilities, designed to test rule accuracy, precision, and recall. The system ensures that all rules meet strict industry-level quality standards:

- **Precision**: ≥95% (≤5% false positive rate)
- **Recall**: ≥95% (≤5% false negative rate)
- **Test Coverage**: 100% of test files
- **Baseline Stability**: ≥99%

## Test File Structure

### Category 1: Safe Files (Files 1-20)
- **Count**: 20 files
- **Expected Findings**: 0
- **Purpose**: Establish false positive baseline
- **Complexity**: Low

### Category 2: Single Flaw Files (Files 21-45)
- **Count**: 25 files
- **Expected Findings**: 1 per file
- **Purpose**: Test basic detection accuracy
- **Complexity**: Low

### Category 3: Multiple Flaw Files (Files 46-80)
- **Count**: 35 files
- **Expected Findings**: 2-4 per file
- **Purpose**: Test comprehensive detection
- **Complexity**: Medium

### Category 4: Complex Flaw Files (Files 81-100)
- **Count**: 20 files
- **Expected Findings**: 5-7 per file
- **Purpose**: Test advanced detection capabilities
- **Complexity**: High to Very High

## Vulnerability Types Covered

- **XSS (Cross-Site Scripting)**: ~60 instances
- **SQL Injection**: ~50 instances
- **CSRF (Cross-Site Request Forgery)**: ~45 instances
- **Authorization Issues**: ~40 instances
- **File Upload Vulnerabilities**: ~35 instances
- **Deserialization Issues**: ~20 instances
- **Secrets Storage**: ~20 instances
- **Path Traversal**: ~15 instances
- **Dynamic Execution**: ~15 instances

## Expected Results

- **Total Expected Findings**: 255
- **Safe Files**: 0 findings
- **Vulnerable Files**: 255 findings across 80 files

## Quick Start

### Prerequisites

1. **Python 3.7+** installed and in PATH
2. **Semgrep** installed and in PATH
3. **WordPress Semgrep Rules** project cloned

### Running Benchmarks

#### Option 1: Using Scripts (Recommended)

**Windows:**
```cmd
cd tests\quality-benchmark-tests
run-benchmarks.bat
```

**PowerShell:**
```powershell
cd tests\quality-benchmark-tests
.\run-benchmarks.ps1
```

#### Option 2: Manual Execution

```bash
cd tests/quality-benchmark-tests
python run-quality-benchmarks.py /path/to/project/root
```

#### Option 3: Test Specific Configurations

```bash
python run-quality-benchmarks.py /path/to/project/root basic.yaml strict.yaml
```

### Output

The benchmark system generates:

1. **JSON Results**: Detailed scan results and metrics
2. **Markdown Report**: Human-readable summary report
3. **Console Output**: Real-time progress and results

## Quality Metrics

### Precision
```
Precision = TP / (TP + FP)
Target: ≥95%
```
Measures the accuracy of positive findings (low false positive rate).

### Recall (Detection Rate)
```
Recall = TP / (TP + FN)
Target: ≥95%
```
Measures the ability to detect actual vulnerabilities (low false negative rate).

### F1 Score
```
F1 = 2 × (Precision × Recall) / (Precision + Recall)
```
Balanced measure of precision and recall.

### False Positive Rate
```
FPR = FP / (TP + FP)
Target: ≤5%
```
Rate of incorrect positive findings.

### False Negative Rate
```
FNR = FN / (TP + FN)
Target: ≤5%
```
Rate of missed vulnerabilities.

## File Organization

```
quality-benchmark-tests/
├── README.md                           # This file
├── TEST_FILE_REGISTRY.md              # Complete registry of all test files
├── TEST_SUMMARY.md                    # Summary of generated test files
├── generate_test_files.py             # Script to generate test files
├── run-quality-benchmarks.py          # Main benchmark runner
├── run-benchmarks.bat                 # Windows batch script
├── run-benchmarks.ps1                 # PowerShell script
├── results/                           # Benchmark results directory
│   ├── quality-benchmark-results-*.json
│   └── quality-benchmark-report-*.md
└── *.php                              # 100 test files
```

## Test File Examples

### Safe File Example
```php
<?php
// safe-basic-functions.php
function safe_function() {
    // Proper capability check
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions.'));
    }
    
    // Safe output with proper escaping
    $safe_data = sanitize_text_field($_POST['data'] ?? '');
    echo esc_html($safe_data);
    
    return true;
}
```

### Vulnerable File Example
```php
<?php
// xss-basic-output.php
function vulnerable_function() {
    // VULNERABILITY: XSS - Direct output without escaping
    $user_input = $_GET['user_input'] ?? '';
    echo $user_input; // VULNERABLE: Should use esc_html()
    
    return true;
}
```

## Interpreting Results

### Pass Criteria
- **Precision**: ≥95%
- **Recall**: ≥95%
- **FP Rate**: ≤5%
- **FN Rate**: ≤5%

### Fail Criteria
- Any metric below target threshold
- Scan errors or timeouts
- Analysis failures

### Example Output
```
basic.yaml: PASS
  Precision: 0.9600 (Target: ≥0.95)
  Recall: 0.9700 (Target: ≥0.95)
  FP Rate: 0.0400 (Target: ≤0.05)
  FN Rate: 0.0300 (Target: ≤0.05)
```

## Troubleshooting

### Common Issues

1. **Semgrep Not Found**
   - Install Semgrep: `pip install semgrep`
   - Ensure it's in your PATH

2. **Python Not Found**
   - Install Python 3.7+
   - Ensure it's in your PATH

3. **Scan Timeouts**
   - Increase timeout in `run-quality-benchmarks.py`
   - Check system resources

4. **Memory Issues**
   - Reduce batch size of test files
   - Run with fewer configurations at once

### Debug Mode

For detailed debugging, modify the benchmark runner:

```python
# In run-quality-benchmarks.py
cmd = [
    "semgrep",
    "--config", str(config_path),
    "--json",
    "--verbose",  # Add verbose output
    "--debug"     # Add debug output
]
```

## Customization

### Adding New Test Files

1. Add file definition to `generate_test_files.py`
2. Run `python generate_test_files.py`
3. Update `TEST_FILE_REGISTRY.md`

### Modifying Quality Targets

Edit the quality targets in `run-quality-benchmarks.py`:

```python
self.quality_targets = {
    "precision_min": 0.95,      # 95%
    "recall_min": 0.95,         # 95%
    "fp_rate_max": 0.05,        # 5%
    "fn_rate_max": 0.05,        # 5%
    "test_coverage_min": 1.0,   # 100%
    "baseline_stability_min": 0.99  # 99%
}
```

### Testing Specific Vulnerability Types

Filter test files by vulnerability type:

```python
# In run-quality-benchmarks.py
xss_files = [f for f in test_files if 'xss' in f.lower()]
sqli_files = [f for f in test_files if 'sqli' in f.lower()]
```

## Performance Considerations

### Scan Time
- **Small files**: ~1-2 seconds each
- **Medium files**: ~2-5 seconds each
- **Large files**: ~5-15 seconds each
- **Total expected time**: 5-15 minutes for all files

### Memory Usage
- **Per file**: ~10-50MB
- **Total**: ~1-2GB for full scan
- **Recommendation**: 4GB+ available RAM

### Optimization Tips
1. Run scans during low system usage
2. Close unnecessary applications
3. Use SSD storage for faster I/O
4. Increase system page file if needed

## Integration

### CI/CD Pipeline

Add to your GitHub Actions workflow:

```yaml
- name: Run Quality Benchmarks
  run: |
    cd tests/quality-benchmark-tests
    python run-quality-benchmarks.py ${{ github.workspace }}
  
- name: Check Quality Targets
  run: |
    # Parse results and fail if targets not met
    python check-quality-targets.py
```

### Pre-commit Hooks

Add quality checks to pre-commit:

```yaml
- repo: local
  hooks:
    - id: quality-benchmarks
      name: Quality Benchmarks
      entry: python tests/quality-benchmark-tests/run-quality-benchmarks.py
      language: system
      pass_filenames: false
```

## Support

### Documentation
- [Quality Benchmarks Guide](../docs/QUALITY_BENCHMARKS.md)
- [Rule Development Guide](../docs/RULE_DEVELOPMENT.md)
- [Testing Strategy](../docs/TESTING_STRATEGY.md)

### Issues
- Report bugs via GitHub Issues
- Include benchmark results and error logs
- Provide system information and versions

### Contributing
- Follow the testing guidelines
- Add new test cases for edge cases
- Improve benchmark accuracy

## License

This testing system is part of the WordPress Semgrep Rules project and follows the same license terms.

---

**Note**: This quality benchmark system is designed to ensure enterprise-grade security rule quality. All rules must pass these benchmarks before being promoted to production use.
