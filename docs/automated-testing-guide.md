# WordPress Semgrep Rules - Automated Testing Guide

## Overview

This guide explains how to use the comprehensive automated testing system for the WordPress Semgrep Rules project. The system includes test execution, regression testing, performance benchmarking, and result reporting.

## System Components

### 1. Automated Test Runner (`run-automated-tests.py`)
- **Purpose**: Executes comprehensive tests against all rule packs and test files
- **Features**: 
  - Test execution with expected results validation
  - Performance metrics collection
  - JSON and HTML report generation
  - Configurable test suites

### 2. Regression Testing Framework (`regression-tests.py`)
- **Purpose**: Compares current test results against baseline results
- **Features**:
  - Detects performance regressions
  - Identifies findings regressions
  - Severity-based analysis
  - Trend tracking

### 3. Performance Benchmarking (`performance-benchmarks.py`)
- **Purpose**: Measures performance metrics across different configurations
- **Features**:
  - Memory usage monitoring
  - CPU usage tracking
  - Multiple iterations with warmup
  - Performance rankings

### 4. Master Test Runner Scripts
- **PowerShell**: `run-tests.ps1` (Windows)
- **Bash**: `run-tests.sh` (Unix/Linux/macOS)
- **Purpose**: Orchestrates all testing components

## Quick Start

### Prerequisites

1. **Python 3.7+**: Required for all testing scripts
2. **Semgrep**: Must be installed and available in PATH
3. **Dependencies**: Automatically installed by the test runner

### Basic Usage

#### Windows (PowerShell)
```powershell
# Run all tests
.\tests\run-tests.ps1 -Mode all -Verbose

# Run only automated tests
.\tests\run-tests.ps1 -Mode tests -Html

# Run performance benchmarks
.\tests\run-tests.ps1 -Mode performance -Verbose
```

#### Unix/Linux/macOS (Bash)
```bash
# Make scripts executable (first time only)
chmod +x tests/run-tests.sh tests/*.py

# Run all tests
./tests/run-tests.sh -m all -v

# Run only automated tests
./tests/run-tests.sh -m tests -h

# Run performance benchmarks
./tests/run-tests.sh -m performance -v
```

## Test Modes

### 1. `all` - Complete Testing Suite
Runs all testing components:
- Automated tests
- Performance benchmarks
- Regression tests (if baseline/current files provided)

### 2. `tests` - Automated Tests Only
Runs comprehensive test execution against all rule packs and test files.

### 3. `regression` - Regression Testing
Compares current results against baseline results to detect regressions.

### 4. `performance` - Performance Benchmarks
Runs performance testing across different configurations and scenarios.

### 5. `quick` - Quick Tests
Runs a subset of tests for rapid validation during development.

## Configuration

### Test Configuration File (`test-config.json`)

The test configuration file defines:
- Test suites and their expected results
- Performance thresholds
- Test file mappings
- Reporting options

#### Example Configuration Structure
```json
{
  "semgrep_binary": "semgrep",
  "rules_path": "../packs/",
  "tests_path": "./",
  "test_suites": {
    "basic_security": {
      "name": "Basic Security Rules",
      "test_files": ["vulnerable-examples/nonce-vulnerable.php"],
      "rule_packs": ["wp-core-security"],
      "expected_results": {
        "vulnerable-examples/nonce-vulnerable.php": {
          "nonce-verification.yaml": 3
        }
      }
    }
  }
}
```

### Custom Configuration

Create a custom configuration file:
```json
{
  "semgrep_binary": "semgrep",
  "rules_path": "/path/to/rules",
  "tests_path": "/path/to/tests",
  "iterations": 5,
  "timeout": 180
}
```

Use with test runner:
```bash
./tests/run-tests.sh -c custom-config.json -m all
```

## Test Suites

### 1. Basic Security Rules
Tests core WordPress security patterns:
- Nonce verification
- Capability checks
- Sanitization functions

### 2. Advanced Security Rules
Tests advanced security patterns:
- REST API security
- AJAX endpoint security
- SQL injection prevention
- XSS prevention

### 3. Taint Analysis Rules
Tests advanced taint analysis:
- Data flow analysis
- SQL injection taint detection
- XSS taint detection

### 4. Edge Case Testing
Tests complex vulnerability patterns:
- Advanced obfuscation
- Complex attack vectors
- File operation vulnerabilities

## Performance Testing

### Performance Metrics

The system tracks:
- **Scan Duration**: Time to complete each scan
- **Memory Usage**: Peak and final memory consumption
- **CPU Usage**: Average CPU utilization
- **Findings Count**: Number of security findings detected

### Performance Thresholds

Default thresholds (configurable):
- **Max Scan Time**: 30 seconds
- **Max Memory Usage**: 500MB
- **Max CPU Percent**: 80%
- **Min Success Rate**: 95%

### Performance Scenarios

1. **Small Test**: Quick validation (5s expected)
2. **Medium Test**: Comprehensive testing (15s expected)
3. **Large Test**: Full project scan (60s expected)

## Regression Testing

### Baseline Creation

Create a baseline for regression testing:
```bash
# Run tests and save as baseline
./tests/run-tests.sh -m tests -o baseline-results/

# Copy results as baseline
cp baseline-results/automated-test-report.json baseline-results.json
```

### Regression Analysis

