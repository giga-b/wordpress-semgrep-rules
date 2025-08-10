#!/usr/bin/env python3
"""
WordPress Semgrep Rules Performance Optimizer

This script analyzes rule performance and provides optimization recommendations
to meet the <30 second scan time requirement.
"""

import json
import os
import sys
import time
import subprocess
from pathlib import Path
from typing import Dict, List, Any

class PerformanceOptimizer:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.configs_dir = self.project_root / "configs"
        self.packs_dir = self.project_root / "packs"
        self.tests_dir = self.project_root / "tests"
        
    def analyze_rule_complexity(self, rule_file: Path) -> Dict[str, Any]:
        """Analyze the complexity of a rule file."""
        try:
            with open(rule_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Count rules
            rule_count = content.count('- id:')
            
            # Count complex patterns
            complex_patterns = content.count('pattern-either:') + content.count('patterns:')
            
            # Count taint analysis rules
            taint_rules = content.count('taint-mode:')
            
            # Estimate complexity score
            complexity_score = rule_count + (complex_patterns * 2) + (taint_rules * 5)
            
            return {
                'file': str(rule_file),
                'rule_count': rule_count,
                'complex_patterns': complex_patterns,
                'taint_rules': taint_rules,
                'complexity_score': complexity_score,
                'file_size_kb': len(content) / 1024
            }
        except Exception as e:
            return {
                'file': str(rule_file),
                'error': str(e),
                'complexity_score': 0
            }
    
    def scan_performance_test(self, config_file: str, target_dir: str = None) -> Dict[str, Any]:
        """Run a performance test with a specific configuration."""
        if target_dir is None:
            target_dir = str(self.tests_dir)
        
        output_file = f"performance-test-{Path(config_file).stem}.json"
        
        start_time = time.time()
        
        try:
            cmd = [
                'semgrep', 'scan',
                '--config', config_file,
                '--json',
                '--output', output_file,
                '--timeout', '60',
                '--max-memory', '1000',
                target_dir
            ]
            
            result = subprocess.run(cmd, capture_output=True, text=True, cwd=self.project_root)
            
            end_time = time.time()
            scan_time = end_time - start_time
            
            if result.returncode == 0:
                with open(output_file, 'r') as f:
                    scan_results = json.load(f)
                
                return {
                    'config': config_file,
                    'scan_time': scan_time,
                    'success': True,
                    'results': scan_results,
                    'files_scanned': len(scan_results.get('paths', {}).get('scanned', [])),
                    'findings': len(scan_results.get('results', [])),
                    'rules_run': len(scan_results.get('results', [])),
                    'memory_usage': scan_results.get('time', {}).get('max_memory_bytes', 0) / 1024 / 1024
                }
            else:
                return {
                    'config': config_file,
                    'scan_time': scan_time,
                    'success': False,
                    'error': result.stderr
                }
                
        except Exception as e:
            return {
                'config': config_file,
                'success': False,
                'error': str(e)
            }
    
    def generate_optimization_report(self) -> Dict[str, Any]:
        """Generate a comprehensive optimization report."""
        report = {
            'timestamp': time.strftime('%Y-%m-%d %H:%M:%S'),
            'rule_analysis': [],
            'performance_tests': [],
            'recommendations': []
        }
        
        # Analyze all rule files
        for pack_dir in self.packs_dir.iterdir():
            if pack_dir.is_dir():
                for rule_file in pack_dir.rglob('*.yaml'):
                    analysis = self.analyze_rule_complexity(rule_file)
                    report['rule_analysis'].append(analysis)
        
        # Run performance tests
        configs = ['configs/basic.yaml', 'configs/strict.yaml', 'configs/plugin-development.yaml']
        
        for config in configs:
            if os.path.exists(config):
                test_result = self.scan_performance_test(config)
                report['performance_tests'].append(test_result)
        
        # Generate recommendations
        report['recommendations'] = self.generate_recommendations(report)
        
        return report
    
    def generate_recommendations(self, report: Dict[str, Any]) -> List[str]:
        """Generate optimization recommendations based on analysis."""
        recommendations = []
        
        # Analyze rule complexity
        high_complexity_rules = [r for r in report['rule_analysis'] if r.get('complexity_score', 0) > 50]
        if high_complexity_rules:
            recommendations.append(f"Found {len(high_complexity_rules)} high-complexity rule files that may impact performance")
        
        # Analyze performance test results
        for test in report['performance_tests']:
            if test.get('success') and test.get('scan_time', 0) > 30:
                recommendations.append(f"Scan time for {test['config']} exceeds 30 seconds ({test['scan_time']:.2f}s)")
            
            if test.get('memory_usage', 0) > 500:
                recommendations.append(f"Memory usage for {test['config']} is high ({test['memory_usage']:.2f}MB)")
        
        # General recommendations
        recommendations.extend([
            "Consider using rule-filters to exclude non-critical rules for faster scanning",
            "Implement incremental scanning for changed files only",
            "Use exclude patterns to skip irrelevant directories",
            "Consider parallel processing for large codebases",
            "Optimize complex regex patterns in rules",
            "Use taint analysis sparingly as it's computationally expensive"
        ])
        
        return recommendations
    
    def create_optimized_config(self, target_scan_time: int = 30) -> str:
        """Create an optimized configuration file."""
        optimized_config = f"""# WordPress Optimized Configuration
# Generated for target scan time: {target_scan_time} seconds

include:
  # Essential security rules only
  - packs/wp-core-security/nonce-verification.yaml
  - packs/wp-core-security/capability-checks.yaml
  - packs/wp-core-security/sanitization-functions.yaml

# Comprehensive exclusions for performance
exclude:
  - "**/node_modules/**"
  - "**/vendor/**"
  - "**/tests/**"
  - "**/*.min.*"
  - "**/wp-admin/**"
  - "**/wp-includes/**"
  - "**/wp-content/uploads/**"
  - "**/wp-content/cache/**"
  - "**/wp-content/backup*/**"
  - "**/wp-content/blogs.dir/**"
  - "**/wp-content/upgrade/**"
  - "**/wp-content/mu-plugins/**"
  - "**/wp-content/plugins/hello.php"
  - "**/wp-content/themes/twenty*/**"

# Performance-focused rule filters
rule-filters:
  - exclude: "wordpress.performance.*"
  - exclude: "wordpress.quality.*"
  - exclude: "wordpress.xss.*"
  - exclude: "wordpress.sql.*"
  - exclude: "wordpress.ajax.*"
  - exclude: "wordpress.rest-api.*"

# Semgrep performance settings
semgrep:
  max-memory: 1000
  timeout: {target_scan_time}
  jobs: 4
  skip-unknown-extensions: true
  use-git-ignore: true
  enable-version-check: false
"""
        
        output_file = self.configs_dir / f"optimized-{target_scan_time}s.yaml"
        with open(output_file, 'w') as f:
            f.write(optimized_config)
        
        return str(output_file)

def main():
    if len(sys.argv) < 2:
        print("Usage: python optimize-performance.py <project_root>")
        sys.exit(1)
    
    project_root = sys.argv[1]
    optimizer = PerformanceOptimizer(project_root)
    
    print("🔍 Analyzing WordPress Semgrep Rules Performance...")
    
    # Generate optimization report
    report = optimizer.generate_optimization_report()
    
    # Save report
    report_file = Path(project_root) / "performance-optimization-report.json"
    with open(report_file, 'w') as f:
        json.dump(report, f, indent=2)
    
    # Create optimized configurations
    optimized_30s = optimizer.create_optimized_config(30)
    optimized_15s = optimizer.create_optimized_config(15)
    
    print(f"\n📊 Performance Analysis Complete!")
    print(f"📄 Report saved to: {report_file}")
    print(f"⚡ Optimized configs created:")
    print(f"   - {optimized_30s} (target: 30s)")
    print(f"   - {optimized_15s} (target: 15s)")
    
    print(f"\n🔧 Recommendations:")
    for i, rec in enumerate(report['recommendations'], 1):
        print(f"   {i}. {rec}")
    
    print(f"\n📈 Performance Test Results:")
    for test in report['performance_tests']:
        if test.get('success'):
            print(f"   - {test['config']}: {test['scan_time']:.2f}s, {test['files_scanned']} files, {test['findings']} findings")
        else:
            print(f"   - {test['config']}: FAILED - {test.get('error', 'Unknown error')}")

if __name__ == "__main__":
    main()
