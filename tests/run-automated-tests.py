#!/usr/bin/env python3
"""
WordPress Semgrep Rules - Automated Testing Framework

This script provides comprehensive automated testing for WordPress Semgrep rules,
including test execution, regression testing, performance benchmarking, and
result reporting.

Usage:
    python run-automated-tests.py [options]

Options:
    --config <file>     Use custom test configuration file
    --rules <path>      Path to rules directory
    --tests <path>      Path to test files directory
    --output <file>     Output file for results
    --performance       Run performance benchmarks
    --regression        Run regression tests
    --verbose           Verbose output
    --json              Output results in JSON format
    --html              Generate HTML report
"""

import os
import sys
import json
import time
import subprocess
import argparse
import datetime
from pathlib import Path
from typing import Dict, List, Any, Optional
import xml.etree.ElementTree as ET
from dataclasses import dataclass, asdict
import hashlib
import shutil

@dataclass
class TestResult:
    """Represents a single test result"""
    test_file: str
    rule_file: str
    rule_pack: str
    expected_findings: int
    actual_findings: int
    findings: List[Dict[str, Any]]
    duration: float
    status: str  # 'pass', 'fail', 'error'
    error_message: Optional[str] = None
    performance_metrics: Optional[Dict[str, Any]] = None

@dataclass
class TestSuite:
    """Represents a test suite"""
    name: str
    description: str
    test_files: List[str]
    rule_packs: List[str]
    expected_results: Dict[str, int]

@dataclass
class TestReport:
    """Comprehensive test report"""
    timestamp: str
    duration: float
    total_tests: int
    passed_tests: int
    failed_tests: int
    error_tests: int
    test_results: List[TestResult]
    performance_summary: Dict[str, Any]
    recommendations: List[str]

