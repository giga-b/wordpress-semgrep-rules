# WordPress Semgrep Rules - Metrics Dashboard Guide

## Overview

The Metrics Dashboard is a comprehensive system for tracking and visualizing the performance of WordPress Semgrep security rules. It provides insights into rule effectiveness, false positive rates, scan performance, and trends over time.

## Features

### Core Metrics Tracking
- **Rule Performance**: Individual rule metrics including precision, recall, and F1 scores
- **Pack Performance**: Aggregated metrics for rule packs (wp-core-security, wp-core-quality, experimental)
- **Scan Performance**: Timing and memory usage statistics
- **False Positive Analysis**: Tracking and trending of false positive rates
- **Success Rates**: Rule execution success and failure tracking

### Visualization
- **Performance Trends**: Line charts showing scan duration over time
- **False Positive Trends**: Tracking false positive rates over time
- **Rule Performance Charts**: Bar charts of top-performing rules
- **Pack Performance**: Comparative performance across rule packs
- **Interactive Tables**: Sortable tables with detailed metrics

### Data Management
- **SQLite Database**: Persistent storage of metrics data
- **Automatic Collection**: Integration with existing test and performance systems
- **Data Retention**: Configurable retention policies
- **Export Capabilities**: JSON, CSV, and HTML export options

## Installation

### Prerequisites
- Python 3.7 or higher
- pip package manager
- Access to the WordPress Semgrep Rules project

### Dependencies
The dashboard requires the following Python packages:
```bash
matplotlib
seaborn
pandas
jinja2
pyyaml
```

### Quick Setup

#### Windows (PowerShell)
```powershell
# Navigate to project root
cd wordpress-semgrep-rules

# Install dependencies and run dashboard
.\tooling\run-metrics-dashboard.ps1 -InstallDependencies -CollectMetrics -GenerateDashboard -ServeDashboard
```

#### Unix/Linux/macOS (Bash)
```bash
# Navigate to project root
cd wordpress-semgrep-rules

# Make script executable
chmod +x tooling/run-metrics-dashboard.sh

# Install dependencies and run dashboard
./tooling/run-metrics-dashboard.sh --install-deps --collect-metrics --generate-dashboard --serve-dashboard
```

## Usage

### Command Line Options

#### PowerShell Script
```powershell
.\tooling\run-metrics-dashboard.ps1 [options]

Options:
    -CollectMetrics      Collect metrics from recent scans
    -GenerateDashboard   Generate HTML dashboard
    -ServeDashboard      Serve dashboard on local web server
    -Port <int>         Port for web server (default: 8080)
    -Config <file>      Configuration file path
    -Output <dir>       Output directory for dashboard files
    -UpdateInterval <int> Update interval in seconds
    -InstallDependencies Install required Python dependencies
    -OpenBrowser        Automatically open browser when serving
```

#### Bash Script
```bash
./tooling/run-metrics-dashboard.sh [options]

Options:
    --collect-metrics     Collect metrics from recent scans
    --generate-dashboard  Generate HTML dashboard
    --serve-dashboard     Serve dashboard on local web server
    --port PORT          Port for web server (default: 8080)
    --config FILE        Configuration file path
    --output DIR         Output directory for dashboard files
    --update-interval N  Update interval in seconds
    --install-deps       Install required Python dependencies
    --open-browser       Automatically open browser when serving
    --help               Show this help message
```

### Common Usage Patterns

#### 1. Initial Setup
```bash
# Install dependencies and collect initial metrics
./tooling/run-metrics-dashboard.sh --install-deps --collect-metrics --generate-dashboard
```

#### 2. View Dashboard
```bash
# Serve dashboard on default port (8080)
./tooling/run-metrics-dashboard.sh --serve-dashboard

# Serve on custom port with browser auto-open
./tooling/run-metrics-dashboard.sh --serve-dashboard --port 9000 --open-browser
```

#### 3. Update Metrics
```bash
# Collect new metrics and regenerate dashboard
./tooling/run-metrics-dashboard.sh --collect-metrics --generate-dashboard
```

#### 4. Full Workflow
```bash
# Complete workflow: install, collect, generate, and serve
./tooling/run-metrics-dashboard.sh --install-deps --collect-metrics --generate-dashboard --serve-dashboard --open-browser
```

## Configuration

### Configuration File
The dashboard uses a YAML configuration file (`tooling/metrics-config.yaml`) for customization:

