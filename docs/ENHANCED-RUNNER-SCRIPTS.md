# Enhanced Runner Scripts Documentation

## Overview

The WordPress Semgrep Security Scanner now includes enhanced runner scripts with advanced features for improved performance, better error handling, and comprehensive reporting. This document covers the new capabilities and usage instructions.

## Script Versions

- **PowerShell Script**: `tooling/run-semgrep.ps1` (v2.0.0)
- **Bash Script**: `tooling/run-semgrep.sh` (v2.0.0)
- **Configuration Validator**: `tooling/validate-configs.py` (v2.0.0)

## New Features

### 1. Performance Monitoring

Monitor resource usage during scans to optimize performance.

**PowerShell:**
```powershell
.\run-semgrep.ps1 -Performance
```

**Bash:**
```bash
./run-semgrep.sh --performance
```

**Features:**
- Real-time memory and CPU monitoring
- Performance logs saved to temporary files
- Background monitoring that doesn't interfere with scanning

### 2. Configuration Validation

Validate configuration files before scanning to catch issues early.

**PowerShell:**
```powershell
.\run-semgrep.ps1 -Validate
```

**Bash:**
```bash
./run-semgrep.sh --validate
```

**Standalone Validator:**
```bash
python tooling/validate-configs.py configs/
python tooling/validate-configs.py --verbose configs/basic.yaml
python tooling/validate-configs.py --output validation-report.json configs/
```

**Validation Checks:**
- YAML syntax validation
- Semgrep rule structure validation
- Required field verification
- Severity level validation
- Language support verification
- Built-in Semgrep validation

### 3. Caching System

Cache scan results to speed up repeated scans.

**PowerShell:**
```powershell
.\run-semgrep.ps1 -Cache
```

**Bash:**
```bash
./run-semgrep.sh --cache
```

**Features:**
- Automatic cache key generation based on config and path
- 24-hour cache expiration
- Cache stored in system temp directory
- Transparent cache usage with fallback to full scan

### 4. Incremental Scanning

Only scan changed files for faster development workflows.

**PowerShell:**
```powershell
.\run-semgrep.ps1 -Incremental
```

**Bash:**
```bash
./run-semgrep.sh --incremental
```

**Features:**
- Git integration for change detection
- Only scans PHP files that have changed
- Falls back to full scan if git is not available
- Ideal for CI/CD pipelines

### 5. Enhanced Result Analysis

Comprehensive analysis and reporting of scan results.

**Features:**
- Detailed statistics (total, errors, warnings, info)
- Grouped findings by rule
- Critical issue highlighting
- Suggested fixes display
- HTML report generation

### 6. HTML Report Generation

Generate beautiful HTML reports for sharing and documentation.

**PowerShell:**
```powershell
.\run-semgrep.ps1 -Report "security-report.html"
```

**Bash:**
```bash
./run-semgrep.sh --report security-report.html
```

**Report Features:**
- Professional styling with color-coded severity
- Summary statistics
- Detailed findings with file locations
- Suggested fixes when available
- Scan duration and metadata

### 7. Timeout Protection

Prevent scans from running indefinitely.

**PowerShell:**
```powershell
.\run-semgrep.ps1 -Timeout 600
```

**Bash:**
```bash
./run-semgrep.sh --timeout 600
```

**Default:** 300 seconds (5 minutes)

### 8. Verbose Output

Get detailed information about the scanning process.

**PowerShell:**
```powershell
.\run-semgrep.ps1 -Verbose
```

**Bash:**
```bash
./run-semgrep.sh --verbose
```

**Features:**
- Command line display
- Detailed progress information
- Debug-level logging
- Performance metrics

## Command Line Options

### PowerShell Script Options

| Option | Description | Default |
|--------|-------------|---------|
| `-Config <file>` | Configuration file path | `configs/plugin-development.yaml` |
| `-Path <path>` | Path to scan | `.` |
| `-Install` | Install Semgrep if not found | `false` |
| `-Verbose` | Enable verbose output | `false` |
| `-Performance` | Enable performance monitoring | `false` |
| `-Validate` | Validate configuration before scanning | `false` |
| `-Cache` | Enable caching for repeated scans | `false` |
| `-Incremental` | Only scan changed files (requires git) | `false` |
| `-Output <file>` | Output file for results | `semgrep-results.json` |
| `-Report <file>` | HTML report file | `semgrep-report.html` |
| `-Timeout <seconds>` | Scan timeout in seconds | `300` |
| `-Help` | Show help message | `false` |