class AutomatedTestRunner:
    """Main automated testing framework"""
    
    def __init__(self, config_path: Optional[str] = None):
        self.config = self._load_config(config_path)
        self.results: List[TestResult] = []
        self.start_time = time.time()
        
    def _load_config(self, config_path: Optional[str] = None) -> Dict[str, Any]:
        """Load test configuration"""
        default_config = {
            'semgrep_binary': 'semgrep',
            'rules_path': '../packs/',
            'tests_path': './',
            'output_path': './test-results/',
            'test_suites': {
                'basic_security': {
                    'name': 'Basic Security Rules',
                    'description': 'Core WordPress security rules',
                    'test_files': [
                        'vulnerable-examples/nonce-vulnerable.php',
                        'vulnerable-examples/capability-vulnerable.php',
                        'vulnerable-examples/sanitization-vulnerable.php',
                        'safe-examples/nonce-safe.php',
                        'safe-examples/capability-safe.php',
                        'safe-examples/sanitization-safe.php'
                    ],
                    'rule_packs': ['wp-core-security'],
                    'expected_results': {
                        'vulnerable-examples/nonce-vulnerable.php': 3,
                        'vulnerable-examples/capability-vulnerable.php': 4,
                        'vulnerable-examples/sanitization-vulnerable.php': 5,
                        'safe-examples/nonce-safe.php': 0,
                        'safe-examples/capability-safe.php': 0,
                        'safe-examples/sanitization-safe.php': 0
                    }
                },
                'advanced_security': {
                    'name': 'Advanced Security Rules',
                    'description': 'Advanced WordPress security patterns',
                    'test_files': [
                        'vulnerable-examples/rest-api-vulnerable.php',
                        'vulnerable-examples/ajax-vulnerable.php',
                        'vulnerable-examples/sql-injection-vulnerable.php',
                        'vulnerable-examples/xss-vulnerable.php',
                        'safe-examples/rest-api-safe.php',
                        'safe-examples/ajax-safe.php',
                        'safe-examples/sql-safe.php',
                        'safe-examples/xss-safe.php'
                    ],
                    'rule_packs': ['wp-core-security'],
                    'expected_results': {
                        'vulnerable-examples/rest-api-vulnerable.php': 4,
                        'vulnerable-examples/ajax-vulnerable.php': 3,
                        'vulnerable-examples/sql-injection-vulnerable.php': 2,
                        'vulnerable-examples/xss-vulnerable.php': 6,
                        'safe-examples/rest-api-safe.php': 0,
                        'safe-examples/ajax-safe.php': 0,
                        'safe-examples/sql-safe.php': 0,
                        'safe-examples/xss-safe.php': 0
                    }
                },
                'taint_analysis': {
                    'name': 'Taint Analysis Rules',
                    'description': 'Advanced taint analysis patterns',
                    'test_files': [
                        'vulnerable-examples/taint-analysis-vulnerable.php',
                        'vulnerable-examples/sql-injection-taint-vulnerable.php',
                        'vulnerable-examples/xss-taint-vulnerable.php',
                        'safe-examples/taint-analysis-safe.php',
                        'safe-examples/sql-injection-taint-safe.php',
                        'safe-examples/xss-taint-safe.php'
                    ],
                    'rule_packs': ['experimental'],
                    'expected_results': {
                        'vulnerable-examples/taint-analysis-vulnerable.php': 8,
                        'vulnerable-examples/sql-injection-taint-vulnerable.php': 6,
                        'vulnerable-examples/xss-taint-vulnerable.php': 7,
                        'safe-examples/taint-analysis-safe.php': 0,
                        'safe-examples/sql-injection-taint-safe.php': 0,
                        'safe-examples/xss-taint-safe.php': 0
                    }
                }
            }
        }
        
        if config_path and os.path.exists(config_path):
            with open(config_path, 'r') as f:
                custom_config = json.load(f)
                default_config.update(custom_config)
                
        return default_config
    
    def run_all_tests(self) -> TestReport:
        """Run all test suites"""
        print("Starting Automated Testing Framework...")
        print("=" * 50)
        
        # Create output directory
        os.makedirs(self.config['output_path'], exist_ok=True)
        
        # Run each test suite
        for suite_name, suite_config in self.config['test_suites'].items():
            print(f"\nRunning Test Suite: {suite_config['name']}")
            print("-" * 40)
            self._run_test_suite(suite_name, suite_config)
        
        # Generate comprehensive report
        return self._generate_report()
    
    def _run_test_suite(self, suite_name: str, suite_config: Dict[str, Any]):
        """Run a specific test suite"""
        print(f"\nRunning Test Suite: {suite_config['name']}")
        print("-" * 40)
        
        for test_file in suite_config['test_files']:
            test_path = os.path.join(self.config['tests_path'], test_file)
            
            if not os.path.exists(test_path):
                print(f"Warning: Test file not found: {test_path}")
                continue
            
            print(f"Testing file: {test_file}")
            
            for rule_pack in suite_config['rule_packs']:
                rule_pack_path = os.path.join(self.config['rules_path'], rule_pack)
                
                if not os.path.exists(rule_pack_path):
                    print(f"Warning: Rule pack not found: {rule_pack_path}")
                    continue
                
                print(f"  Using rule pack: {rule_pack}")
                
                # Run tests against all rules in the pack
                for rule_file in os.listdir(rule_pack_path):
                    if rule_file.endswith('.yaml'):
                        rule_path = os.path.join(rule_pack_path, rule_file)
                        # Handle nested expected results structure
                        test_expected = suite_config['expected_results'].get(test_file, {})
                        if isinstance(test_expected, dict):
                            expected_findings = test_expected.get(rule_file, 0)
                        else:
                            # Fallback for flat structure
                            expected_findings = test_expected
                        
                        result = self._run_single_test(
                            test_path, rule_path, rule_pack, rule_file, expected_findings
                        )
                        self.results.append(result)
    
    def _run_single_test(self, test_file: str, rule_file: str, rule_pack: str, 
                        rule_name: str, expected_findings: int) -> TestResult:
        """Run a single test case"""
        start_time = time.time()
        
        try:
            # Run semgrep
            cmd = [
                self.config['semgrep_binary'],
                '--config', rule_file,
                '--json',
                '--quiet',
                test_file
            ]
            
            print(f"    Running: {rule_name} (expected: {expected_findings})")
            
            result = subprocess.run(cmd, capture_output=True, text=True, timeout=60)
            
            # Parse results
            if result.returncode == 0:
                findings = json.loads(result.stdout) if result.stdout.strip() else []
                actual_findings = len(findings)
                
                # Determine test status
                if actual_findings == expected_findings:
                    status = 'pass'
                else:
                    status = 'fail'
                
                error_message = None
            else:
                status = 'error'
                actual_findings = 0
                findings = []
                error_message = result.stderr
            
            duration = time.time() - start_time
            
            # Print result
            if status == 'pass':
                print(f"      ✓ PASS: {actual_findings} findings (expected {expected_findings})")
            elif status == 'fail':
                print(f"      ✗ FAIL: {actual_findings} findings (expected {expected_findings})")
            else:
                print(f"      ✗ ERROR: {error_message}")
            
            return TestResult(
                test_file=test_file,
                rule_file=rule_file,
                rule_pack=rule_pack,
                expected_findings=expected_findings,
                actual_findings=actual_findings,
                findings=findings,
                duration=duration,
                status=status,
                error_message=error_message
            )
            
        except subprocess.TimeoutExpired:
            return TestResult(
                test_file=test_file,
                rule_file=rule_file,
                rule_pack=rule_pack,
                expected_findings=expected_findings,
                actual_findings=0,
                findings=[],
                duration=60.0,
                status='error',
                error_message='Test timeout after 60 seconds'
            )
        except Exception as e:
            return TestResult(
                test_file=test_file,
                rule_file=rule_file,
                rule_pack=rule_pack,
                expected_findings=expected_findings,
                actual_findings=0,
                findings=[],
                duration=time.time() - start_time,
                status='error',
                error_message=str(e)
            )
    
    def run_performance_benchmarks(self) -> Dict[str, Any]:
        """Run performance benchmarks"""
        print("\nRunning Performance Benchmarks...")
        print("-" * 40)
        
        benchmarks = {}
        
        # Test different configurations
        configs = ['basic.yaml', 'strict.yaml', 'plugin-development.yaml']
        
        for config in configs:
            config_path = os.path.join('../configs', config)
            if os.path.exists(config_path):
                print(f"Benchmarking {config}...")
                
                start_time = time.time()
                memory_before = self._get_memory_usage()
                
                # Run semgrep on test directory
                cmd = [
                    self.config['semgrep_binary'],
                    '--config', config_path,
                    '--json',
                    '--quiet',
                    self.config['tests_path']
                ]
                
                result = subprocess.run(cmd, capture_output=True, text=True, timeout=300)
                
                duration = time.time() - start_time
                memory_after = self._get_memory_usage()
                
                benchmarks[config] = {
                    'duration': duration,
                    'memory_usage': memory_after - memory_before,
                    'findings_count': len(json.loads(result.stdout)) if result.stdout.strip() else 0,
                    'success': result.returncode == 0
                }
        
        return benchmarks
    
    def _get_memory_usage(self) -> float:
        """Get current memory usage in MB"""
        try:
            import psutil
            process = psutil.Process()
            return process.memory_info().rss / 1024 / 1024
        except ImportError:
            return 0.0
    
    def _generate_report(self) -> TestReport:
        """Generate comprehensive test report"""
        total_tests = len(self.results)
        passed_tests = len([r for r in self.results if r.status == 'pass'])
        failed_tests = len([r for r in self.results if r.status == 'fail'])
        error_tests = len([r for r in self.results if r.status == 'error'])
        
        # Generate recommendations
        recommendations = self._generate_recommendations()
        
        # Performance summary
        performance_summary = {
            'total_duration': time.time() - self.start_time,
            'average_test_duration': sum(r.duration for r in self.results) / total_tests if total_tests > 0 else 0,
            'slowest_test': max(self.results, key=lambda x: x.duration).duration if self.results else 0
        }
        
        return TestReport(
            timestamp=datetime.datetime.now().isoformat(),
            duration=time.time() - self.start_time,
            total_tests=total_tests,
            passed_tests=passed_tests,
            failed_tests=failed_tests,
            error_tests=error_tests,
            test_results=self.results,
            performance_summary=performance_summary,
            recommendations=recommendations
        )
    
    def _generate_recommendations(self) -> List[str]:
        """Generate recommendations based on test results"""
        recommendations = []
        
        # Analyze failed tests
        failed_tests = [r for r in self.results if r.status == 'fail']
        if failed_tests:
            recommendations.append(f"Review {len(failed_tests)} failed tests for rule accuracy")
        
        # Analyze error tests
        error_tests = [r for r in self.results if r.status == 'error']
        if error_tests:
            recommendations.append(f"Investigate {len(error_tests)} test errors")
        
        # Performance recommendations
        slow_tests = [r for r in self.results if r.duration > 10.0]
        if slow_tests:
            recommendations.append(f"Optimize {len(slow_tests)} slow tests (>10s)")
        
        # Coverage recommendations
        total_rules = len(set(r.rule_file for r in self.results))
        if total_rules < 10:
            recommendations.append("Consider adding more test cases for better coverage")
        
        return recommendations
    
    def save_report(self, report: TestReport, output_file: str):
        """Save test report to file"""
        # Convert dataclasses to dictionaries
        report_dict = asdict(report)
        
        with open(output_file, 'w') as f:
            json.dump(report_dict, f, indent=2)
        
        print(f"\nTest report saved to: {output_file}")
    
    def generate_html_report(self, report: TestReport, output_file: str):
        """Generate HTML report"""
        html_content = self._generate_html_content(report)
        
        with open(output_file, 'w') as f:
            f.write(html_content)
        
        print(f"HTML report generated: {output_file}")
    
    def _generate_html_content(self, report: TestReport) -> str:
        """Generate HTML content for the report"""
        return f"""
<!DOCTYPE html>
<html>
<head>
    <title>WordPress Semgrep Rules - Test Report</title>
    <style>
        body {{ font-family: Arial, sans-serif; margin: 20px; }}
        .header {{ background-color: #f0f0f0; padding: 20px; border-radius: 5px; }}
        .summary {{ margin: 20px 0; }}
        .test-result {{ margin: 10px 0; padding: 10px; border-radius: 3px; }}
        .pass {{ background-color: #d4edda; border: 1px solid #c3e6cb; }}
        .fail {{ background-color: #f8d7da; border: 1px solid #f5c6cb; }}
        .error {{ background-color: #fff3cd; border: 1px solid #ffeaa7; }}
        .recommendations {{ background-color: #e2e3e5; padding: 15px; border-radius: 5px; }}
    </style>
</head>
<body>
    <div class="header">
        <h1>WordPress Semgrep Rules - Test Report</h1>
        <p>Generated: {report.timestamp}</p>
        <p>Duration: {report.duration:.2f} seconds</p>
    </div>
    
    <div class="summary">
        <h2>Test Summary</h2>
        <p>Total Tests: {report.total_tests}</p>
        <p>Passed: {report.passed_tests}</p>
        <p>Failed: {report.failed_tests}</p>
        <p>Errors: {report.error_tests}</p>
        <p>Success Rate: {(report.passed_tests / report.total_tests * 100):.1f}%</p>
    </div>
    
    <div class="recommendations">
        <h2>Recommendations</h2>
        <ul>
            {''.join(f'<li>{rec}</li>' for rec in report.recommendations)}
        </ul>
    </div>
    
    <h2>Test Results</h2>
    {''.join(self._generate_test_result_html(result) for result in report.test_results)}
</body>
</html>
"""
    
    def _generate_test_result_html(self, result: TestResult) -> str:
        """Generate HTML for a single test result"""
        return f"""
    <div class="test-result {result.status}">
        <h3>{os.path.basename(result.test_file)} - {os.path.basename(result.rule_file)}</h3>
        <p>Status: {result.status.upper()}</p>
        <p>Expected Findings: {result.expected_findings}</p>
        <p>Actual Findings: {result.actual_findings}</p>
        <p>Duration: {result.duration:.2f}s</p>
        {f'<p>Error: {result.error_message}</p>' if result.error_message else ''}
    </div>
"""