Compare current results against baseline:
```bash
./tests/run-tests.sh -m regression -b baseline-results.json -u current-results.json
```

### Regression Types

1. **Findings Regression**: Fewer security findings detected
2. **Performance Regression**: Increased scan time or memory usage
3. **Success Rate Regression**: Lower test success rate

## Output and Reporting

### Output Directory Structure
```
test-results/
├── automated-test-report.json
├── automated-test-report.html
├── performance-benchmark-report.json
├── performance-benchmark-report.html
├── regression-report.json
├── regression-report.html
└── test-summary.json
```

### Report Types

#### JSON Reports
- Machine-readable format
- Detailed test results
- Performance metrics
- Recommendations

#### HTML Reports
- Human-readable format
- Color-coded results
- Interactive elements
- Visual charts (when available)

### Report Contents

#### Test Results
- Test file and rule combinations
- Expected vs actual findings
- Test duration and status
- Error messages (if any)

#### Performance Metrics
- Duration statistics (mean, median, std dev)
- Memory usage patterns
- CPU utilization
- Performance rankings

#### Regression Analysis
- Baseline vs current comparison
- Regression severity levels
- Performance degradation analysis
- Recommendations for improvement

## Advanced Usage

### Custom Test Execution

Run individual test components:
```bash
# Run automated tests directly
python tests/run-automated-tests.py --config test-config.json --verbose

# Run regression analysis
python tests/regression-tests.py --baseline baseline.json --current current.json --html

# Run performance benchmarks
python tests/performance-benchmarks.py --iterations 10 --warmup 3
```

### Continuous Integration

#### GitHub Actions Example
```yaml
name: Automated Testing
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Set up Python
        uses: actions/setup-python@v2
        with:
          python-version: '3.9'
      - name: Install Semgrep
        run: |
          python -m pip install semgrep
      - name: Run Tests
        run: |
          chmod +x tests/run-tests.sh
          ./tests/run-tests.sh -m all -v
      - name: Upload Results
        uses: actions/upload-artifact@v2
        with:
          name: test-results
          path: test-results/
```

### Custom Test Development

#### Adding New Test Cases

1. Create test files in appropriate directories:
   - `tests/vulnerable-examples/` for vulnerable code
   - `tests/safe-examples/` for secure code

2. Update configuration file with expected results:
```json
{
  "test_suites": {
    "new_suite": {
      "test_files": ["vulnerable-examples/new-vulnerability.php"],
      "expected_results": {
        "vulnerable-examples/new-vulnerability.php": {
          "new-rule.yaml": 2
        }
      }
    }
  }
}
```

#### Adding New Rule Packs

1. Create rule files in `packs/` directory
2. Update configuration to include new rule packs
3. Add expected results for test files

## Troubleshooting

### Common Issues

#### Python Not Found
```bash
# Install Python 3.7+
# Windows: Download from python.org
# macOS: brew install python3
# Ubuntu: sudo apt install python3
```

#### Semgrep Not Found
```bash
# Install Semgrep
pip install semgrep
# or
pip3 install semgrep
```

#### Permission Denied
```bash
# Make scripts executable
chmod +x tests/*.py tests/*.sh
```

#### Test Failures

1. **Check Semgrep Installation**: Ensure semgrep is working
2. **Verify Test Files**: Ensure test files exist and are accessible
3. **Check Configuration**: Validate JSON configuration syntax
4. **Review Expected Results**: Update expected findings counts if rules changed

### Debug Mode

Enable verbose output for debugging:
```bash
./tests/run-tests.sh -m all -v
```

### Log Files

Check for detailed logs in output directory:
- `test-results/` contains all output files
- JSON files contain detailed error information
- HTML reports show visual error indicators

## Best Practices

### 1. Regular Testing
- Run tests before committing changes
- Use CI/CD for automated testing
- Establish baseline results for regression testing

### 2. Performance Monitoring
- Track performance trends over time
- Set up alerts for performance regressions
- Optimize slow configurations

### 3. Test Maintenance
- Update expected results when rules change
- Add new test cases for new vulnerabilities
- Remove obsolete test cases

### 4. Documentation
- Document new test cases
- Update configuration when adding rules
- Maintain troubleshooting guides

## Integration with Development Workflow

### Pre-commit Testing
```bash
# Quick validation before commit
./tests/run-tests.sh -m quick
```

### Pull Request Testing
```bash
# Full test suite for PR validation
./tests/run-tests.sh -m all -v -h
```

### Release Testing
```bash
# Comprehensive testing for releases
./tests/run-tests.sh -m all -v -h
# Regression testing against previous release
./tests/run-tests.sh -m regression -b previous-release.json -u current-release.json
```

## Support and Contributing

### Getting Help
1. Check this documentation
2. Review test output files
3. Enable verbose mode for debugging
4. Check GitHub issues for known problems

### Contributing
1. Follow existing test patterns
2. Update documentation when adding features
3. Ensure all tests pass before submitting
4. Add appropriate test cases for new rules

## Conclusion

The automated testing system provides comprehensive validation for WordPress Semgrep Rules, ensuring:
- Rule accuracy and effectiveness
- Performance optimization
- Regression detection
- Quality assurance

Regular use of this system helps maintain high-quality security rules and prevents regressions in rule effectiveness and performance.
