# WordPress Semgrep Rules - Performance Testing Guide

## Overview

This guide explains how to use the comprehensive performance testing framework for WordPress Semgrep rules. The framework provides detailed analysis of scan times, memory usage, CPU utilization, and optimization recommendations.

## Features

### Core Capabilities
- **Multi-scenario Testing**: Test different project sizes (small, medium, large)
- **Configuration Comparison**: Compare performance across different rule configurations
- **Resource Monitoring**: Real-time monitoring of CPU, memory, and I/O usage
- **Statistical Analysis**: Comprehensive statistics with confidence intervals
- **Baseline Comparison**: Compare results against established baselines
- **Optimization Recommendations**: Automated suggestions for performance improvements
- **Visualization**: Generate charts and graphs for performance analysis

### Performance Metrics
- **Scan Time**: Total time to complete security scan
- **Memory Usage**: Peak and final memory consumption
- **CPU Utilization**: Average CPU usage during scan
- **Throughput**: Files processed per second
- **Success Rate**: Percentage of successful test runs
- **Resource Efficiency**: Memory and CPU usage per file

## Prerequisites

### Required Software
- **Python 3.7+**: Core runtime environment
- **Semgrep**: Security scanning engine
- **pip**: Python package manager

### Required Python Packages
```bash
pip install psutil matplotlib numpy pyyaml
```

### Optional Dependencies
- **jq**: JSON processing for result analysis (Bash script)
- **bc**: Basic calculator for statistical calculations (Bash script)

## Quick Start

### 1. Basic Performance Test
```bash
# PowerShell
.\tests\run-comprehensive-performance-test.ps1

# Bash
./tests/run-comprehensive-performance-test.sh
```

### 2. Comprehensive Test with Visualizations
```bash
# PowerShell
.\tests\run-comprehensive-performance-test.ps1 -Iterations 15 -Visualize -Optimize

# Bash
./tests/run-comprehensive-performance-test.sh --iterations 15 --visualize --optimize
```

### 3. Custom Configuration Test
```bash
# PowerShell
.\tests\run-comprehensive-performance-test.ps1 -Config custom-config.json -Output ./results

# Bash
./tests/run-comprehensive-performance-test.sh --config custom-config.json --output ./results
```

## Command Line Options

### Basic Options
| Option | Description | Default |
|--------|-------------|---------|
| `--config <file>` | Custom test configuration file | Built-in config |
| `--rules <path>` | Path to rules directory | `../packs/` |
| `--tests <path>` | Path to test files directory | `./` |
| `--output <path>` | Output directory for results | `./performance-results/` |
| `--iterations <int>` | Number of iterations per test | `10` |
| `--warmup <int>` | Number of warmup runs | `3` |

### Analysis Options
| Option | Description |
|--------|-------------|
| `--verbose` | Enable verbose output |
| `--json` | Output results in JSON format |
| `--html` | Generate HTML report |
| `--optimize` | Run optimization analysis |
| `--baseline` | Establish performance baseline |
| `--compare` | Compare against baseline |
| `--visualize` | Generate performance visualizations |

## Test Scenarios

### Small Project Scenario
- **Description**: Testing with small WordPress plugin codebase
- **Test Files**: Basic security examples (nonce, capability, sanitization)
- **Configurations**: `basic.yaml`, `strict.yaml`
- **Purpose**: Baseline performance measurement

### Medium Project Scenario
- **Description**: Testing with medium-sized WordPress plugin
- **Test Files**: All vulnerable and safe examples
- **Configurations**: `basic.yaml`, `strict.yaml`, `plugin-development.yaml`
- **Purpose**: Real-world plugin performance

### Large Project Scenario
- **Description**: Testing with large WordPress project
- **Test Files**: All test files including subdirectories
- **Configurations**: All available configurations
- **Purpose**: Enterprise-scale performance testing

## Performance Thresholds

### Default Targets
- **Scan Time**: < 30 seconds
- **Memory Usage**: < 500 MB
- **CPU Usage**: < 80%
- **Success Rate**: > 95%
- **Throughput**: > 0.1 files/second