def main():
    """Main entry point"""
    parser = argparse.ArgumentParser(description='WordPress Semgrep Rules Automated Testing')
    parser.add_argument('--config', help='Custom test configuration file')
    parser.add_argument('--rules', help='Path to rules directory')
    parser.add_argument('--tests', help='Path to test files directory')
    parser.add_argument('--output', help='Output file for results')
    parser.add_argument('--performance', action='store_true', help='Run performance benchmarks')
    parser.add_argument('--regression', action='store_true', help='Run regression tests')
    parser.add_argument('--verbose', action='store_true', help='Verbose output')
    parser.add_argument('--json', action='store_true', help='Output results in JSON format')
    parser.add_argument('--html', action='store_true', help='Generate HTML report')
    
    args = parser.parse_args()
    
    # Initialize test runner
    runner = AutomatedTestRunner(args.config)
    
    # Override config with command line arguments
    if args.rules:
        runner.config['rules_path'] = args.rules
    if args.tests:
        runner.config['tests_path'] = args.tests
    
    # Run tests
    report = runner.run_all_tests()
    
    # Run performance benchmarks if requested
    if args.performance:
        performance_results = runner.run_performance_benchmarks()
        report.performance_summary.update(performance_results)
    
    # Output results
    output_file = args.output or 'test-results/automated-test-report.json'
    
    if args.json:
        runner.save_report(report, output_file)
    
    if args.html:
        html_file = output_file.replace('.json', '.html')
        runner.generate_html_report(report, html_file)
    
    # Print summary
    print(f"\nTest Summary:")
    print(f"Total Tests: {report.total_tests}")
    print(f"Passed: {report.passed_tests}")
    print(f"Failed: {report.failed_tests}")
    print(f"Errors: {report.error_tests}")
    print(f"Success Rate: {(report.passed_tests / report.total_tests * 100):.1f}%")
    print(f"Duration: {report.duration:.2f} seconds")
    
    # Exit with error code if there are failures
    if report.failed_tests > 0 or report.error_tests > 0:
        sys.exit(1)

if __name__ == '__main__':
    main()