### Bash Script Options

| Option | Description | Default |
|--------|-------------|---------|
| `-c, --config CONFIG` | Configuration file path | `configs/plugin-development.yaml` |
| `-p, --path PATH` | Path to scan | `.` |
| `--install` | Install Semgrep if not found | `false` |
| `-v, --verbose` | Enable verbose output | `false` |
| `--performance` | Enable performance monitoring | `false` |
| `--validate` | Validate configuration before scanning | `false` |
| `--cache` | Enable caching for repeated scans | `false` |
| `--incremental` | Only scan changed files (requires git) | `false` |
| `-o, --output FILE` | Output file for results | `semgrep-results.json` |
| `-r, --report FILE` | HTML report file | `semgrep-report.html` |
| `-t, --timeout SECONDS` | Scan timeout in seconds | `300` |
| `-h, --help` | Show help message | `false` |

## Available Configurations

The scripts support all available configuration files:

- `configs/basic.yaml` - Essential security rules
- `configs/strict.yaml` - Comprehensive security coverage
- `configs/plugin-development.yaml` - WordPress plugin development
- `configs/optimized-15s.yaml` - Fast scanning (< 15s)
- `configs/optimized-30s.yaml` - Balanced scanning (< 30s)
- `configs/performance-optimized.yaml` - Performance-focused rules

## Usage Examples

### Basic Usage

**PowerShell:**
```powershell
# Basic scan with default configuration
.\run-semgrep.ps1

# Scan specific path with strict configuration
.\run-semgrep.ps1 -Config configs/strict.yaml -Path src/

# Verbose scan with performance monitoring
.\run-semgrep.ps1 -Verbose -Performance
```

**Bash:**
```bash
# Basic scan with default configuration
./run-semgrep.sh

# Scan specific path with strict configuration
./run-semgrep.sh -c configs/strict.yaml -p src/

# Verbose scan with performance monitoring
./run-semgrep.sh -v --performance
```

### Advanced Usage

**PowerShell:**
```powershell
# Development workflow with caching and incremental scanning
.\run-semgrep.ps1 -Cache -Incremental -Verbose

# Security audit with custom output and HTML report
.\run-semgrep.ps1 -Config configs/strict.yaml -Output audit-results.json -Report audit-report.html

# Performance testing with timeout
.\run-semgrep.ps1 -Performance -Timeout 600 -Verbose
```

**Bash:**
```bash
# Development workflow with caching and incremental scanning
./run-semgrep.sh --cache --incremental -v

# Security audit with custom output and HTML report
./run-semgrep.sh -c configs/strict.yaml -o audit-results.json -r audit-report.html

# Performance testing with timeout
./run-semgrep.sh --performance --timeout 600 -v
```

### CI/CD Integration

**GitHub Actions Example:**
```yaml
- name: Security Scan
  run: |
    chmod +x tooling/run-semgrep.sh
    ./tooling/run-semgrep.sh --cache --incremental --performance
```

**PowerShell CI Example:**
```powershell
# Pre-commit hook
.\run-semgrep.ps1 -Incremental -Cache -Timeout 120
```

## Configuration Validation

### Standalone Validator Usage

```bash
# Validate all configurations
python tooling/validate-configs.py configs/

# Validate specific file with verbose output
python tooling/validate-configs.py --verbose configs/basic.yaml

# Generate validation report
python tooling/validate-configs.py --output validation-report.json configs/
```

### Validation Features

- **YAML Syntax**: Validates proper YAML formatting
- **Rule Structure**: Checks required and optional fields
- **Severity Levels**: Validates ERROR, WARNING, INFO values
- **Language Support**: Verifies supported programming languages
- **Pattern Validation**: Ensures proper pattern structure
- **Semgrep Integration**: Uses Semgrep's built-in validation

