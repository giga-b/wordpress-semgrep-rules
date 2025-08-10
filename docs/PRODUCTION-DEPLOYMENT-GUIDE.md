# WordPress Semgrep Rules - Production Deployment Guide

## Overview

This guide provides comprehensive instructions for deploying the WordPress Semgrep Rules in production environments. It covers enterprise deployment, CI/CD integration, monitoring, and maintenance procedures.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Installation Methods](#installation-methods)
3. [Configuration Management](#configuration-management)
4. [CI/CD Integration](#cicd-integration)
5. [Monitoring and Alerting](#monitoring-and-alerting)
6. [Performance Optimization](#performance-optimization)
7. [Security Hardening](#security-hardening)
8. [Backup and Recovery](#backup-and-recovery)
9. [Troubleshooting](#troubleshooting)
10. [Maintenance Procedures](#maintenance-procedures)

## Prerequisites

### System Requirements

#### Minimum Requirements
- **Operating System**: Linux (Ubuntu 20.04+, CentOS 8+), macOS 10.15+, Windows 10/11
- **Python**: 3.8 or higher
- **Memory**: 4GB RAM minimum, 8GB recommended
- **Storage**: 2GB free space minimum
- **Network**: Internet access for rule updates

#### Recommended Requirements
- **Operating System**: Linux (Ubuntu 22.04 LTS)
- **Python**: 3.11 or higher
- **Memory**: 16GB RAM
- **Storage**: 10GB free space (SSD recommended)
- **CPU**: 4+ cores
- **Network**: High-speed internet connection

### Software Dependencies

#### Core Dependencies
```bash
# Python packages
pip install semgrep>=1.45.0
pip install pyyaml>=6.0
pip install jinja2>=3.1.0
pip install matplotlib>=3.5.0
pip install seaborn>=0.11.0
pip install pandas>=1.5.0
pip install psutil>=5.9.0

# System packages (Ubuntu/Debian)
sudo apt-get update
sudo apt-get install -y python3-pip python3-venv git curl wget

# System packages (CentOS/RHEL)
sudo yum install -y python3-pip python3-venv git curl wget
```

#### Optional Dependencies
```bash
# For enhanced performance
pip install numpy>=1.21.0
pip install scipy>=1.7.0

# For monitoring and metrics
pip install prometheus-client>=0.14.0
pip install grafana-api>=1.0.0

# For advanced caching
pip install redis>=4.0.0
pip install memcached>=1.0.0
```

## Installation Methods

### Method 1: Direct Installation

#### Step 1: Clone Repository
```bash
# Clone the repository
git clone https://github.com/giga-b/wordpress-semgrep-rules.git
cd wordpress-semgrep-rules

# Create virtual environment
python3 -m venv .venv
source .venv/bin/activate  # Linux/macOS
# or
.venv\Scripts\Activate.ps1  # Windows
```

#### Step 2: Install Dependencies
```bash
# Install Python dependencies
pip install -r requirements.txt

# Install Semgrep
pip install semgrep>=1.45.0
```

#### Step 3: Verify Installation
```bash
# Test basic functionality
semgrep --version
python tooling/validate-configs.py

# Run test scan
semgrep scan --config=configs/basic.yaml tests/vulnerable-examples/ --json
```

### Method 2: Docker Deployment

#### Step 1: Create Dockerfile
```dockerfile
FROM python:3.11-slim

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy requirements and install Python dependencies
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Install Semgrep
RUN pip install --no-cache-dir semgrep>=1.45.0

# Copy application files
COPY . .

# Create non-root user
RUN useradd -m -u 1000 semgrep && chown -R semgrep:semgrep /app
USER semgrep

# Set default command
CMD ["semgrep", "--help"]
```

#### Step 2: Build and Run
```bash
# Build Docker image
docker build -t wordpress-semgrep-rules .

# Run container
docker run -v $(pwd):/workspace wordpress-semgrep-rules \
    semgrep scan --config=configs/basic.yaml /workspace
```

### Method 3: Package Manager Installation

#### Using pip (Python Package)
```bash
# Install from PyPI (when available)
pip install wordpress-semgrep-rules

# Or install from GitHub
pip install git+https://github.com/giga-b/wordpress-semgrep-rules.git
```

## Configuration Management

### Environment-Specific Configurations

#### Development Environment
```yaml
# configs/development.yaml
rules:
  - include: packs/wp-core-security/
  - include: packs/wp-core-quality/
  - exclude: packs/experimental/

scanning:
  timeout: 60
  max_memory: 2048
  parallel: true

reporting:
  format: json
  output: reports/development/
  severity: warning
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
  parallel: true

reporting:
  format: json
  output: reports/staging/
  severity: error
```

#### Production Environment
```yaml
# configs/production.yaml
rules:
  - include: packs/wp-core-security/
  - include: packs/wp-core-quality/

scanning:
  timeout: 300
  max_memory: 8192
  parallel: true
  incremental: true

reporting:
  format: json
  output: reports/production/
  severity: error
  alerting: true
```

### Configuration Validation

#### Automated Validation
```bash
# Validate all configurations
python tooling/validate-configs.py --all

# Validate specific configuration
python tooling/validate-configs.py --config configs/production.yaml

# Test configuration with sample files
python tooling/validate-configs.py --test-scan tests/vulnerable-examples/
```

## CI/CD Integration

### GitHub Actions Integration

#### Basic Workflow
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

#### Advanced Workflow with Auto-fix
```yaml
# .github/workflows/security-scan-with-fix.yml
name: Security Scan with Auto-fix

on:
  push:
    branches: [ main, develop ]

jobs:
  security-scan:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Set up Python
      uses: actions/setup-python@v4
      with:
        python-version: '3.11'
    
    - name: Install Dependencies
      run: |
        pip install semgrep>=1.45.0
        pip install -r requirements.txt
    
    - name: Run Security Scan
      run: |
        semgrep scan --config=configs/strict.yaml \
          --json --output semgrep-results.json
    
    - name: Apply Auto-fixes
      run: |
        python tooling/auto_fix.py \
          --results semgrep-results.json \
          --backup --dry-run
    
    - name: Commit Fixes
      run: |
        git config --local user.email "action@github.com"
        git config --local user.name "GitHub Action"
        git add -A
        git commit -m "Apply security fixes" || exit 0
        git push
```

### GitLab CI Integration

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

### Jenkins Integration

#### Jenkins Pipeline
```groovy
// Jenkinsfile
pipeline {
    agent any
    
    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
        
        stage('Setup') {
            steps {
                sh 'pip install semgrep>=1.45.0'
                sh 'pip install -r requirements.txt'
            }
        }
        
        stage('Security Scan') {
            steps {
                sh '''
                    semgrep scan --config=configs/strict.yaml \
                        --json --output semgrep-results.json
                '''
            }
            post {
                always {
                    archiveArtifacts artifacts: 'semgrep-results.json'
                }
            }
        }
        
        stage('Auto-fix') {
            when {
                branch 'main'
            }
            steps {
                sh '''
                    python tooling/auto_fix.py \
                        --results semgrep-results.json \
                        --backup
                '''
            }
        }
    }
}
```

## Monitoring and Alerting

### Metrics Collection

#### Prometheus Metrics
```python
# tooling/metrics_collector.py
from prometheus_client import Counter, Histogram, Gauge
import time

# Define metrics
scan_duration = Histogram('semgrep_scan_duration_seconds', 'Time spent scanning')
findings_total = Counter('semgrep_findings_total', 'Total findings by severity', ['severity'])
files_scanned = Counter('semgrep_files_scanned_total', 'Total files scanned')
scan_errors = Counter('semgrep_scan_errors_total', 'Total scan errors')

def collect_metrics(scan_results):
    """Collect metrics from scan results"""
    scan_duration.observe(scan_results.get('time', 0))
    
    for finding in scan_results.get('results', []):
        severity = finding.get('extra', {}).get('severity', 'unknown')
        findings_total.labels(severity=severity).inc()
    
    files_scanned.inc(scan_results.get('files_scanned', 0))
```

#### Grafana Dashboard
```json
{
  "dashboard": {
    "title": "WordPress Semgrep Rules - Security Metrics",
    "panels": [
      {
        "title": "Scan Duration",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(semgrep_scan_duration_seconds_sum[5m])",
            "legendFormat": "Scan Duration"
          }
        ]
      },
      {
        "title": "Findings by Severity",
        "type": "piechart",
        "targets": [
          {
            "expr": "semgrep_findings_total",
            "legendFormat": "{{severity}}"
          }
        ]
      }
    ]
  }
}
```

### Alerting Configuration

#### Alert Rules
```yaml
# alerting/rules.yml
groups:
  - name: wordpress-semgrep-rules
    rules:
      - alert: HighSeverityFindings
        expr: semgrep_findings_total{severity="error"} > 10
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "High severity security findings detected"
          description: "{{ $value }} high severity findings detected in the last scan"
      
      - alert: ScanFailure
        expr: semgrep_scan_errors_total > 0
        for: 1m
        labels:
          severity: critical
        annotations:
          summary: "Security scan failed"
          description: "Semgrep scan has failed - immediate attention required"
      
      - alert: ScanTimeout
        expr: semgrep_scan_duration_seconds > 300
        for: 2m
        labels:
          severity: warning
        annotations:
          summary: "Security scan taking too long"
          description: "Scan duration exceeded 5 minutes"
```

## Performance Optimization

### Caching Strategy

#### File System Caching
```python
# tooling/cache_manager.py
import os
import hashlib
import json
from pathlib import Path

class CacheManager:
    def __init__(self, cache_dir=".cache"):
        self.cache_dir = Path(cache_dir)
        self.cache_dir.mkdir(exist_ok=True)
    
    def get_cache_key(self, file_path, config_hash):
        """Generate cache key for file and configuration"""
        file_hash = hashlib.md5(Path(file_path).read_bytes()).hexdigest()
        return f"{file_hash}_{config_hash}"
    
    def get_cached_results(self, cache_key):
        """Retrieve cached results"""
        cache_file = self.cache_dir / f"{cache_key}.json"
        if cache_file.exists():
            return json.loads(cache_file.read_text())
        return None
    
    def cache_results(self, cache_key, results):
        """Cache scan results"""
        cache_file = self.cache_dir / f"{cache_key}.json"
        cache_file.write_text(json.dumps(results))
```

#### Redis Caching
```python
# tooling/redis_cache.py
import redis
import json
import hashlib

class RedisCache:
    def __init__(self, host='localhost', port=6379, db=0):
        self.redis = redis.Redis(host=host, port=port, db=db)
        self.ttl = 3600  # 1 hour
    
    def get_cache_key(self, file_path, config_hash):
        """Generate cache key"""
        file_hash = hashlib.md5(Path(file_path).read_bytes()).hexdigest()
        return f"semgrep:{file_hash}:{config_hash}"
    
    def get_cached_results(self, cache_key):
        """Get cached results from Redis"""
        cached = self.redis.get(cache_key)
        return json.loads(cached) if cached else None
    
    def cache_results(self, cache_key, results):
        """Cache results in Redis"""
        self.redis.setex(cache_key, self.ttl, json.dumps(results))
```

### Parallel Processing

#### Multi-threaded Scanning
```python
# tooling/parallel_scanner.py
import concurrent.futures
import os
from pathlib import Path

class ParallelScanner:
    def __init__(self, max_workers=None):
        self.max_workers = max_workers or os.cpu_count()
    
    def scan_directory(self, directory, config_path):
        """Scan directory with parallel processing"""
        files = list(Path(directory).rglob("*.php"))
        
        with concurrent.futures.ThreadPoolExecutor(max_workers=self.max_workers) as executor:
            futures = [
                executor.submit(self.scan_file, file, config_path)
                for file in files
            ]
            
            results = []
            for future in concurrent.futures.as_completed(futures):
                results.extend(future.result())
        
        return results
    
    def scan_file(self, file_path, config_path):
        """Scan individual file"""
        # Implementation for single file scan
        pass
```

## Security Hardening

### Access Control

#### File Permissions
```bash
# Set secure file permissions
chmod 755 tooling/
chmod 644 tooling/*.py
chmod 600 configs/production.yaml
chmod 644 configs/basic.yaml configs/strict.yaml

# Set ownership
chown -R semgrep:semgrep /opt/wordpress-semgrep-rules
```

#### Network Security
```bash
# Firewall rules (iptables)
# Allow only necessary ports
iptables -A INPUT -p tcp --dport 22 -j ACCEPT  # SSH
iptables -A INPUT -p tcp --dport 80 -j ACCEPT  # HTTP
iptables -A INPUT -p tcp --dport 443 -j ACCEPT # HTTPS
iptables -A INPUT -j DROP

# Or using ufw (Ubuntu)
ufw allow ssh
ufw allow 'Nginx Full'
ufw enable
```

### Secrets Management

#### Environment Variables
```bash
# .env file (do not commit to version control)
SEMGREP_API_TOKEN=your_api_token_here
GITHUB_TOKEN=your_github_token_here
SLACK_WEBHOOK_URL=your_slack_webhook_here
DATABASE_URL=your_database_url_here
```

#### HashiCorp Vault Integration
```python
# tooling/vault_integration.py
import hvac
import os

class VaultManager:
    def __init__(self, vault_url, token):
        self.client = hvac.Client(url=vault_url, token=token)
    
    def get_secret(self, secret_path):
        """Retrieve secret from Vault"""
        response = self.client.secrets.kv.v2.read_secret_version(
            path=secret_path
        )
        return response['data']['data']
    
    def set_secret(self, secret_path, secret_data):
        """Store secret in Vault"""
        self.client.secrets.kv.v2.create_or_update_secret(
            path=secret_path,
            secret=secret_data
        )
```

## Backup and Recovery

### Backup Strategy

#### Automated Backups
```bash
#!/bin/bash
# backup.sh

# Configuration
BACKUP_DIR="/backups/wordpress-semgrep-rules"
DATE=$(date +%Y%m%d_%H%M%S)
PROJECT_DIR="/opt/wordpress-semgrep-rules"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Backup configuration files
tar -czf "$BACKUP_DIR/config_$DATE.tar.gz" \
    -C "$PROJECT_DIR" configs/ tooling/ docs/

# Backup scan results
tar -czf "$BACKUP_DIR/results_$DATE.tar.gz" \
    -C "$PROJECT_DIR" reports/ metrics.db

# Backup cache (optional)
tar -czf "$BACKUP_DIR/cache_$DATE.tar.gz" \
    -C "$PROJECT_DIR" .cache/

# Clean old backups (keep last 7 days)
find "$BACKUP_DIR" -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $BACKUP_DIR"
```

#### Database Backup
```python
# tooling/database_backup.py
import sqlite3
import shutil
from datetime import datetime
import os

class DatabaseBackup:
    def __init__(self, db_path, backup_dir):
        self.db_path = db_path
        self.backup_dir = backup_dir
        os.makedirs(backup_dir, exist_ok=True)
    
    def create_backup(self):
        """Create database backup"""
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        backup_path = os.path.join(self.backup_dir, f"metrics_{timestamp}.db")
        
        # Create backup
        shutil.copy2(self.db_path, backup_path)
        
        # Verify backup
        if self.verify_backup(backup_path):
            print(f"Backup created successfully: {backup_path}")
            return backup_path
        else:
            print("Backup verification failed")
            return None
    
    def verify_backup(self, backup_path):
        """Verify backup integrity"""
        try:
            conn = sqlite3.connect(backup_path)
            conn.execute("SELECT COUNT(*) FROM sqlite_master")
            conn.close()
            return True
        except Exception:
            return False
```

### Recovery Procedures

#### Configuration Recovery
```bash
#!/bin/bash
# recover.sh

# Configuration
BACKUP_DIR="/backups/wordpress-semgrep-rules"
PROJECT_DIR="/opt/wordpress-semgrep-rules"
BACKUP_DATE="$1"  # Format: YYYYMMDD_HHMMSS

if [ -z "$BACKUP_DATE" ]; then
    echo "Usage: $0 YYYYMMDD_HHMMSS"
    exit 1
fi

# Stop services
systemctl stop wordpress-semgrep-rules

# Restore configuration
tar -xzf "$BACKUP_DIR/config_$BACKUP_DATE.tar.gz" -C "$PROJECT_DIR"

# Restore results
tar -xzf "$BACKUP_DIR/results_$BACKUP_DATE.tar.gz" -C "$PROJECT_DIR"

# Restore cache
tar -xzf "$BACKUP_DIR/cache_$BACKUP_DATE.tar.gz" -C "$PROJECT_DIR"

# Set permissions
chown -R semgrep:semgrep "$PROJECT_DIR"
chmod 755 "$PROJECT_DIR/tooling/"
chmod 644 "$PROJECT_DIR/configs/"*.yaml

# Start services
systemctl start wordpress-semgrep-rules

echo "Recovery completed successfully"
```

## Troubleshooting

### Common Issues

#### Issue 1: Semgrep Installation Problems
```bash
# Problem: Semgrep installation fails
# Solution: Use alternative installation methods

# Method 1: Using pip with specific version
pip install semgrep==1.45.0

# Method 2: Using conda
conda install -c conda-forge semgrep

# Method 3: Using Docker
docker run -v $(pwd):/src returntocorp/semgrep semgrep scan --config=configs/basic.yaml /src
```

#### Issue 2: Memory Issues
```bash
# Problem: Out of memory during scan
# Solution: Optimize memory usage

# Increase swap space
sudo fallocate -l 4G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile

# Optimize Python memory
export PYTHONOPTIMIZE=1
export PYTHONHASHSEED=0

# Use incremental scanning
semgrep scan --config=configs/basic.yaml --enable-version-check=false --disable-version-check
```

#### Issue 3: Performance Issues
```bash
# Problem: Slow scan performance
# Solution: Performance optimization

# Enable caching
export SEMGREP_CACHE_DIR="/tmp/semgrep-cache"

# Use parallel processing
semgrep scan --config=configs/basic.yaml --jobs 4

# Exclude unnecessary directories
semgrep scan --config=configs/basic.yaml --exclude-dir=vendor --exclude-dir=node_modules
```

### Debug Mode

#### Enable Debug Logging
```bash
# Set debug environment variables
export SEMGREP_VERBOSE=1
export SEMGREP_DEBUG=1
export PYTHONPATH="${PYTHONPATH}:/opt/wordpress-semgrep-rules"

# Run with debug output
semgrep scan --config=configs/basic.yaml --verbose --debug
```

#### Log Analysis
```python
# tooling/log_analyzer.py
import re
import json
from pathlib import Path

class LogAnalyzer:
    def __init__(self, log_file):
        self.log_file = Path(log_file)
    
    def analyze_errors(self):
        """Analyze error patterns in logs"""
        errors = []
        with open(self.log_file) as f:
            for line in f:
                if 'ERROR' in line or 'Exception' in line:
                    errors.append(line.strip())
        return errors
    
    def generate_report(self):
        """Generate analysis report"""
        errors = self.analyze_errors()
        return {
            'total_errors': len(errors),
            'error_types': self.categorize_errors(errors),
            'recommendations': self.get_recommendations(errors)
        }
```

## Maintenance Procedures

### Regular Maintenance Tasks

#### Daily Tasks
```bash
#!/bin/bash
# daily_maintenance.sh

# 1. Check system resources
df -h
free -h
top -n 1

# 2. Verify Semgrep installation
semgrep --version

# 3. Run quick health check
python tooling/health_check.py

# 4. Clean old cache files
find .cache/ -mtime +1 -delete

# 5. Check for updates
python tooling/check_updates.py
```

#### Weekly Tasks
```bash
#!/bin/bash
# weekly_maintenance.sh

# 1. Update Semgrep rules
pip install --upgrade semgrep

# 2. Run full test suite
python tests/run-automated-tests.py

# 3. Generate performance report
python tooling/performance-optimizer.py --report

# 4. Backup configuration
./backup.sh

# 5. Clean old reports
find reports/ -mtime +30 -delete
```

#### Monthly Tasks
```bash
#!/bin/bash
# monthly_maintenance.sh

# 1. Security audit
python tooling/security_audit.py

# 2. Performance optimization
python tooling/performance-optimizer.py --optimize

# 3. Update documentation
python tooling/update_docs.py

# 4. Review and rotate logs
logrotate /etc/logrotate.d/wordpress-semgrep-rules

# 5. System health check
python tooling/system_health_check.py
```

### Update Procedures

#### Rule Updates
```bash
# Update rules from repository
git pull origin main

# Validate updated rules
python tooling/validate-rules.py

# Test updated rules
python tests/run-automated-tests.py

# Deploy to production
./deploy.sh production
```

#### Configuration Updates
```bash
# Backup current configuration
cp configs/production.yaml configs/production.yaml.backup

# Update configuration
# Edit configs/production.yaml

# Validate configuration
python tooling/validate-configs.py --config configs/production.yaml

# Test configuration
semgrep scan --config=configs/production.yaml tests/vulnerable-examples/ --dry-run

# Apply configuration
systemctl reload wordpress-semgrep-rules
```

### Health Monitoring

#### Health Check Script
```python
# tooling/health_check.py
import subprocess
import json
import sys
from pathlib import Path

class HealthChecker:
    def __init__(self):
        self.checks = []
    
    def check_semgrep_installation(self):
        """Check if Semgrep is properly installed"""
        try:
            result = subprocess.run(['semgrep', '--version'], 
                                  capture_output=True, text=True)
            return result.returncode == 0
        except FileNotFoundError:
            return False
    
    def check_configuration_files(self):
        """Check if configuration files exist and are valid"""
        config_files = ['configs/basic.yaml', 'configs/strict.yaml']
        for config_file in config_files:
            if not Path(config_file).exists():
                return False
        return True
    
    def check_database_connectivity(self):
        """Check database connectivity"""
        try:
            import sqlite3
            conn = sqlite3.connect('metrics.db')
            conn.close()
            return True
        except Exception:
            return False
    
    def run_all_checks(self):
        """Run all health checks"""
        checks = {
            'semgrep_installation': self.check_semgrep_installation(),
            'configuration_files': self.check_configuration_files(),
            'database_connectivity': self.check_database_connectivity()
        }
        
        all_passed = all(checks.values())
        
        return {
            'status': 'healthy' if all_passed else 'unhealthy',
            'checks': checks,
            'timestamp': datetime.now().isoformat()
        }

if __name__ == '__main__':
    checker = HealthChecker()
    result = checker.run_all_checks()
    print(json.dumps(result, indent=2))
    sys.exit(0 if result['status'] == 'healthy' else 1)
```

## Conclusion

This production deployment guide provides comprehensive instructions for deploying and maintaining the WordPress Semgrep Rules in production environments. Follow these guidelines to ensure a secure, performant, and reliable deployment.

For additional support and troubleshooting, refer to the [Troubleshooting Guide](troubleshooting.md) and [Community Guidelines](COMMUNITY-GUIDELINES.md).

## Appendix

### A. Configuration Templates
- [Development Configuration](configs/development.yaml)
- [Staging Configuration](configs/staging.yaml)
- [Production Configuration](configs/production.yaml)

### B. Monitoring Dashboards
- [Grafana Dashboard Configuration](monitoring/grafana-dashboard.json)
- [Prometheus Alert Rules](monitoring/alert-rules.yml)

### C. Scripts and Tools
- [Backup Script](tooling/backup.sh)
- [Recovery Script](tooling/recover.sh)
- [Health Check Script](tooling/health_check.py)
- [Maintenance Scripts](tooling/maintenance/)

### D. Security Checklists
- [Deployment Security Checklist](security/deployment-checklist.md)
- [Production Hardening Guide](security/hardening-guide.md)