```yaml
# Database configuration
database:
  path: "metrics.db"
  backup_enabled: true
  backup_interval_hours: 24
  max_backups: 7

# Data collection settings
collection:
  test_results:
    - path: "tests/test-results/automated-test-report.json"
      enabled: true
      update_interval_minutes: 30
  
  performance_reports:
    - path: "performance-optimization-report.json"
      enabled: true

# Dashboard configuration
dashboard:
  output_directory: "dashboard"
  update_interval_seconds: 300
  auto_refresh: true

# Metrics calculation settings
metrics:
  precision_recall:
    fallback:
      precision_estimate: 0.85
      recall_estimate: 0.90
      false_positive_rate: 0.15

# Alerting configuration
alerts:
  enabled: true
  thresholds:
    false_positive_rate:
      warning: 0.10
      critical: 0.20
    scan_time:
      warning: 30.0
      critical: 60.0
```

### Key Configuration Sections

#### Database Settings
- **path**: SQLite database file location
- **backup_enabled**: Enable automatic database backups
- **backup_interval_hours**: How often to create backups
- **max_backups**: Number of backup files to keep

#### Collection Settings
- **test_results**: Sources for test result data
- **performance_reports**: Sources for performance data
- **scan_results**: Sources for Semgrep scan results

#### Dashboard Settings
- **output_directory**: Where to save generated dashboard files
- **update_interval_seconds**: How often to refresh data
- **auto_refresh**: Enable automatic page refresh

#### Metrics Settings
- **precision_recall**: Configuration for precision/recall calculations
- **performance_scoring**: Weights and thresholds for performance scoring

#### Alerting Settings
- **enabled**: Enable alert notifications
- **thresholds**: Warning and critical thresholds for various metrics

## Dashboard Interface

### Main Dashboard Page
The admin dashboard is accessible via web browser at `http://localhost:8080/admin-dashboard.html` (or custom port).

#### Header Section
- Project title and timestamp
- Overall project status indicators

#### Metrics Grid
Key performance indicators displayed as cards:
- **Total Scans**: Number of rule executions
- **Total Findings**: Total vulnerabilities detected
- **False Positives**: Estimated false positive count
- **Overall Precision**: Percentage of accurate findings
- **Overall Recall**: Percentage of vulnerabilities detected
- **F1 Score**: Harmonic mean of precision and recall
- **Avg Scan Time**: Average time per scan
- **Total Scan Time**: Cumulative scan time

#### Charts Section
Interactive visualizations:
1. **Performance Trends**: Scan duration over time
2. **False Positive Trends**: False positive rate trends
3. **Rule Performance**: Top 10 rules by F1 score
4. **Pack Performance**: Performance scores by rule pack

#### Tables Section
Detailed data tables:
1. **Top Performing Rules**: Detailed metrics for best rules
2. **Rule Pack Performance**: Comparative pack statistics

### Interpreting Metrics

#### Rule Performance Metrics
- **F1 Score**: Overall rule effectiveness (0-1, higher is better)
- **Precision**: Accuracy of findings (0-1, higher is better)
- **Recall**: Coverage of vulnerabilities (0-1, higher is better)
- **Success Rate**: Percentage of successful rule executions
- **Avg Duration**: Average execution time per rule

#### Pack Performance Metrics
- **Performance Score**: Overall pack quality (0-100, higher is better)
- **False Positive Rate**: Percentage of incorrect findings
- **Active Rules**: Number of rules with recent activity
- **Total Scan Time**: Cumulative execution time

#### Performance Indicators
- **Green**: Excellent performance (score ≥ 80)
- **Yellow**: Good performance (score 60-79)
- **Red**: Poor performance (score < 60)

## Data Sources

### Automatic Collection
The dashboard automatically collects data from:

1. **Test Results**: `tests/test-results/automated-test-report.json`
2. **Performance Reports**: `performance-optimization-report.json`
3. **Semgrep Results**: `semgrep-results.json`
4. **Test Results**: `test-results.json`

### Manual Data Import
You can manually import data by placing JSON files in the expected locations or modifying the configuration file to point to custom data sources.

### Data Format
The dashboard expects data in the following format:

```json
{
  "timestamp": "2025-01-09T12:00:00.000000",
  "test_results": [
    {
      "test_file": "path/to/test.php",
      "rule_file": "path/to/rule.yaml",
      "rule_pack": "wp-core-security",
      "expected_findings": 5,
      "actual_findings": 5,
      "duration": 2.5,
      "status": "pass",
      "performance_metrics": {
        "memory_usage": 50.2
      }
    }
  ]
}
```