## Performance Optimization

### Caching Strategy

The caching system uses:
- **Cache Key**: SHA256 hash of config file + scan path
- **Storage**: System temporary directory
- **Expiration**: 24 hours
- **Fallback**: Automatic full scan if cache invalid

### Incremental Scanning

Incremental scanning:
- **Git Integration**: Uses `git diff --name-only HEAD~1`
- **File Filtering**: Only scans PHP files
- **Fallback**: Full scan if git not available
- **Performance**: Significant speed improvement for small changes

### Performance Monitoring

Performance monitoring provides:
- **Memory Usage**: Real-time memory consumption tracking
- **CPU Usage**: CPU utilization monitoring
- **Logging**: Performance data saved to temporary files
- **Non-intrusive**: Background monitoring without impact

## Error Handling

### Enhanced Error Reporting

- **Configuration Errors**: Detailed validation messages
- **Semgrep Errors**: Proper error capture and display
- **Timeout Handling**: Graceful timeout with user notification
- **File System Errors**: Comprehensive file operation error handling

### Exit Codes

- **0**: Successful scan with no critical issues
- **1**: Scan completed with critical security issues
- **1**: Configuration validation failed
- **1**: Semgrep installation failed
- **1**: Scan timeout or other errors

## Output Formats

### JSON Results

Standard Semgrep JSON output with enhanced analysis:
```json
{
  "results": [...],
  "summary": {
    "total": 15,
    "errors": 3,
    "warnings": 8,
    "info": 4
  }
}
```

### HTML Reports

Professional HTML reports with:
- Color-coded severity levels
- File location links
- Suggested fixes
- Summary statistics
- Scan metadata

### Console Output

Enhanced console output with:
- Color-coded messages
- Progress indicators
- Detailed statistics
- Critical issue highlighting

## Troubleshooting

### Common Issues

1. **Semgrep Not Found**
   ```bash
   # Install Semgrep
   ./run-semgrep.sh --install
   ```

2. **Configuration Validation Errors**
   ```bash
   # Validate configuration
   python tooling/validate-configs.py configs/basic.yaml
   ```

3. **Cache Issues**
   ```bash
   # Clear cache (manual)
   rm /tmp/semgrep_cache_*.json
   ```

4. **Performance Issues**
   ```bash
   # Monitor performance
   ./run-semgrep.sh --performance --verbose
   ```

### Debug Mode

Enable verbose output for debugging:
```bash
./run-semgrep.sh --verbose --performance
```

## Integration with Existing Workflows

### Pre-commit Hooks

```bash
#!/bin/bash
# .git/hooks/pre-commit
./tooling/run-semgrep.sh --incremental --cache --timeout 120
```

### CI/CD Pipelines

```yaml
# GitHub Actions
- name: Security Scan
  run: |
    ./tooling/run-semgrep.sh --cache --performance --report security-report.html
```

### IDE Integration

Configure your IDE to use the enhanced scripts:
- **VS Code**: Add to tasks.json
- **PhpStorm**: External tool configuration
- **Sublime Text**: Build system integration

## Best Practices

1. **Use Caching**: Enable caching for development workflows
2. **Incremental Scanning**: Use incremental scanning for frequent checks
3. **Performance Monitoring**: Monitor performance for large codebases
4. **Configuration Validation**: Validate configurations before deployment
5. **HTML Reports**: Generate reports for documentation and sharing
6. **Timeout Protection**: Set appropriate timeouts for your codebase size

## Migration from v1.x

The enhanced scripts are backward compatible with existing configurations:

1. **No Breaking Changes**: Existing command line usage still works
2. **New Features**: Optional new features can be enabled as needed
3. **Gradual Adoption**: Can adopt new features incrementally
4. **Documentation**: Comprehensive help available with `-Help` flag

## Support

For issues and questions:

1. **Check Help**: Use `-Help` flag for command line help
2. **Validate Configs**: Use configuration validator for config issues
3. **Verbose Mode**: Enable verbose output for debugging
4. **Performance Monitoring**: Use performance monitoring for optimization
5. **Documentation**: Refer to this documentation for detailed usage
