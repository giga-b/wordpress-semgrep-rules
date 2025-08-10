# WordPress Semgrep Rules - Production User Guide

## Overview

This guide is designed for production users who want to integrate WordPress Semgrep Rules into their development workflows, CI/CD pipelines, and security practices. It provides practical, step-by-step instructions for real-world usage scenarios.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Basic Usage](#basic-usage)
3. [Advanced Configuration](#advanced-configuration)
4. [Integration Scenarios](#integration-scenarios)
5. [Best Practices](#best-practices)
6. [Troubleshooting](#troubleshooting)
7. [Reference](#reference)

## Getting Started

### Prerequisites

Before using WordPress Semgrep Rules in production, ensure you have:

- **Python 3.8+** installed and accessible
- **Git** for version control
- **Access to your WordPress project** (plugin, theme, or custom code)
- **Basic understanding** of WordPress security concepts

### Quick Installation

#### Option 1: Direct Installation (Recommended)
```bash
# Clone the repository
git clone https://github.com/giga-b/wordpress-semgrep-rules.git
cd wordpress-semgrep-rules

# Create virtual environment
python3 -m venv .venv
source .venv/bin/activate  # Linux/macOS
# or
.venv\Scripts\Activate.ps1  # Windows

# Install dependencies
pip install -r requirements.txt
pip install semgrep>=1.45.0
```

#### Option 2: Using Docker
```bash
# Pull the Docker image
docker pull giga-b/wordpress-semgrep-rules:latest

# Run a scan
docker run -v $(pwd):/workspace giga-b/wordpress-semgrep-rules \
    semgrep scan --config=configs/basic.yaml /workspace
```

#### Option 3: Using Package Manager
```bash
# Install via pip (when available)
pip install wordpress-semgrep-rules

# Or install directly from GitHub
pip install git+https://github.com/giga-b/wordpress-semgrep-rules.git
```

### First Scan

Run your first security scan:

```bash
# Basic security scan
semgrep scan --config=configs/basic.yaml /path/to/your/wordpress-project

# Plugin development scan
semgrep scan --config=configs/plugin-development.yaml /path/to/your/plugin

# Comprehensive security audit
semgrep scan --config=configs/strict.yaml /path/to/your/project --json --output results.json
```

## Basic Usage

### Understanding Configuration Types

The project provides three main configuration types:

#### 1. Basic Configuration (`configs/basic.yaml`)
**Use for**: Quick security checks, CI/CD integration
**Features**:
- Essential WordPress security rules
- Fast scanning (< 30 seconds)
- Low false positive rate
- Suitable for automated workflows

```bash
# Quick security check
semgrep scan --config=configs/basic.yaml your-project/
```

#### 2. Plugin Development Configuration (`configs/plugin-development.yaml`)
**Use for**: WordPress plugin development
**Features**:
- Plugin-specific security patterns
- AJAX and REST API security
- WordPress hook security
- Balanced performance and coverage

```bash
# Plugin development scan
semgrep scan --config=configs/plugin-development.yaml your-plugin/
```

#### 3. Strict Configuration (`configs/strict.yaml`)
**Use for**: Security audits, compliance checks
**Features**:
- Comprehensive security coverage
- Advanced vulnerability detection
- Quality and performance rules
- Detailed reporting

```bash
# Comprehensive security audit
semgrep scan --config=configs/strict.yaml your-project/ --json --output audit-results.json
```

### Output Formats

#### JSON Output (Recommended for CI/CD)
```bash
semgrep scan --config=configs/basic.yaml your-project/ --json --output results.json
```

#### HTML Report
```bash
semgrep scan --config=configs/basic.yaml your-project/ --html --output report.html
```

#### SARIF Format (For IDE Integration)
```bash
semgrep scan --config=configs/basic.yaml your-project/ --sarif --output results.sarif
```

### Understanding Results

#### Result Structure
```json
{
  "results": [
    {
      "check_id": "wordpress.security.nonce.missing",
      "path": "includes/admin.php",
      "start": {"line": 45, "col": 5},
      "end": {"line": 45, "col": 25},
      "extra": {
        "message": "Missing nonce verification for form submission",
        "severity": "ERROR",
        "metadata": {
          "category": "security",
          "cwe": "CWE-352"
        }
      }
    }
  ],
  "errors": [],
  "paths": {
    "scanned": ["your-project/"],
    "skipped": []
  }
}
```

#### Severity Levels
- **ERROR**: Critical security issues requiring immediate attention
- **WARNING**: Security concerns that should be addressed
- **INFO**: Informational findings and best practices

## Advanced Configuration

### Custom Configuration

Create a custom configuration for your specific needs:

```yaml
# custom-config.yaml
rules:
  # Include core WordPress security rules
  - include: packs/wp-core-security/
  
  # Include quality rules
  - include: packs/wp-core-quality/
  
  # Exclude experimental rules for production
  - exclude: packs/experimental/

# Scanning options
scanning:
  timeout: 120
  max_memory: 4096
  parallel: true
  incremental: true

# Reporting options
reporting:
  format: json
  output: reports/
  severity: warning
  include_patterns:
    - "*.php"
  exclude_patterns:
    - "vendor/"
    - "node_modules/"
    - "tests/"
```

### Environment-Specific Configurations

#### Development Environment
```yaml
# configs/dev.yaml
rules:
  - include: packs/wp-core-security/
  - include: packs/wp-core-quality/

scanning:
  timeout: 60
  max_memory: 2048

reporting:
  format: json
  severity: info
```

#### Staging Environment
```yaml
# configs/staging.yaml
rules:
  - include: packs/wp-core-security/
  - include: packs/wp-core-quality/
  - include: packs/experimental/

scanning:
  timeout: 120
  max_memory: 4096

reporting:
  format: json
  severity: warning
```

#### Production Environment
```yaml
# configs/prod.yaml
rules:
  - include: packs/wp-core-security/
  - include: packs/wp-core-quality/

scanning:
  timeout: 300
  max_memory: 8192
  incremental: true

reporting:
  format: json
  severity: error
  alerting: true
```

### Rule Customization

#### Excluding Specific Rules
```yaml
# Exclude specific rules by ID
rules:
  - include: packs/wp-core-security/
  - exclude:
    - wordpress.security.nonce.missing
    - wordpress.security.capability.missing
```

#### Custom Rule Severity
```yaml
# Override rule severity
rules:
  - include: packs/wp-core-security/
  - rules:
    - id: wordpress.security.nonce.missing
      severity: ERROR
    - id: wordpress.security.capability.missing
      severity: WARNING
```

## Integration Scenarios

### Scenario 1: WordPress Plugin Development

#### Setup
```bash
# Navigate to your plugin directory
cd /path/to/your-wordpress-plugin

# Create a security scanning script
cat > scan-security.sh << 'EOF'
#!/bin/bash
# Security scanning for WordPress plugin

echo "Running security scan..."

# Run basic security scan
semgrep scan --config=configs/plugin-development.yaml . \
    --json --output security-results.json

# Check for critical issues
if jq -e '.results[] | select(.extra.severity == "ERROR")' security-results.json > /dev/null; then
    echo "❌ Critical security issues found!"
    exit 1
else
    echo "✅ No critical security issues found"
fi
EOF

chmod +x scan-security.sh
```

#### Pre-commit Hook
```bash
# .git/hooks/pre-commit
#!/bin/bash

echo "Running security scan..."

# Run security scan on staged files
git diff --cached --name-only --diff-filter=ACM | grep '\.php$' | while read file; do
    semgrep scan --config=configs/plugin-development.yaml "$file" --json --output /tmp/pre-commit-results.json
    
    if jq -e '.results[] | select(.extra.severity == "ERROR")' /tmp/pre-commit-results.json > /dev/null; then
        echo "❌ Security issues found in $file"
        exit 1
    fi
done

echo "✅ Security scan passed"
```

### Scenario 2: WordPress Theme Development

#### Theme Security Configuration
```yaml
# theme-security.yaml
rules:
  # Include core security rules
  - include: packs/wp-core-security/
  
  # Include theme-specific rules
  - include: packs/wp-core-quality/
  
  # Exclude plugin-specific rules
  - exclude: packs/experimental/

scanning:
  timeout: 90
  max_memory: 3072

reporting:
  format: json
  output: security-reports/
  severity: warning
  include_patterns:
    - "*.php"
    - "*.css"
    - "*.js"
  exclude_patterns:
    - "node_modules/"
    - "vendor/"
    - "build/"
```

#### Automated Theme Testing
```bash
#!/bin/bash
# test-theme-security.sh

THEME_DIR="/path/to/your-theme"
REPORT_DIR="security-reports"

echo "Testing theme security..."

# Create report directory
mkdir -p "$REPORT_DIR"

# Run security scan
semgrep scan --config=theme-security.yaml "$THEME_DIR" \
    --json --output "$REPORT_DIR/theme-security-$(date +%Y%m%d).json"

# Generate HTML report
semgrep scan --config=theme-security.yaml "$THEME_DIR" \
    --html --output "$REPORT_DIR/theme-security-$(date +%Y%m%d).html"

echo "Security scan completed. Reports saved to $REPORT_DIR/"
```

### Scenario 3: WordPress Agency Workflow

#### Multi-Project Scanning
```python
# agency_scanner.py
#!/usr/bin/env python3

import os
import subprocess
import json
from pathlib import Path
from datetime import datetime

class AgencyScanner:
    def __init__(self, projects_dir, config_path):
        self.projects_dir = Path(projects_dir)
        self.config_path = config_path
        self.reports_dir = Path("agency-reports")
        self.reports_dir.mkdir(exist_ok=True)
    
    def scan_project(self, project_path):
        """Scan a single project"""
        project_name = project_path.name
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        
        # Create project report directory
        project_report_dir = self.reports_dir / project_name
        project_report_dir.mkdir(exist_ok=True)
        
        # Run security scan
        json_output = project_report_dir / f"security-{timestamp}.json"
        html_output = project_report_dir / f"security-{timestamp}.html"
        
        try:
            # JSON scan
            subprocess.run([
                "semgrep", "scan",
                "--config", self.config_path,
                "--json", "--output", str(json_output),
                str(project_path)
            ], check=True)
            
            # HTML scan
            subprocess.run([
                "semgrep", "scan",
                "--config", self.config_path,
                "--html", "--output", str(html_output),
                str(project_path)
            ], check=True)
            
            return {
                "project": project_name,
                "status": "success",
                "json_report": str(json_output),
                "html_report": str(html_output)
            }
            
        except subprocess.CalledProcessError as e:
            return {
                "project": project_name,
                "status": "error",
                "error": str(e)
            }
    
    def scan_all_projects(self):
        """Scan all projects in the directory"""
        results = []
        
        for project_path in self.projects_dir.iterdir():
            if project_path.is_dir() and not project_path.name.startswith('.'):
                print(f"Scanning {project_path.name}...")
                result = self.scan_project(project_path)
                results.append(result)
        
        return results
    
    def generate_summary_report(self, results):
        """Generate summary report"""
        summary = {
            "scan_date": datetime.now().isoformat(),
            "total_projects": len(results),
            "successful_scans": len([r for r in results if r["status"] == "success"]),
            "failed_scans": len([r for r in results if r["status"] == "error"]),
            "projects": results
        }
        
        summary_file = self.reports_dir / f"summary-{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        with open(summary_file, 'w') as f:
            json.dump(summary, f, indent=2)
        
        return summary_file

if __name__ == "__main__":
    scanner = AgencyScanner("/path/to/projects", "configs/strict.yaml")
    results = scanner.scan_all_projects()
    summary_file = scanner.generate_summary_report(results)
    print(f"Scan completed. Summary saved to {summary_file}")
```

### Scenario 4: CI/CD Integration

#### GitHub Actions Workflow
```yaml
# .github/workflows/security-scan.yml
name: Security Scan

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  security-scan:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Set up Python
      uses: actions/setup-python@v4
      with:
        python-version: '3.11'
    
    - name: Install Semgrep
      run: |
        pip install semgrep>=1.45.0
        pip install -r requirements.txt
    
    - name: Run Security Scan
      run: |
        semgrep scan --config=configs/strict.yaml \
          --json --output semgrep-results.json \
          --error-on-findings
    
    - name: Upload Results
      uses: actions/upload-artifact@v3
      with:
        name: security-results
        path: semgrep-results.json
    
    - name: Comment Results
      if: github.event_name == 'pull_request'
      uses: actions/github-script@v6
      with:
        script: |
          const fs = require('fs');
          const results = JSON.parse(fs.readFileSync('semgrep-results.json', 'utf8'));
          
          const errorCount = results.results.filter(r => r.extra.severity === 'ERROR').length;
          const warningCount = results.results.filter(r => r.extra.severity === 'WARNING').length;
          
          const comment = `## Security Scan Results
          
          🔍 **Scan completed successfully**
          
          - ❌ **Errors**: ${errorCount}
          - ⚠️ **Warnings**: ${warningCount}
          
          ${errorCount > 0 ? '🚨 **Critical security issues detected!**' : '✅ **No critical issues found**'}
          
          [Download full results](https://github.com/${{ github.repository }}/actions/runs/${{ github.run_id }}/artifacts)`;
          
          github.rest.issues.createComment({
            issue_number: context.issue.number,
            owner: context.repo.owner,
            repo: context.repo.repo,
            body: comment
          });
```

#### GitLab CI Pipeline
```yaml
# .gitlab-ci.yml
stages:
  - security

security-scan:
  stage: security
  image: python:3.11-slim
  before_script:
    - pip install semgrep>=1.45.0
    - pip install -r requirements.txt
  script:
    - semgrep scan --config=configs/strict.yaml --json --output semgrep-results.json
  artifacts:
    reports:
      semgrep: semgrep-results.json
    paths:
      - semgrep-results.json
  rules:
    - if: $CI_PIPELINE_SOURCE == "merge_request_event"
    - if: $CI_COMMIT_BRANCH == $CI_DEFAULT_BRANCH
```

### Scenario 5: IDE Integration

#### VS Code Integration
```json
// .vscode/settings.json
{
  "semgrep.enabled": true,
  "semgrep.config": "./configs/plugin-development.yaml",
  "semgrep.autoScan": true,
  "semgrep.severity": "warning",
  "semgrep.maxProblems": 100
}
```

#### Cursor Integration
```json
// .cursorrules
{
  "security": {
    "semgrep": {
      "enabled": true,
      "config": "./configs/plugin-development.yaml",
      "autoScan": true
    }
  }
}
```

## Best Practices

### 1. Scanning Strategy

#### Incremental Scanning
```bash
# Scan only changed files
git diff --name-only HEAD~1 | grep '\.php$' | xargs semgrep scan --config=configs/basic.yaml

# Scan staged files only
git diff --cached --name-only | grep '\.php$' | xargs semgrep scan --config=configs/basic.yaml
```

#### Performance Optimization
```bash
# Use parallel processing
semgrep scan --config=configs/basic.yaml --jobs 4 your-project/

# Exclude unnecessary directories
semgrep scan --config=configs/basic.yaml \
  --exclude-dir=vendor \
  --exclude-dir=node_modules \
  --exclude-dir=tests \
  your-project/
```

### 2. Result Management

#### Automated Result Processing
```python
# result_processor.py
import json
import sys
from pathlib import Path

def process_results(results_file):
    """Process and categorize scan results"""
    with open(results_file) as f:
        results = json.load(f)
    
    # Categorize by severity
    errors = [r for r in results['results'] if r['extra']['severity'] == 'ERROR']
    warnings = [r for r in results['results'] if r['extra']['severity'] == 'WARNING']
    info = [r for r in results['results'] if r['extra']['severity'] == 'INFO']
    
    # Generate summary
    summary = {
        'total_findings': len(results['results']),
        'errors': len(errors),
        'warnings': len(warnings),
        'info': len(info),
        'files_scanned': len(results['paths']['scanned']),
        'critical_issues': [e for e in errors if 'critical' in e['extra']['message'].lower()]
    }
    
    return summary

def should_fail_build(summary):
    """Determine if build should fail based on results"""
    return summary['errors'] > 0 or len(summary['critical_issues']) > 0

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python result_processor.py <results.json>")
        sys.exit(1)
    
    summary = process_results(sys.argv[1])
    print(json.dumps(summary, indent=2))
    
    if should_fail_build(summary):
        print("❌ Build should fail due to security issues")
        sys.exit(1)
    else:
        print("✅ Build can proceed")
```

#### Result Filtering
```bash
# Filter results by severity
jq '.results[] | select(.extra.severity == "ERROR")' results.json

# Filter results by file
jq '.results[] | select(.path | contains("admin.php"))' results.json

# Count findings by category
jq '.results | group_by(.extra.metadata.category) | map({category: .[0].extra.metadata.category, count: length})' results.json
```

### 3. Security Workflow

#### Pre-deployment Security Check
```bash
#!/bin/bash
# pre-deploy-security.sh

echo "Running pre-deployment security check..."

# Run comprehensive security scan
semgrep scan --config=configs/strict.yaml . \
    --json --output pre-deploy-results.json

# Check for critical issues
CRITICAL_ISSUES=$(jq '.results[] | select(.extra.severity == "ERROR")' pre-deploy-results.json)

if [ -n "$CRITICAL_ISSUES" ]; then
    echo "❌ Critical security issues found:"
    echo "$CRITICAL_ISSUES"
    exit 1
else
    echo "✅ No critical security issues found"
fi

# Generate security report
semgrep scan --config=configs/strict.yaml . \
    --html --output pre-deploy-report.html

echo "Security check completed. Report saved to pre-deploy-report.html"
```

#### Security Review Process
```bash
#!/bin/bash
# security-review.sh

echo "Starting security review process..."

# 1. Run security scan
semgrep scan --config=configs/strict.yaml . \
    --json --output security-review.json

# 2. Generate detailed report
semgrep scan --config=configs/strict.yaml . \
    --html --output security-review.html

# 3. Check for auto-fixable issues
python tooling/auto_fix.py --results security-review.json --dry-run

# 4. Generate summary
echo "Security Review Summary:"
echo "========================"
jq -r '.results | group_by(.extra.severity) | map("\(.[0].extra.severity): \(length)") | .[]' security-review.json

echo ""
echo "Review complete. Check security-review.html for detailed results."
```

### 4. Team Collaboration

#### Shared Configuration
```yaml
# team-config.yaml
# Shared configuration for team development

rules:
  - include: packs/wp-core-security/
  - include: packs/wp-core-quality/

scanning:
  timeout: 120
  max_memory: 4096
  parallel: true

reporting:
  format: json
  output: team-reports/
  severity: warning

# Team-specific exclusions
exclude_patterns:
  - "vendor/"
  - "node_modules/"
  - "tests/"
  - "build/"
  - "dist/"

# Team-specific rules
team_rules:
  - id: custom.team.naming
    pattern: "function [a-z][a-zA-Z0-9_]*"
    message: "Function names should follow team naming convention"
    severity: INFO
```

#### Team Workflow Integration
```bash
#!/bin/bash
# team-security-check.sh

TEAM_MEMBERS=("alice" "bob" "charlie")
REVIEWER="security-lead"

echo "Running team security check..."

# Run security scan
semgrep scan --config=team-config.yaml . \
    --json --output team-security-results.json

# Generate team report
python tooling/generate_team_report.py \
    --results team-security-results.json \
    --team-members "${TEAM_MEMBERS[@]}" \
    --reviewer "$REVIEWER" \
    --output team-security-report.html

# Send notifications
python tooling/notify_team.py \
    --report team-security-report.html \
    --slack-webhook "$SLACK_WEBHOOK_URL"

echo "Team security check completed"
```

## Troubleshooting

### Common Issues and Solutions

#### Issue 1: Semgrep Not Found
```bash
# Problem: semgrep command not found
# Solution: Install Semgrep

# Method 1: Using pip
pip install semgrep>=1.45.0

# Method 2: Using conda
conda install -c conda-forge semgrep

# Method 3: Using Docker
docker run -v $(pwd):/src returntocorp/semgrep semgrep scan --config=configs/basic.yaml /src
```

#### Issue 2: Configuration File Not Found
```bash
# Problem: Configuration file not found
# Solution: Check file path and permissions

# Verify configuration file exists
ls -la configs/

# Check file permissions
chmod 644 configs/basic.yaml

# Use absolute path
semgrep scan --config=/full/path/to/configs/basic.yaml your-project/
```

#### Issue 3: Memory Issues
```bash
# Problem: Out of memory during scan
# Solution: Optimize memory usage

# Increase memory limit
semgrep scan --config=configs/basic.yaml --max-memory 8192 your-project/

# Use incremental scanning
semgrep scan --config=configs/basic.yaml --enable-version-check=false your-project/

# Exclude large directories
semgrep scan --config=configs/basic.yaml \
    --exclude-dir=vendor \
    --exclude-dir=node_modules \
    your-project/
```

#### Issue 4: Slow Performance
```bash
# Problem: Scan taking too long
# Solution: Performance optimization

# Use parallel processing
semgrep scan --config=configs/basic.yaml --jobs 4 your-project/

# Enable caching
export SEMGREP_CACHE_DIR="/tmp/semgrep-cache"
semgrep scan --config=configs/basic.yaml your-project/

# Use basic configuration for quick scans
semgrep scan --config=configs/basic.yaml your-project/
```

#### Issue 5: False Positives
```bash
# Problem: Too many false positives
# Solution: Fine-tune configuration

# Use more specific rules
semgrep scan --config=packs/wp-core-security/ your-project/

# Exclude specific rules
semgrep scan --config=configs/basic.yaml \
    --exclude-rule wordpress.security.nonce.missing \
    your-project/

# Adjust severity levels
semgrep scan --config=configs/basic.yaml \
    --severity ERROR \
    your-project/
```

### Debug Mode

#### Enable Verbose Output
```bash
# Set debug environment variables
export SEMGREP_VERBOSE=1
export SEMGREP_DEBUG=1

# Run with debug output
semgrep scan --config=configs/basic.yaml --verbose --debug your-project/
```

#### Debug Configuration Issues
```bash
# Validate configuration
python tooling/validate-configs.py --config configs/basic.yaml

# Test configuration with sample files
python tooling/validate-configs.py --test-scan tests/vulnerable-examples/

# Check rule syntax
python tooling/validate-rules.py --rules packs/wp-core-security/
```

### Getting Help

#### Check Documentation
```bash
# View Semgrep help
semgrep --help

# View specific command help
semgrep scan --help

# Check configuration documentation
cat docs/configuration-validator-guide.md
```

#### Community Support
- **GitHub Issues**: [Create an issue](https://github.com/giga-b/wordpress-semgrep-rules/issues)
- **Discussions**: [Join discussions](https://github.com/giga-b/wordpress-semgrep-rules/discussions)
- **Documentation**: [Read the docs](docs/)

## Reference

### Command Line Options

#### Basic Options
```bash
semgrep scan [OPTIONS] [TARGET]

Options:
  --config TEXT              Configuration file or directory
  --json                     Output results in JSON format
  --html                     Output results in HTML format
  --sarif                    Output results in SARIF format
  --output TEXT              Output file path
  --severity [ERROR|WARNING|INFO]  Minimum severity level
  --jobs INTEGER             Number of parallel jobs
  --timeout INTEGER          Timeout in seconds
  --max-memory INTEGER       Maximum memory in MB
  --exclude-dir TEXT         Exclude directories
  --include-dir TEXT         Include directories
  --exclude-rule TEXT        Exclude specific rules
  --include-rule TEXT        Include specific rules
  --dry-run                  Show what would be scanned
  --verbose                  Verbose output
  --debug                    Debug output
```

#### Advanced Options
```bash
# Performance options
--jobs 4                    # Use 4 parallel jobs
--timeout 300              # 5 minute timeout
--max-memory 8192          # 8GB memory limit

# Output options
--json --output results.json    # JSON output
--html --output report.html     # HTML report
--sarif --output results.sarif  # SARIF format

# Filtering options
--severity ERROR              # Only show errors
--exclude-dir vendor          # Exclude vendor directory
--include-rule wordpress.security.nonce.missing  # Include specific rule
```

### Configuration Reference

#### Rule Configuration
```yaml
# Rule inclusion/exclusion
rules:
  - include: packs/wp-core-security/     # Include all rules in directory
  - exclude: packs/experimental/          # Exclude experimental rules
  - rules:                               # Custom rules
    - id: custom.rule
      pattern: "dangerous_function()"
      message: "Avoid using dangerous function"
      severity: ERROR

# Scanning options
scanning:
  timeout: 120              # Timeout in seconds
  max_memory: 4096          # Memory limit in MB
  parallel: true            # Enable parallel processing
  incremental: true         # Enable incremental scanning

# Reporting options
reporting:
  format: json              # Output format
  output: reports/          # Output directory
  severity: warning         # Minimum severity
  include_patterns:         # File patterns to include
    - "*.php"
  exclude_patterns:         # File patterns to exclude
    - "vendor/"
    - "tests/"
```

### File Patterns

#### Include Patterns
```yaml
include_patterns:
  - "*.php"                 # All PHP files
  - "**/*.php"              # PHP files in subdirectories
  - "includes/*.php"        # PHP files in includes directory
  - "admin/**/*.php"        # PHP files in admin subdirectories
```

#### Exclude Patterns
```yaml
exclude_patterns:
  - "vendor/"               # Vendor directory
  - "node_modules/"         # Node modules
  - "tests/"                # Test files
  - "build/"                # Build artifacts
  - "*.test.php"            # Test PHP files
  - "**/cache/**"           # Cache directories
```

### Environment Variables

#### Semgrep Variables
```bash
export SEMGREP_CACHE_DIR="/tmp/semgrep-cache"    # Cache directory
export SEMGREP_VERBOSE=1                         # Verbose output
export SEMGREP_DEBUG=1                           # Debug mode
export SEMGREP_API_TOKEN="your_token"            # API token
```

#### Performance Variables
```bash
export PYTHONOPTIMIZE=1                          # Python optimization
export PYTHONHASHSEED=0                          # Hash seed
export PYTHONPATH="${PYTHONPATH}:/path/to/rules" # Python path
```

### Integration Examples

#### Pre-commit Hook
```bash
#!/bin/bash
# .git/hooks/pre-commit

# Run security scan on staged PHP files
git diff --cached --name-only --diff-filter=ACM | grep '\.php$' | while read file; do
    semgrep scan --config=configs/basic.yaml "$file" --json --output /tmp/pre-commit-results.json
    
    if jq -e '.results[] | select(.extra.severity == "ERROR")' /tmp/pre-commit-results.json > /dev/null; then
        echo "❌ Security issues found in $file"
        exit 1
    fi
done
```

#### CI/CD Pipeline
```yaml
# GitHub Actions example
- name: Security Scan
  run: |
    semgrep scan --config=configs/strict.yaml \
      --json --output semgrep-results.json \
      --error-on-findings
```

#### IDE Integration
```json
// VS Code settings
{
  "semgrep.enabled": true,
  "semgrep.config": "./configs/plugin-development.yaml",
  "semgrep.autoScan": true
}
```

## Conclusion

This production user guide provides comprehensive instructions for integrating WordPress Semgrep Rules into your development workflow. Follow these guidelines to ensure secure, efficient, and reliable WordPress development.

For additional support and advanced usage scenarios, refer to the [Production Deployment Guide](PRODUCTION-DEPLOYMENT-GUIDE.md) and [Troubleshooting Guide](troubleshooting.md).

## Appendix

### A. Quick Reference Cards
- [Command Reference](quick-reference/commands.md)
- [Configuration Reference](quick-reference/config.md)
- [Troubleshooting Reference](quick-reference/troubleshooting.md)

### B. Templates and Examples
- [Configuration Templates](templates/configs/)
- [CI/CD Templates](templates/cicd/)
- [Script Templates](templates/scripts/)

### C. Additional Resources
- [WordPress Security Best Practices](https://developer.wordpress.org/plugins/security/)
- [Semgrep Documentation](https://semgrep.dev/docs/)
- [OWASP Top Ten](https://owasp.org/www-project-top-ten/)
