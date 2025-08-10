# GitHub Actions Integration Guide

## Overview

This guide explains how to use the GitHub Actions integration for automated security scanning in the WordPress Semgrep Rules project. The integration provides comprehensive security analysis, performance benchmarking, and automated reporting.

## Workflow Files

### 1. Security Scan Workflow (`security-scan.yml`)

**Location**: `.github/workflows/security-scan.yml`

**Purpose**: Automated security scanning with comprehensive reporting and performance tracking.

#### Triggers

- **Push**: Runs on pushes to `main` and `develop` branches
- **Pull Request**: Runs on PRs to `main` branch
- **Schedule**: Weekly scan on Sundays at 2 AM UTC
- **Manual**: Manual trigger with customizable options

#### Manual Trigger Options

When manually triggering the workflow, you can specify:

- **Configuration**: Choose from `basic`, `strict`, `plugin-development`, `optimized-15s`, `optimized-30s`
- **Scan Path**: Specify which directory to scan (default: entire repository)
- **Include Experimental**: Option to include experimental rules

#### Jobs

##### 1. Security Scan Job

**Purpose**: Run Semgrep security analysis with detailed reporting

**Features**:
- Configuration validation
- Comprehensive security scanning
- Results analysis and categorization
- Detailed reporting with severity breakdown
- PR comments with findings summary
- Build failure on critical issues

**Outputs**:
- `security-scan-results.json`: Raw Semgrep results
- `security-report.md`: Human-readable report
- PR comments with findings summary

##### 2. Performance Benchmark Job

**Purpose**: Track scanning performance across different configurations

**Features**:
- Tests all available configurations
- Measures scan duration
- Generates performance metrics
- Runs only on main branch pushes

**Outputs**:
- `performance-results.txt`: Performance metrics

##### 3. Security Dashboard Job

**Purpose**: Generate security dashboard data for tracking

**Features**:
- Aggregates scan results
- Creates dashboard JSON data
- Tracks historical security metrics

**Outputs**:
- `security-dashboard.json`: Dashboard data

## Pre-commit Integration

### Configuration File

**Location**: `.pre-commit-config.yaml`

**Purpose**: Local security checks before committing code

### Hooks Included

1. **Semgrep Security Scan**
   - Runs basic and plugin-development configurations
   - Scans PHP and include files
   - Excludes test directories and vendor files
   - Fails on security errors

2. **YAML Lint**
   - Validates YAML syntax in configuration files
   - Ensures proper formatting

3. **PHP Syntax Check**
   - Validates PHP syntax
   - Catches syntax errors before commit

4. **Code Quality Checks**
   - Trailing whitespace removal
   - End of file fixer
   - YAML/JSON syntax validation
   - Merge conflict detection

## Setup Instructions

### 1. GitHub Actions Setup

The workflow is automatically configured and will run on:
- All pushes to main/develop branches
- All pull requests to main branch
- Weekly scheduled scans
- Manual triggers

### 2. Pre-commit Setup

Install pre-commit hooks:

```bash
# Install pre-commit
pip install pre-commit

# Install the git hook scripts
pre-commit install

# Run against all files (optional)
pre-commit run --all-files
```

### 3. Local Development Setup

For local development without pre-commit:

```bash
# Install Semgrep
pip install semgrep==1.75.0

# Run security scan manually
semgrep scan --config=configs/basic.yaml --json --output=results.json .

# Run with specific configuration
semgrep scan --config=configs/plugin-development.yaml --json --output=results.json .
```

## Configuration Options

### Available Configurations

1. **basic.yaml**: Essential security rules, minimal false positives
2. **strict.yaml**: Comprehensive security coverage
3. **plugin-development.yaml**: WordPress plugin-specific patterns
4. **optimized-15s.yaml**: Performance-optimized for 15-second scans
5. **optimized-30s.yaml**: Performance-optimized for 30-second scans

### Customizing Scans

#### GitHub Actions

Modify the workflow file to:
- Change trigger conditions
- Add new configurations
- Modify scan paths
- Adjust failure conditions

#### Pre-commit