## Troubleshooting

### Common Issues

#### 1. Missing Dependencies
**Error**: `ModuleNotFoundError: No module named 'matplotlib'`
**Solution**: Install dependencies using the `--install-deps` flag

#### 2. No Data Sources Found
**Error**: "No data sources found. Dashboard will show empty data."
**Solution**: Run tests first to generate data files, or check file paths in configuration

#### 3. Port Already in Use
**Error**: `Address already in use`
**Solution**: Use a different port with `--port` option

#### 4. Permission Denied
**Error**: `Permission denied` on script execution
**Solution**: Make script executable: `chmod +x tooling/run-metrics-dashboard.sh`

#### 5. Database Locked
**Error**: `database is locked`
**Solution**: Close any other processes using the database, or delete and recreate the database file

### Debug Mode
Enable verbose output by modifying the Python script or checking the log file (`metrics-dashboard.log`).

### Log Files
The dashboard creates log files for debugging:
- **metrics-dashboard.log**: Main application log
- **metrics.db**: SQLite database file
- **dashboard/**: Generated dashboard files

## Integration

### CI/CD Integration
The dashboard can be integrated into CI/CD pipelines:

```yaml
# GitHub Actions example
- name: Generate Metrics Dashboard
  run: |
    ./tooling/run-metrics-dashboard.sh --collect-metrics --generate-dashboard
    
- name: Upload Dashboard Artifact
  uses: actions/upload-artifact@v2
  with:
    name: metrics-dashboard
    path: dashboard/
```

### IDE Integration
The dashboard can be launched from IDEs by configuring the runner scripts as external tools.

### Automated Monitoring
Set up automated monitoring by:
1. Creating a cron job to collect metrics regularly
2. Configuring alerts for performance thresholds
3. Setting up automated dashboard updates

## Advanced Features

### Custom Metrics
Define custom metrics in the configuration file:

```yaml
custom_metrics:
  definitions:
    - name: "security_coverage"
      description: "Percentage of security rules that found vulnerabilities"
      calculation: "true_positives / total_security_rules"
      unit: "percentage"
```

### Alerting
Configure alerts for performance issues:

```yaml
alerts:
  enabled: true
  email:
    enabled: true
    smtp_server: "smtp.gmail.com"
    recipients: ["admin@example.com"]
  thresholds:
    false_positive_rate:
      warning: 0.10
      critical: 0.20
```

### Data Export
Export metrics data in various formats:

```bash
# Export to JSON
python tooling/metrics_dashboard.py --export-json metrics.json

# Export to CSV
python tooling/metrics_dashboard.py --export-csv metrics.csv
```

## Best Practices

### Regular Maintenance
1. **Monitor Performance**: Check dashboard regularly for performance trends
2. **Review False Positives**: Investigate high false positive rates
3. **Update Rules**: Use metrics to identify rules needing improvement
4. **Backup Data**: Ensure database backups are working
5. **Clean Old Data**: Remove outdated metrics data

### Performance Optimization
1. **Limit Data Retention**: Configure appropriate retention periods
2. **Optimize Queries**: Use database indexes for large datasets
3. **Batch Processing**: Collect metrics in batches for efficiency
4. **Caching**: Enable caching for frequently accessed data

### Security Considerations
1. **Access Control**: Limit dashboard access to authorized users
2. **Data Privacy**: Ensure sensitive data is not exposed
3. **Network Security**: Use HTTPS in production environments
4. **Input Validation**: Validate all configuration inputs

## Support

### Getting Help
1. Check the troubleshooting section above
2. Review log files for error details
3. Verify configuration file syntax
4. Test with minimal configuration

### Contributing
To contribute to the metrics dashboard:
1. Follow the project's coding standards
2. Add tests for new features
3. Update documentation
4. Submit pull requests with detailed descriptions

### Reporting Issues
When reporting issues, include:
1. Operating system and version
2. Python version
3. Error messages and log files
4. Steps to reproduce the issue
5. Configuration file contents (if relevant)

## Conclusion

The Metrics Dashboard provides comprehensive insights into WordPress Semgrep rule performance, enabling data-driven improvements to security scanning effectiveness. Regular use of the dashboard helps maintain high-quality security rules and optimal scanning performance.