### Customizing Thresholds
Create a custom configuration file to adjust thresholds:

```json
{
  "performance_thresholds": {
    "max_scan_time": 45.0,
    "max_memory_usage": 750.0,
    "max_cpu_percent": 90.0,
    "min_success_rate": 0.98,
    "min_throughput_files_per_second": 0.05
  }
}
```

## Output Files

### Generated Reports
- **`comprehensive-performance-report.json`**: Main performance report
- **`detailed-test-results.json`**: Individual test results
- **`optimization-report.json`**: Optimization analysis (if enabled)
- **`visualizations/`**: Performance charts and graphs

### Report Structure
```json
{
  "timestamp": "2025-01-09T12:00:00.000000",
  "duration": 120.5,
  "total_tests": 120,
  "successful_tests": 118,
  "failed_tests": 2,
  "test_summaries": [...],
  "performance_rankings": {...},
  "optimization_recommendations": [...],
  "baseline_comparison": {...},
  "performance_trends": {...}
}
```

## Performance Analysis

### Understanding Results

#### Scan Time Analysis
- **Mean/Median**: Central tendency of scan times
- **Standard Deviation**: Variability in performance
- **Min/Max**: Best and worst case scenarios
- **Percentiles**: Performance distribution

#### Memory Usage Analysis
- **Peak Memory**: Maximum memory consumption
- **Final Memory**: Memory usage after scan completion
- **Memory Efficiency**: Memory usage per file processed

#### Throughput Analysis
- **Files/Second**: Processing speed
- **Rules/Second**: Rule execution rate
- **Efficiency**: Resource usage per unit of work

### Performance Rankings
The framework automatically ranks configurations by:
1. **Fastest Configurations**: Lowest scan times
2. **Highest Throughput**: Most files processed per second
3. **Most Memory Efficient**: Lowest memory usage
4. **Best Resource Utilization**: Optimal CPU/memory balance

## Optimization Analysis

### Automatic Optimization
The optimization framework can:
- **Identify Bottlenecks**: Find performance-limiting rules
- **Rule Prioritization**: Rank rules by performance impact
- **Configuration Optimization**: Generate optimized configurations
- **Coverage Analysis**: Assess security coverage impact

### Optimization Strategies
1. **Rule Removal**: Remove low-priority, high-impact rules
2. **Pattern Simplification**: Optimize complex patterns
3. **Configuration Tuning**: Adjust rule combinations
4. **Caching Implementation**: Enable result caching

### Optimization Report
```json
{
  "timestamp": "2025-01-09T12:00:00.000000",
  "optimization_results": [...],
  "summary": {
    "total_configs_optimized": 4,
    "average_scan_time_improvement": 25.3,
    "average_memory_improvement": 15.7,
    "total_rules_removed": 12,
    "total_rules_optimized": 8
  }
}
```

## Visualization

### Available Charts
1. **Scan Time Comparison**: Bar chart comparing scan times across configurations
2. **Memory Usage Comparison**: Memory consumption analysis
3. **Throughput Comparison**: Processing speed comparison
4. **Performance Trends**: Time series analysis of performance metrics

### Chart Features
- **Threshold Lines**: Visual indicators of performance targets
- **Color Coding**: Green for acceptable, red for threshold violations
- **Interactive Elements**: Hover tooltips and zoom capabilities
- **Export Options**: PNG, PDF, and SVG formats

## Troubleshooting

### Common Issues

#### Python Package Installation
```bash
# Install required packages
pip install psutil matplotlib numpy pyyaml

# Verify installation
python -c "import psutil, matplotlib, numpy, yaml; print('All packages installed')"
```

#### Semgrep Not Found
```bash
# Install Semgrep
pip install semgrep

# Or use package manager
# macOS
brew install semgrep

# Ubuntu/Debian
wget -qO - https://semgrep.dev/rs/checksums.txt | grep -E "semgrep-v[0-9]+\.[0-9]+\.[0-9]+-ubuntu-16\.04\.tar\.gz" | head -1 | awk '{print $1}' | xargs -I {} wget -qO - https://github.com/returntocorp/semgrep/releases/download/v{}/semgrep-v{}-ubuntu-16.04.tar.gz | tar -xz
```

