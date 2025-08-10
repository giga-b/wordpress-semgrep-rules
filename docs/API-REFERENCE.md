# WordPress Semgrep Rules - API Reference

## Overview

This document provides a comprehensive reference for all APIs, tools, scripts, and interfaces available in the WordPress Semgrep Rules project. It serves as a technical reference for developers, system administrators, and integrators.

## Table of Contents

1. [Command Line Tools](#command-line-tools)
2. [Python APIs](#python-apis)
3. [Configuration APIs](#configuration-apis)
4. [Rule APIs](#rule-apis)
5. [Testing APIs](#testing-apis)
6. [Integration APIs](#integration-apis)
7. [Monitoring APIs](#monitoring-apis)
8. [Data Formats](#data-formats)
9. [Error Codes](#error-codes)

## Command Line Tools

### Core Scanning Tools

#### `semgrep` - Main Scanning Engine

**Description**: Primary Semgrep scanning command with WordPress-specific configurations.

**Usage**:
```bash
semgrep scan [OPTIONS] [TARGET]
```

**Options**:
```bash
# Configuration
--config TEXT                    Configuration file or directory
--config-file TEXT              Alternative config file path
--rules TEXT                    Comma-separated list of rule IDs

# Output
--json                          Output results in JSON format
--html                          Output results in HTML format
--sarif                         Output results in SARIF format
--output TEXT                   Output file path
--output-format TEXT            Output format (json, html, sarif, text)

# Filtering
--severity [ERROR|WARNING|INFO] Minimum severity level
--include-rule TEXT             Include specific rules
--exclude-rule TEXT             Exclude specific rules
--include-dir TEXT              Include specific directories
--exclude-dir TEXT              Exclude specific directories

# Performance
--jobs INTEGER                  Number of parallel jobs (default: 1)
--timeout INTEGER               Timeout in seconds (default: 300)
--max-memory INTEGER            Maximum memory in MB (default: 4096)
--enable-version-check BOOLEAN  Enable version checking (default: true)

# Debugging
--verbose                       Verbose output
--debug                         Debug output
--dry-run                       Show what would be scanned
--quiet                         Suppress output
```

**Examples**:
```bash
# Basic scan
semgrep scan --config=configs/basic.yaml /path/to/project

# JSON output with custom timeout
semgrep scan --config=configs/strict.yaml --json --output results.json --timeout 600 /path/to/project

# Parallel processing with exclusions
semgrep scan --config=configs/plugin-development.yaml --jobs 4 --exclude-dir vendor --exclude-dir tests /path/to/project
```

#### `run-semgrep.sh` - Unix/Linux Runner

**Description**: Enhanced bash script for running Semgrep with additional features.

**Usage**:
```bash
./tooling/run-semgrep.sh [OPTIONS] [TARGET]
```

**Options**:
```bash
# Basic options
--config CONFIG                 Configuration file path
--target TARGET                 Target directory or file
--output OUTPUT                 Output file path
--format FORMAT                 Output format (json, html, sarif)

# Advanced options
--parallel JOBS                 Number of parallel jobs
--timeout SECONDS              Scan timeout
--memory MB                    Memory limit in MB
--incremental                  Enable incremental scanning
--cache-dir DIR                Cache directory path

# Integration options
--ci                           CI/CD mode
--pre-commit                   Pre-commit hook mode
--auto-fix                     Enable auto-fix mode
--backup                       Create backups before fixes

# Debugging
--verbose                      Verbose output
--debug                        Debug mode
--dry-run                      Dry run mode
--help                         Show help message
```

**Examples**:
```bash
# Basic usage
./tooling/run-semgrep.sh --config configs/basic.yaml --target /path/to/project

# CI/CD mode with auto-fix
./tooling/run-semgrep.sh --config configs/strict.yaml --ci --auto-fix --backup

# Performance optimized
./tooling/run-semgrep.sh --config configs/plugin-development.yaml --parallel 4 --timeout 300 --memory 8192
```

#### `run-semgrep.ps1` - Windows PowerShell Runner

**Description**: Enhanced PowerShell script for running Semgrep on Windows systems.

**Usage**:
```powershell
.\tooling\run-semgrep.ps1 [OPTIONS] [TARGET]
```

**Options**:
```powershell
# Basic options
-Config CONFIG                  Configuration file path
-Target TARGET                  Target directory or file
-Output OUTPUT                  Output file path
-Format FORMAT                  Output format (json, html, sarif)

# Advanced options
-Parallel JOBS                  Number of parallel jobs
-Timeout SECONDS               Scan timeout
-Memory MB                     Memory limit in MB
-Incremental                   Enable incremental scanning
-CacheDir DIR                  Cache directory path

# Integration options
-CI                            CI/CD mode
-PreCommit                     Pre-commit hook mode
-AutoFix                       Enable auto-fix mode
-Backup                        Create backups before fixes

# Debugging
-Verbose                       Verbose output
-Debug                         Debug mode
-DryRun                        Dry run mode
-Help                          Show help message
```

**Examples**:
```powershell
# Basic usage
.\tooling\run-semgrep.ps1 -Config configs\basic.yaml -Target C:\path\to\project

# CI/CD mode with auto-fix
.\tooling\run-semgrep.ps1 -Config configs\strict.yaml -CI -AutoFix -Backup

# Performance optimized
.\tooling\run-semgrep.ps1 -Config configs\plugin-development.yaml -Parallel 4 -Timeout 300 -Memory 8192
```

### Utility Tools

#### `validate-configs.py` - Configuration Validator

**Description**: Validates Semgrep configuration files and rule syntax.

**Usage**:
```bash
python tooling/validate-configs.py [OPTIONS]
```

**Options**:
```bash
# Input options
--config CONFIG                 Configuration file to validate
--all                          Validate all configuration files
--rules RULES                  Validate specific rule files

# Testing options
--test-scan TARGET             Test scan with target files
--test-rules                   Test rule syntax
--test-patterns                Test pattern matching

# Output options
--output OUTPUT                Output file for results
--format FORMAT                Output format (json, text)
--verbose                      Verbose output

# Validation options
--strict                       Strict validation mode
--fix                          Auto-fix validation issues
--backup                       Create backup before fixing
```

**Examples**:
```bash
# Validate specific configuration
python tooling/validate-configs.py --config configs/basic.yaml

# Validate all configurations
python tooling/validate-configs.py --all --verbose

# Test configuration with sample files
python tooling/validate-configs.py --config configs/strict.yaml --test-scan tests/vulnerable-examples/
```

#### `validate-rules.py` - Rule Validator

**Description**: Validates Semgrep rule syntax and structure.

**Usage**:
```bash
python tooling/validate-rules.py [OPTIONS]
```

**Options**:
```bash
# Input options
--rules RULES                  Rule directory or file
--pack PACK                    Validate specific rule pack
--rule-id RULE_ID              Validate specific rule ID

# Validation options
--syntax                       Validate syntax only
--semantics                    Validate semantics
--patterns                     Validate patterns
--metadata                     Validate metadata
--all                          Validate all aspects

# Output options
--output OUTPUT                Output file for results
--format FORMAT                Output format (json, text)
--verbose                      Verbose output
--fix                          Auto-fix issues
```

**Examples**:
```bash
# Validate all rules
python tooling/validate-rules.py --rules packs/ --all

# Validate specific pack
python tooling/validate-rules.py --pack wp-core-security --verbose

# Validate specific rule
python tooling/validate-rules.py --rule-id wordpress.security.nonce.missing
```

#### `auto_fix.py` - Auto-fix System

**Description**: Automatically fixes common security issues detected by Semgrep.

**Usage**:
```bash
python tooling/auto_fix.py [OPTIONS]
```

**Options**:
```bash
# Input options
--results RESULTS              Semgrep results file
--config CONFIG                Auto-fix configuration file
--target TARGET                Target directory or file

# Fix options
--fix-all                      Fix all issues
--fix-errors                   Fix error-level issues only
--fix-warnings                 Fix warning-level issues only
--fix-specific RULE_ID         Fix specific rule issues
--confidence-threshold FLOAT   Minimum confidence threshold (0.0-1.0)

# Safety options
--dry-run                      Preview fixes without applying
--backup                       Create backup before fixing
--validate                     Validate fixes after applying
--rollback                     Enable rollback capability

# Output options
--output OUTPUT                Output file for fix report
--format FORMAT                Output format (json, html)
--verbose                      Verbose output
```

**Examples**:
```bash
# Preview fixes
python tooling/auto_fix.py --results semgrep-results.json --dry-run

# Apply fixes with backup
python tooling/auto_fix.py --results semgrep-results.json --backup --fix-errors

# Fix specific rule with validation
python tooling/auto_fix.py --results semgrep-results.json --fix-specific wordpress.security.nonce.missing --validate
```

## Python APIs

### Core Scanner API

#### `SemgrepScanner` Class

**Description**: Main Python API for running Semgrep scans programmatically.

**Location**: `tooling/semgrep_scanner.py`

**Constructor**:
```python
SemgrepScanner(
    config_path: str,
    target_path: str,
    output_format: str = "json",
    output_path: Optional[str] = None,
    timeout: int = 300,
    max_memory: int = 4096,
    jobs: int = 1,
    verbose: bool = False
)
```

**Methods**:

```python
class SemgrepScanner:
    def scan(self) -> Dict[str, Any]:
        """Run Semgrep scan and return results."""
        
    def scan_file(self, file_path: str) -> Dict[str, Any]:
        """Scan a single file."""
        
    def scan_directory(self, directory_path: str) -> Dict[str, Any]:
        """Scan a directory recursively."""
        
    def validate_config(self) -> bool:
        """Validate configuration file."""
        
    def get_scan_stats(self) -> Dict[str, Any]:
        """Get scan statistics."""
        
    def export_results(self, format: str, path: str) -> bool:
        """Export results in specified format."""
```

**Example Usage**:
```python
from tooling.semgrep_scanner import SemgrepScanner

# Create scanner instance
scanner = SemgrepScanner(
    config_path="configs/basic.yaml",
    target_path="/path/to/project",
    output_format="json",
    timeout=600,
    jobs=4
)

# Run scan
results = scanner.scan()

# Export results
scanner.export_results("html", "report.html")

# Get statistics
stats = scanner.get_scan_stats()
print(f"Scanned {stats['files_scanned']} files in {stats['duration']} seconds")
```

### Configuration API

#### `ConfigurationManager` Class

**Description**: Manages Semgrep configuration files and settings.

**Location**: `tooling/configuration_manager.py`

**Constructor**:
```python
ConfigurationManager(
    config_dir: str = "configs/",
    cache_dir: Optional[str] = None,
    validate_on_load: bool = True
)
```

**Methods**:

```python
class ConfigurationManager:
    def load_config(self, config_name: str) -> Dict[str, Any]:
        """Load configuration by name."""
        
    def save_config(self, config_name: str, config_data: Dict[str, Any]) -> bool:
        """Save configuration to file."""
        
    def validate_config(self, config_data: Dict[str, Any]) -> List[str]:
        """Validate configuration data."""
        
    def merge_configs(self, base_config: str, override_config: str) -> Dict[str, Any]:
        """Merge two configurations."""
        
    def get_available_configs(self) -> List[str]:
        """Get list of available configurations."""
        
    def create_custom_config(self, name: str, rules: List[str], options: Dict[str, Any]) -> bool:
        """Create custom configuration."""
```

**Example Usage**:
```python
from tooling.configuration_manager import ConfigurationManager

# Create configuration manager
config_manager = ConfigurationManager()

# Load configuration
config = config_manager.load_config("basic")

# Validate configuration
errors = config_manager.validate_config(config)
if errors:
    print(f"Configuration errors: {errors}")

# Create custom configuration
custom_config = {
    "rules": ["packs/wp-core-security/"],
    "scanning": {"timeout": 120, "jobs": 4},
    "reporting": {"format": "json", "severity": "warning"}
}
config_manager.save_config("custom", custom_config)
```

### Rule Management API

#### `RuleManager` Class

**Description**: Manages Semgrep rules and rule packs.

**Location**: `tooling/rule_manager.py`

**Constructor**:
```python
RuleManager(
    rules_dir: str = "packs/",
    cache_dir: Optional[str] = None,
    validate_rules: bool = True
)
```

**Methods**:

```python
class RuleManager:
    def load_rules(self, pack_name: str) -> List[Dict[str, Any]]:
        """Load rules from a pack."""
        
    def validate_rule(self, rule_data: Dict[str, Any]) -> List[str]:
        """Validate individual rule."""
        
    def create_rule(self, rule_id: str, pattern: str, message: str, severity: str) -> Dict[str, Any]:
        """Create new rule."""
        
    def update_rule(self, rule_id: str, updates: Dict[str, Any]) -> bool:
        """Update existing rule."""
        
    def delete_rule(self, rule_id: str) -> bool:
        """Delete rule."""
        
    def get_rule_stats(self, pack_name: str) -> Dict[str, Any]:
        """Get statistics for rule pack."""
        
    def export_rules(self, pack_name: str, format: str, path: str) -> bool:
        """Export rules in specified format."""
```

**Example Usage**:
```python
from tooling.rule_manager import RuleManager

# Create rule manager
rule_manager = RuleManager()

# Load rules from pack
rules = rule_manager.load_rules("wp-core-security")

# Create custom rule
custom_rule = rule_manager.create_rule(
    rule_id="custom.security.example",
    pattern="dangerous_function($X)",
    message="Avoid using dangerous function",
    severity="ERROR"
)

# Validate rule
errors = rule_manager.validate_rule(custom_rule)
if not errors:
    print("Rule is valid")
```

### Cache Management API

#### `CacheManager` Class

**Description**: Manages caching for scan results and rule compilation.

**Location**: `tooling/cache_manager.py`

**Constructor**:
```python
CacheManager(
    cache_dir: str = ".cache/",
    max_size: int = 1024 * 1024 * 100,  # 100MB
    ttl: int = 3600  # 1 hour
)
```

**Methods**:

```python
class CacheManager:
    def get_cache_key(self, file_path: str, config_hash: str) -> str:
        """Generate cache key for file and configuration."""
        
    def get_cached_results(self, cache_key: str) -> Optional[Dict[str, Any]]:
        """Retrieve cached results."""
        
    def cache_results(self, cache_key: str, results: Dict[str, Any]) -> bool:
        """Cache scan results."""
        
    def invalidate_cache(self, pattern: str = "*") -> int:
        """Invalidate cache entries matching pattern."""
        
    def get_cache_stats(self) -> Dict[str, Any]:
        """Get cache statistics."""
        
    def cleanup_cache(self) -> int:
        """Clean up expired cache entries."""
```

**Example Usage**:
```python
from tooling.cache_manager import CacheManager
import hashlib

# Create cache manager
cache_manager = CacheManager()

# Generate cache key
file_hash = hashlib.md5(open("file.php", "rb").read()).hexdigest()
config_hash = hashlib.md5(open("config.yaml", "rb").read()).hexdigest()
cache_key = cache_manager.get_cache_key("file.php", config_hash)

# Check for cached results
cached_results = cache_manager.get_cached_results(cache_key)
if cached_results:
    print("Using cached results")
else:
    # Run scan and cache results
    results = run_scan()
    cache_manager.cache_results(cache_key, results)
```

## Configuration APIs

### Configuration File Format

#### Basic Configuration Structure
```yaml
# Configuration file structure
rules:
  - include: packs/wp-core-security/     # Include rule pack
  - exclude: packs/experimental/          # Exclude rule pack
  - rules:                               # Custom rules
    - id: custom.rule.id
      pattern: "pattern_to_match"
      message: "Rule message"
      severity: ERROR|WARNING|INFO

scanning:
  timeout: 300                           # Timeout in seconds
  max_memory: 4096                       # Memory limit in MB
  jobs: 4                                # Parallel jobs
  incremental: true                      # Enable incremental scanning
  cache_enabled: true                    # Enable caching

reporting:
  format: json                           # Output format
  output: reports/                       # Output directory
  severity: warning                      # Minimum severity
  include_patterns:                      # File patterns to include
    - "*.php"
  exclude_patterns:                      # File patterns to exclude
    - "vendor/"
    - "tests/"

validation:
  strict: false                          # Strict validation mode
  auto_fix: false                        # Enable auto-fix
  backup: true                           # Create backups
```

#### Environment-Specific Configurations

**Development Configuration**:
```yaml
# configs/development.yaml
rules:
  - include: packs/wp-core-security/
  - include: packs/wp-core-quality/

scanning:
  timeout: 60
  max_memory: 2048
  jobs: 2

reporting:
  format: json
  severity: info
  output: reports/development/
```

**Production Configuration**:
```yaml
# configs/production.yaml
rules:
  - include: packs/wp-core-security/
  - include: packs/wp-core-quality/

scanning:
  timeout: 300
  max_memory: 8192
  jobs: 4
  incremental: true

reporting:
  format: json
  severity: error
  output: reports/production/
  alerting: true
```

### Configuration Validation

#### Validation Rules
```python
# Configuration validation schema
CONFIG_SCHEMA = {
    "type": "object",
    "properties": {
        "rules": {
            "type": "array",
            "items": {
                "oneOf": [
                    {"type": "string"},
                    {"type": "object"}
                ]
            }
        },
        "scanning": {
            "type": "object",
            "properties": {
                "timeout": {"type": "integer", "minimum": 1},
                "max_memory": {"type": "integer", "minimum": 1},
                "jobs": {"type": "integer", "minimum": 1, "maximum": 16}
            }
        },
        "reporting": {
            "type": "object",
            "properties": {
                "format": {"enum": ["json", "html", "sarif", "text"]},
                "severity": {"enum": ["error", "warning", "info"]}
            }
        }
    },
    "required": ["rules"]
}
```

## Rule APIs

### Rule Format

#### Basic Rule Structure
```yaml
- id: wordpress.security.nonce.missing
  languages: [php]
  message: "Missing nonce verification for form submission"
  severity: ERROR
  metadata:
    category: "security"
    cwe: "CWE-352"
    references:
      - "https://developer.wordpress.org/plugins/security/nonces/"
  patterns:
    - pattern: "wp_nonce_field(...)"
  pattern-not: "wp_verify_nonce(...)"
  fix: "Add nonce verification: wp_verify_nonce($_POST['_wpnonce'], 'action_name')"
```

#### Advanced Rule Patterns
```yaml
- id: wordpress.security.sql.injection
  languages: [php]
  message: "Potential SQL injection vulnerability"
  severity: ERROR
  metadata:
    category: "security"
    cwe: "CWE-89"
  patterns:
    - pattern-either:
      - pattern: "$wpdb->query($VARIABLE)"
      - pattern: "mysql_query($VARIABLE)"
  pattern-not:
    - pattern: "$wpdb->prepare(...)"
    - pattern: "esc_sql(...)"
  fix: "Use prepared statements: $wpdb->prepare('SELECT * FROM table WHERE id = %d', $variable)"
```

### Rule Categories

#### Security Rules
```yaml
# Nonce verification
- id: wordpress.security.nonce.missing
- id: wordpress.security.nonce.invalid
- id: wordpress.security.nonce.expired

# Capability checks
- id: wordpress.security.capability.missing
- id: wordpress.security.capability.invalid
- id: wordpress.security.capability.insufficient

# Input sanitization
- id: wordpress.security.sanitization.missing
- id: wordpress.security.sanitization.weak
- id: wordpress.security.sanitization.bypassed

# Output escaping
- id: wordpress.security.output.unescaped
- id: wordpress.security.output.weak_escaping
- id: wordpress.security.output.context_mismatch
```

#### Quality Rules
```yaml
# WordPress coding standards
- id: wordpress.quality.naming.convention
- id: wordpress.quality.function.length
- id: wordpress.quality.complexity.high

# Performance rules
- id: wordpress.performance.query.inefficient
- id: wordpress.performance.memory.leak
- id: wordpress.performance.cache.missing

# Best practices
- id: wordpress.best_practices.hook.usage
- id: wordpress.best_practices.plugin.structure
- id: wordpress.best_practices.theme.compatibility
```

## Testing APIs

### Test Runner API

#### `TestRunner` Class

**Description**: Runs automated tests for rules and configurations.

**Location**: `tests/test_runner.py`

**Constructor**:
```python
TestRunner(
    test_dir: str = "tests/",
    config_dir: str = "configs/",
    output_dir: str = "test-results/"
)
```

**Methods**:

```python
class TestRunner:
    def run_vulnerable_tests(self, config_name: str) -> Dict[str, Any]:
        """Run tests against vulnerable examples."""
        
    def run_safe_tests(self, config_name: str) -> Dict[str, Any]:
        """Run tests against safe examples."""
        
    def run_regression_tests(self) -> Dict[str, Any]:
        """Run regression tests."""
        
    def run_performance_tests(self) -> Dict[str, Any]:
        """Run performance benchmarks."""
        
    def generate_test_report(self, results: Dict[str, Any]) -> str:
        """Generate test report."""
        
    def validate_test_results(self, results: Dict[str, Any]) -> bool:
        """Validate test results."""
```

**Example Usage**:
```python
from tests.test_runner import TestRunner

# Create test runner
test_runner = TestRunner()

# Run vulnerable tests
vulnerable_results = test_runner.run_vulnerable_tests("basic")

# Run safe tests
safe_results = test_runner.run_safe_tests("basic")

# Generate report
report = test_runner.generate_test_report({
    "vulnerable": vulnerable_results,
    "safe": safe_results
})

# Validate results
if test_runner.validate_test_results(vulnerable_results):
    print("All vulnerable tests passed")
```

### Test Data Format

#### Test Case Structure
```yaml
# test case structure
test_cases:
  - name: "nonce_verification_missing"
    description: "Test for missing nonce verification"
    file: "tests/vulnerable-examples/nonce-missing.php"
    expected_findings:
      - rule_id: "wordpress.security.nonce.missing"
        severity: "ERROR"
        line: 15
    expected_count: 1
    
  - name: "nonce_verification_present"
    description: "Test for proper nonce verification"
    file: "tests/safe-examples/nonce-present.php"
    expected_findings: []
    expected_count: 0
```

## Integration APIs

### CI/CD Integration

#### GitHub Actions API

**Workflow Configuration**:
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
```

#### GitLab CI API

**Pipeline Configuration**:
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

### IDE Integration

#### VS Code Extension API

**Extension Configuration**:
```json
// .vscode/settings.json
{
  "wordpressSemgrep.enabled": true,
  "wordpressSemgrep.configPath": "./configs/plugin-development.yaml",
  "wordpressSemgrep.autoScan": true,
  "wordpressSemgrep.severity": "warning",
  "wordpressSemgrep.maxProblems": 100,
  "wordpressSemgrep.timeout": 30
}
```

**Extension Commands**:
```json
// package.json commands
{
  "contributes": {
    "commands": [
      {
        "command": "wordpressSemgrep.scanFile",
        "title": "Scan Current File"
      },
      {
        "command": "wordpressSemgrep.scanWorkspace",
        "title": "Scan Workspace"
      },
      {
        "command": "wordpressSemgrep.showProblems",
        "title": "Show Security Problems"
      }
    ]
  }
}
```

## Monitoring APIs

### Metrics Collection

#### `MetricsCollector` Class

**Description**: Collects and manages scan metrics and performance data.

**Location**: `tooling/metrics_collector.py`

**Constructor**:
```python
MetricsCollector(
    db_path: str = "metrics.db",
    retention_days: int = 30
)
```

**Methods**:

```python
class MetricsCollector:
    def record_scan(self, scan_data: Dict[str, Any]) -> bool:
        """Record scan metrics."""
        
    def get_scan_stats(self, days: int = 7) -> Dict[str, Any]:
        """Get scan statistics for specified period."""
        
    def get_rule_performance(self, rule_id: str) -> Dict[str, Any]:
        """Get performance metrics for specific rule."""
        
    def get_false_positive_rate(self, rule_id: str) -> float:
        """Calculate false positive rate for rule."""
        
    def export_metrics(self, format: str, path: str) -> bool:
        """Export metrics in specified format."""
        
    def cleanup_old_metrics(self, days: int) -> int:
        """Clean up old metrics data."""
```

**Example Usage**:
```python
from tooling.metrics_collector import MetricsCollector

# Create metrics collector
collector = MetricsCollector()

# Record scan metrics
scan_data = {
    "timestamp": "2024-01-15T10:30:00Z",
    "config": "basic",
    "target": "/path/to/project",
    "duration": 45.2,
    "files_scanned": 150,
    "findings": 12,
    "errors": 2,
    "warnings": 8,
    "info": 2
}
collector.record_scan(scan_data)

# Get statistics
stats = collector.get_scan_stats(days=7)
print(f"Average scan time: {stats['avg_duration']} seconds")

# Get rule performance
performance = collector.get_rule_performance("wordpress.security.nonce.missing")
print(f"False positive rate: {performance['false_positive_rate']}")
```

### Dashboard API

#### `DashboardGenerator` Class

**Description**: Generates HTML dashboard for metrics visualization.

**Location**: `tooling/dashboard_generator.py`

**Constructor**:
```python
DashboardGenerator(
    metrics_db: str = "metrics.db",
    template_dir: str = "templates/",
    output_dir: str = "dashboard/"
)
```

**Methods**:

```python
class DashboardGenerator:
    def generate_dashboard(self) -> str:
        """Generate main dashboard."""
        
    def generate_performance_chart(self) -> str:
        """Generate performance chart."""
        
    def generate_findings_chart(self) -> str:
        """Generate findings chart."""
        
    def generate_rule_performance_table(self) -> str:
        """Generate rule performance table."""
        
    def export_dashboard(self, format: str, path: str) -> bool:
        """Export dashboard in specified format."""
```

**Example Usage**:
```python
from tooling.dashboard_generator import DashboardGenerator

# Create dashboard generator
dashboard = DashboardGenerator()

# Generate dashboard
dashboard_path = dashboard.generate_dashboard()

# Generate specific charts
performance_chart = dashboard.generate_performance_chart()
findings_chart = dashboard.generate_findings_chart()

print(f"Dashboard generated at: {dashboard_path}")
```

## Data Formats

### Scan Results Format

#### JSON Output Structure
```json
{
  "version": "1.0.0",
  "scan_info": {
    "timestamp": "2024-01-15T10:30:00Z",
    "config": "configs/basic.yaml",
    "target": "/path/to/project",
    "duration": 45.2,
    "files_scanned": 150,
    "files_skipped": 5
  },
  "results": [
    {
      "check_id": "wordpress.security.nonce.missing",
      "path": "includes/admin.php",
      "start": {
        "line": 45,
        "col": 5,
        "offset": 1234
      },
      "end": {
        "line": 45,
        "col": 25,
        "offset": 1254
      },
      "extra": {
        "message": "Missing nonce verification for form submission",
        "severity": "ERROR",
        "metadata": {
          "category": "security",
          "cwe": "CWE-352",
          "references": [
            "https://developer.wordpress.org/plugins/security/nonces/"
          ]
        },
        "fix": "Add nonce verification: wp_verify_nonce($_POST['_wpnonce'], 'action_name')",
        "confidence": 0.95
      }
    }
  ],
  "errors": [],
  "paths": {
    "scanned": ["/path/to/project/"],
    "skipped": ["/path/to/project/vendor/"]
  }
}
```

#### HTML Report Structure
```html
<!DOCTYPE html>
<html>
<head>
    <title>WordPress Semgrep Security Report</title>
    <style>
        /* CSS styles for report */
    </style>
</head>
<body>
    <h1>Security Scan Report</h1>
    <div class="summary">
        <h2>Summary</h2>
        <p>Scanned: 150 files</p>
        <p>Duration: 45.2 seconds</p>
        <p>Findings: 12 total</p>
    </div>
    <div class="findings">
        <h2>Findings</h2>
        <!-- Findings table -->
    </div>
</body>
</html>
```

### Configuration Format

#### YAML Configuration
```yaml
# Configuration file format
rules:
  - include: packs/wp-core-security/
  - exclude: packs/experimental/
  - rules:
    - id: custom.rule
      pattern: "dangerous_function($X)"
      message: "Avoid using dangerous function"
      severity: ERROR

scanning:
  timeout: 300
  max_memory: 4096
  jobs: 4
  incremental: true

reporting:
  format: json
  output: reports/
  severity: warning
  include_patterns:
    - "*.php"
  exclude_patterns:
    - "vendor/"
    - "tests/"
```

### Metrics Format

#### Database Schema
```sql
-- Metrics database schema
CREATE TABLE scans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    timestamp DATETIME NOT NULL,
    config TEXT NOT NULL,
    target TEXT NOT NULL,
    duration REAL NOT NULL,
    files_scanned INTEGER NOT NULL,
    files_skipped INTEGER NOT NULL,
    findings_count INTEGER NOT NULL,
    errors_count INTEGER NOT NULL,
    warnings_count INTEGER NOT NULL,
    info_count INTEGER NOT NULL
);

CREATE TABLE findings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    scan_id INTEGER NOT NULL,
    rule_id TEXT NOT NULL,
    file_path TEXT NOT NULL,
    line_number INTEGER NOT NULL,
    severity TEXT NOT NULL,
    message TEXT NOT NULL,
    confidence REAL,
    FOREIGN KEY (scan_id) REFERENCES scans(id)
);

CREATE TABLE rule_performance (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    rule_id TEXT NOT NULL,
    scan_id INTEGER NOT NULL,
    execution_time REAL NOT NULL,
    findings_count INTEGER NOT NULL,
    false_positives INTEGER NOT NULL,
    FOREIGN KEY (scan_id) REFERENCES scans(id)
);
```

## Error Codes

### Common Error Codes

#### Configuration Errors
```python
# Configuration error codes
CONFIG_ERRORS = {
    "CONFIG_FILE_NOT_FOUND": "Configuration file not found",
    "CONFIG_INVALID_YAML": "Invalid YAML syntax in configuration",
    "CONFIG_MISSING_RULES": "No rules specified in configuration",
    "CONFIG_INVALID_RULE": "Invalid rule definition",
    "CONFIG_DUPLICATE_RULE": "Duplicate rule ID found",
    "CONFIG_INVALID_SEVERITY": "Invalid severity level",
    "CONFIG_INVALID_PATTERN": "Invalid pattern syntax"
}
```

#### Scanning Errors
```python
# Scanning error codes
SCAN_ERRORS = {
    "TARGET_NOT_FOUND": "Target file or directory not found",
    "TARGET_NO_ACCESS": "No access to target file or directory",
    "SCAN_TIMEOUT": "Scan exceeded timeout limit",
    "SCAN_MEMORY_LIMIT": "Scan exceeded memory limit",
    "SCAN_INTERRUPTED": "Scan was interrupted",
    "SCAN_NO_FILES": "No files found to scan",
    "SCAN_PARTIAL": "Scan completed with errors"
}
```

#### Rule Errors
```python
# Rule error codes
RULE_ERRORS = {
    "RULE_NOT_FOUND": "Rule not found",
    "RULE_INVALID_SYNTAX": "Invalid rule syntax",
    "RULE_COMPILATION_FAILED": "Rule compilation failed",
    "RULE_PATTERN_INVALID": "Invalid pattern in rule",
    "RULE_METADATA_INVALID": "Invalid metadata in rule",
    "RULE_DEPENDENCY_MISSING": "Rule dependency not found"
}
```

### Error Handling

#### Error Response Format
```json
{
  "error": {
    "code": "CONFIG_FILE_NOT_FOUND",
    "message": "Configuration file not found",
    "details": {
      "file_path": "/path/to/config.yaml",
      "suggested_fix": "Check file path and permissions"
    },
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

#### Error Handling Example
```python
try:
    scanner = SemgrepScanner(config_path="invalid.yaml", target_path="/path/to/project")
    results = scanner.scan()
except ConfigurationError as e:
    print(f"Configuration error: {e.code} - {e.message}")
    if e.details.get("suggested_fix"):
        print(f"Suggested fix: {e.details['suggested_fix']}")
except ScanningError as e:
    print(f"Scanning error: {e.code} - {e.message}")
except Exception as e:
    print(f"Unexpected error: {str(e)}")
```

## Conclusion

This API reference provides comprehensive documentation for all interfaces and tools available in the WordPress Semgrep Rules project. Use this reference to integrate the security scanning capabilities into your development workflows, CI/CD pipelines, and custom applications.

For additional examples and use cases, refer to the [Production User Guide](PRODUCTION-USER-GUIDE.md) and [Development Guide](DEVELOPMENT-GUIDE.md).

## Appendix

### A. Code Examples
- [Python Integration Examples](examples/python/)
- [Shell Script Examples](examples/shell/)
- [Configuration Examples](examples/configs/)

### B. Templates
- [CI/CD Templates](templates/cicd/)
- [Configuration Templates](templates/configs/)
- [Rule Templates](templates/rules/)

### C. Reference Cards
- [Command Reference](reference/commands.md)
- [Configuration Reference](reference/config.md)
- [Rule Reference](reference/rules.md)