Modify `.pre-commit-config.yaml` to:
- Add new hooks
- Change file patterns
- Modify exclusion rules
- Add custom checks

## Understanding Results

### Scan Results Structure

```json
{
  "results": [
    {
      "check_id": "wordpress.nonce.missing-verification",
      "path": "src/plugin.php",
      "start": {"line": 15, "col": 5},
      "end": {"line": 15, "col": 25},
      "extra": {
        "message": "Nonce verification is missing",
        "severity": "ERROR",
        "metadata": {
          "category": "nonce-verification",
          "cwe": "CWE-352"
        }
      }
    }
  ]
}
```

### Severity Levels

- **ERROR**: Critical security issues that must be fixed
- **WARNING**: Security concerns that should be reviewed
- **INFO**: Informational findings for best practices

### Categories

- **nonce-verification**: CSRF protection issues
- **capability-checks**: Authorization problems
- **xss-prevention**: Cross-site scripting vulnerabilities
- **sql-injection**: Database injection risks
- **sanitization-functions**: Input validation issues

## Troubleshooting

### Common Issues

#### 1. Workflow Fails on Critical Issues

**Solution**: Review and fix all ERROR-level findings before merging.

#### 2. Pre-commit Hook Fails

**Solution**: 
```bash
# Skip pre-commit for this commit (not recommended)
git commit --no-verify

# Run pre-commit manually to see detailed errors
pre-commit run --all-files
```

#### 3. Configuration Validation Fails

**Solution**: Check YAML syntax in configuration files:
```bash
# Validate specific configuration
semgrep scan --config=configs/basic.yaml --dryrun
```

#### 4. Performance Issues

**Solution**: Use optimized configurations:
- `optimized-15s.yaml` for fast scans
- `optimized-30s.yaml` for balanced performance

### Debug Mode

Enable debug output in GitHub Actions:

```yaml
- name: Run security scan
  env:
    SEMGREP_VERBOSE: "true"
  run: |
    semgrep scan --config=configs/basic.yaml --verbose .
```

## Best Practices

### 1. Regular Scans

- Run weekly scheduled scans
- Use manual triggers for specific configurations
- Monitor performance metrics

### 2. PR Reviews

- Always review security scan results
- Fix critical issues before merging
- Use PR comments for guidance

### 3. Local Development

- Use pre-commit hooks for immediate feedback
- Run manual scans before pushing
- Test with different configurations

### 4. Configuration Management

- Keep configurations up to date
- Test new rules before deployment
- Monitor false positive rates

## Integration with Other Tools

### 1. IDE Integration

The GitHub Actions workflow can be extended to integrate with:
- VS Code security extensions
- Cursor IDE integration
- Other development tools

### 2. CI/CD Pipeline

The workflow can be integrated into:
- Jenkins pipelines
- GitLab CI
- Azure DevOps
- Other CI/CD systems

### 3. Security Tools

Can be combined with:
- Static analysis tools
- Dependency scanners
- Code quality tools

## Monitoring and Metrics

### 1. Security Dashboard

The workflow generates dashboard data for:
- Historical security trends
- Performance metrics
- Configuration effectiveness

### 2. Performance Tracking

Monitor:
- Scan duration trends
- Configuration performance
- Resource usage

### 3. Quality Metrics

Track:
- False positive rates
- Rule effectiveness
- Coverage improvements

## Support and Maintenance

### 1. Updating Semgrep

Update the version in:
- `.github/workflows/security-scan.yml`
- `.pre-commit-config.yaml`
- `tooling/run-semgrep.ps1`

### 2. Adding New Rules

1. Add rules to appropriate pack files
2. Update configuration files
3. Test with vulnerable examples
4. Update documentation

### 3. Configuration Changes

1. Test configurations locally
2. Update workflow files
3. Validate with test cases
4. Update documentation

## Conclusion

The GitHub Actions integration provides comprehensive security scanning capabilities for the WordPress Semgrep Rules project. By following this guide, you can effectively use automated security analysis to improve code quality and security posture.

For additional support, refer to:
- [Semgrep Documentation](https://semgrep.dev/docs/)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Pre-commit Documentation](https://pre-commit.com/)