#### Permission Issues
```bash
# Make script executable (Bash)
chmod +x tests/run-comprehensive-performance-test.sh

# PowerShell execution policy
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

#### Memory Issues
- Reduce number of iterations: `--iterations 5`
- Use smaller test scenarios
- Increase system memory or use swap

### Performance Tips

#### System Optimization
1. **Close Unnecessary Applications**: Free up system resources
2. **Disable Antivirus Scanning**: Temporarily exclude test directories
3. **Use SSD Storage**: Faster file I/O operations
4. **Increase Available Memory**: More RAM for large scans

#### Test Optimization
1. **Use Warmup Runs**: Stabilize system performance
2. **Multiple Iterations**: Get statistically significant results
3. **Isolated Testing**: Run tests in clean environment
4. **Baseline Comparison**: Track performance over time

## Advanced Usage

### Custom Test Scenarios
Create custom test scenarios in configuration:

```json
{
  "test_scenarios": {
    "custom_scenario": {
      "name": "Custom Test Scenario",
      "description": "Custom performance test scenario",
      "test_files": [
        "path/to/test/files/*.php",
        "specific/test/file.php"
      ],
      "configs": ["basic.yaml", "custom-config.yaml"]
    }
  }
}
```

### Continuous Integration
Integrate performance testing into CI/CD pipelines:

```yaml
# GitHub Actions example
- name: Performance Test
  run: |
    ./tests/run-comprehensive-performance-test.sh \
      --iterations 5 \
      --output ./performance-results \
      --json
```

### Automated Monitoring
Set up automated performance monitoring:

```bash
# Daily performance check
0 2 * * * /path/to/project/tests/run-comprehensive-performance-test.sh --baseline --output /var/log/performance
```

## Best Practices

### Testing Strategy
1. **Establish Baselines**: Run baseline tests before major changes
2. **Regular Testing**: Schedule regular performance tests
3. **Regression Testing**: Test after rule updates
4. **Comparative Analysis**: Compare against previous results

### Result Interpretation
1. **Statistical Significance**: Use multiple iterations for reliable results
2. **Context Awareness**: Consider system load and environment
3. **Trend Analysis**: Look for performance trends over time
4. **Actionable Insights**: Focus on actionable optimization recommendations

### Performance Optimization
1. **Rule Complexity**: Simplify complex patterns
2. **Configuration Tuning**: Optimize rule combinations
3. **Caching Strategy**: Implement appropriate caching
4. **Resource Management**: Monitor and optimize resource usage

## Support and Resources

### Documentation
- [PRD Document](PRD-WordPress-Semgrep-Rules-Development.md)
- [Development Guide](DEVELOPMENT-GUIDE.md)
- [Performance Optimization Summary](../PERFORMANCE-OPTIMIZATION-SUMMARY.md)

### Tools and Scripts
- [Performance Test Script](../tests/comprehensive-performance-test.py)
- [Performance Optimizer](../tooling/performance-optimizer.py)
- [PowerShell Runner](../tests/run-comprehensive-performance-test.ps1)
- [Bash Runner](../tests/run-comprehensive-performance-test.sh)

### Community Resources
- [Semgrep Documentation](https://semgrep.dev/docs/)
- [WordPress Security Best Practices](https://developer.wordpress.org/plugins/security/)
- [Performance Testing Best Practices](https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/11-Client_Side_Testing/10-Business_Logic_Testing)

## Conclusion

The comprehensive performance testing framework provides powerful tools for analyzing and optimizing WordPress Semgrep rule performance. By following this guide, you can:

- Establish performance baselines
- Identify optimization opportunities
- Track performance improvements
- Ensure consistent performance across configurations
- Make data-driven optimization decisions

Regular performance testing is essential for maintaining optimal scanning performance while ensuring comprehensive security coverage.
