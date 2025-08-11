#!/usr/bin/env python3
"""
Automated Testing Framework for WordPress Semgrep Rules
Part of Task 1.1.5: Set up automated testing framework

This framework provides comprehensive testing capabilities for:
- Rule validation and syntax checking
- Test case execution and validation
- Performance benchmarking
- False positive analysis
- Integration testing with real WordPress plugins
"""

import os
import sys
import json
import subprocess
import time
import argparse
import logging
from pathlib import Path
from typing import Dict, List, Optional, Tuple
from dataclasses import dataclass
from datetime import datetime

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

@dataclass
class TestResult:
    """Represents the result of a single test case"""
    test_name: str
    rule_id: str
    status: str  # 'pass', 'fail', 'error'
    expected_findings: int
    actual_findings: int
    execution_time: float
    error_message: Optional[str] = None
    findings: List[Dict] = None

@dataclass
class TestSuite:
    """Represents a collection of test cases"""
    name: str
    description: str
    test_cases: List[Dict]
    rules: List[str]

class WordPressSemgrepTester:
    """Main testing framework for WordPress Semgrep rules"""
    
    def __init__(self, config_path: str = "tooling/test-config.json"):
        self.config_path = Path(config_path)
        self.config = self._load_config()
        self.results_dir = Path("test-results")
        self.results_dir.mkdir(exist_ok=True)
        
    def _load_config(self) -> Dict:
        """Load test configuration"""
        if self.config_path.exists():
            with open(self.config_path, 'r') as f:
                return json.load(f)
        else:
            return self._create_default_config()
    
    def _create_default_config(self) -> Dict:
        """Create default test configuration"""
        config = {
            "test_suites": {
                "basic_validation": {
                    "description": "Basic rule validation tests",
                    "test_cases": [
                        {
                            "name": "nonce_verification_test",
                            "description": "Test nonce verification detection",
                            "test_file": "tests/test-cases/nonce-verification.php",
                            "rule_id": "wordpress.nonce.verification",
                            "expected_findings": 1
                        }
                    ]
                },
                "security_patterns": {
                    "description": "Security pattern detection tests",
                    "test_cases": [
                        {
                            "name": "xss_prevention_test",
                            "description": "Test XSS prevention patterns",
                            "test_file": "tests/test-cases/xss-prevention.php",
                            "rule_id": "wordpress.xss.unescaped-output",
                            "expected_findings": 2
                        }
                    ]
                }
            },
            "performance_thresholds": {
                "max_scan_time": 30.0,  # seconds
                "max_memory_usage": 512,  # MB
                "max_rules_per_scan": 50
            },
            "corpus_testing": {
                "enabled": True,
                "max_plugins": 100,
                "sample_size": 10
            }
        }
        
        # Save default config
        self.config_path.parent.mkdir(exist_ok=True)
        with open(self.config_path, 'w') as f:
            json.dump(config, f, indent=2)
        
        return config
    
    def validate_rule_syntax(self, rule_file: str) -> bool:
        """Validate Semgrep rule syntax"""
        try:
            result = subprocess.run(
                ["semgrep", "--validate", rule_file],
                capture_output=True,
                text=True,
                timeout=30
            )
            return result.returncode == 0
        except subprocess.TimeoutExpired:
            logger.error(f"Rule validation timeout for {rule_file}")
            return False
        except Exception as e:
            logger.error(f"Rule validation error for {rule_file}: {e}")
            return False
    
    def run_single_test(self, test_case: Dict) -> TestResult:
        """Run a single test case"""
        start_time = time.time()
        
        try:
            # Run Semgrep on test file
            cmd = [
                "semgrep",
                "--json",
                "--config", f"packs/{test_case['rule_id']}.yaml",
                test_case['test_file']
            ]
            
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                timeout=60
            )
            
            execution_time = time.time() - start_time
            
            if result.returncode != 0 and result.returncode != 1:
                return TestResult(
                    test_name=test_case['name'],
                    rule_id=test_case['rule_id'],
                    status='error',
                    expected_findings=test_case['expected_findings'],
                    actual_findings=0,
                    execution_time=execution_time,
                    error_message=result.stderr
                )
            
            # Parse results
            findings = []
            if result.stdout.strip():
                try:
                    semgrep_results = json.loads(result.stdout)
                    findings = semgrep_results.get('results', [])
                except json.JSONDecodeError:
                    logger.error(f"Failed to parse Semgrep results for {test_case['name']}")
            
            actual_findings = len(findings)
            expected_findings = test_case['expected_findings']
            
            # Determine test status
            if actual_findings == expected_findings:
                status = 'pass'
            else:
                status = 'fail'
            
            return TestResult(
                test_name=test_case['name'],
                rule_id=test_case['rule_id'],
                status=status,
                expected_findings=expected_findings,
                actual_findings=actual_findings,
                execution_time=execution_time,
                findings=findings
            )
            
        except subprocess.TimeoutExpired:
            return TestResult(
                test_name=test_case['name'],
                rule_id=test_case['rule_id'],
                status='error',
                expected_findings=test_case['expected_findings'],
                actual_findings=0,
                execution_time=time.time() - start_time,
                error_message="Test execution timeout"
            )
        except Exception as e:
            return TestResult(
                test_name=test_case['name'],
                rule_id=test_case['rule_id'],
                status='error',
                expected_findings=test_case['expected_findings'],
                actual_findings=0,
                execution_time=time.time() - start_time,
                error_message=str(e)
            )
    
    def run_test_suite(self, suite_name: str) -> List[TestResult]:
        """Run a complete test suite"""
        if suite_name not in self.config['test_suites']:
            logger.error(f"Test suite '{suite_name}' not found")
            return []
        
        suite = self.config['test_suites'][suite_name]
        logger.info(f"Running test suite: {suite_name} - {suite['description']}")
        
        results = []
        for test_case in suite['test_cases']:
            logger.info(f"Running test: {test_case['name']}")
            result = self.run_single_test(test_case)
            results.append(result)
            
            # Log result
            if result.status == 'pass':
                logger.info(f"✓ {test_case['name']} PASSED")
            elif result.status == 'fail':
                logger.warning(f"✗ {test_case['name']} FAILED (Expected: {result.expected_findings}, Got: {result.actual_findings})")
            else:
                logger.error(f"✗ {test_case['name']} ERROR: {result.error_message}")
        
        return results
    
    def run_performance_benchmark(self, rule_file: str, test_corpus: str) -> Dict:
        """Run performance benchmark on a rule"""
        logger.info(f"Running performance benchmark for {rule_file}")
        
        start_time = time.time()
        start_memory = self._get_memory_usage()
        
        try:
            cmd = [
                "semgrep",
                "--json",
                "--config", rule_file,
                test_corpus
            ]
            
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                timeout=300  # 5 minutes timeout
            )
            
            end_time = time.time()
            end_memory = self._get_memory_usage()
            
            execution_time = end_time - start_time
            memory_usage = end_memory - start_memory
            
            # Parse results
            findings = []
            if result.stdout.strip():
                try:
                    semgrep_results = json.loads(result.stdout)
                    findings = semgrep_results.get('results', [])
                except json.JSONDecodeError:
                    pass
            
            return {
                'rule_file': rule_file,
                'execution_time': execution_time,
                'memory_usage_mb': memory_usage,
                'findings_count': len(findings),
                'success': result.returncode in [0, 1],
                'error_message': result.stderr if result.returncode not in [0, 1] else None
            }
            
        except subprocess.TimeoutExpired:
            return {
                'rule_file': rule_file,
                'execution_time': 300,
                'memory_usage_mb': 0,
                'findings_count': 0,
                'success': False,
                'error_message': 'Performance benchmark timeout'
            }
    
    def _get_memory_usage(self) -> float:
        """Get current memory usage in MB"""
        try:
            import psutil
            process = psutil.Process()
            return process.memory_info().rss / 1024 / 1024
        except ImportError:
            return 0.0
    
    def generate_test_report(self, results: List[TestResult], suite_name: str) -> str:
        """Generate a comprehensive test report"""
        total_tests = len(results)
        passed_tests = len([r for r in results if r.status == 'pass'])
        failed_tests = len([r for r in results if r.status == 'fail'])
        error_tests = len([r for r in results if r.status == 'error'])
        
        total_time = sum(r.execution_time for r in results)
        avg_time = total_time / total_tests if total_tests > 0 else 0
        
        report = f"""
# Test Report: {suite_name}
Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}

## Summary
- **Total Tests**: {total_tests}
- **Passed**: {passed_tests} ({passed_tests/total_tests*100:.1f}%)
- **Failed**: {failed_tests} ({failed_tests/total_tests*100:.1f}%)
- **Errors**: {error_tests} ({error_tests/total_tests*100:.1f}%)
- **Total Execution Time**: {total_time:.2f}s
- **Average Execution Time**: {avg_time:.2f}s

## Detailed Results
"""
        
        for result in results:
            status_icon = "✓" if result.status == 'pass' else "✗"
            report += f"""
### {result.test_name}
- **Status**: {status_icon} {result.status.upper()}
- **Rule**: {result.rule_id}
- **Expected Findings**: {result.expected_findings}
- **Actual Findings**: {result.actual_findings}
- **Execution Time**: {result.execution_time:.2f}s
"""
            
            if result.error_message:
                report += f"- **Error**: {result.error_message}\n"
            
            if result.status == 'fail':
                report += f"- **Issue**: Expected {result.expected_findings} findings, got {result.actual_findings}\n"
        
        return report
    
    def save_test_results(self, results: List[TestResult], suite_name: str):
        """Save test results to file"""
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        report_file = self.results_dir / f"test-report-{suite_name}-{timestamp}.md"
        
        report = self.generate_test_report(results, suite_name)
        
        with open(report_file, 'w') as f:
            f.write(report)
        
        logger.info(f"Test report saved to: {report_file}")
        
        # Also save JSON results
        json_file = self.results_dir / f"test-results-{suite_name}-{timestamp}.json"
        json_results = []
        
        for result in results:
            json_results.append({
                'test_name': result.test_name,
                'rule_id': result.rule_id,
                'status': result.status,
                'expected_findings': result.expected_findings,
                'actual_findings': result.actual_findings,
                'execution_time': result.execution_time,
                'error_message': result.error_message
            })
        
        with open(json_file, 'w') as f:
            json.dump(json_results, f, indent=2)
        
        logger.info(f"Test results saved to: {json_file}")

def main():
    """Main entry point for the testing framework"""
    parser = argparse.ArgumentParser(description='WordPress Semgrep Rules Testing Framework')
    parser.add_argument('--suite', help='Test suite to run')
    parser.add_argument('--rule', help='Single rule file to test')
    parser.add_argument('--performance', help='Run performance benchmark on corpus')
    parser.add_argument('--validate', help='Validate rule syntax')
    parser.add_argument('--list-suites', action='store_true', help='List available test suites')
    
    args = parser.parse_args()
    
    tester = WordPressSemgrepTester()
    
    if args.list_suites:
        print("Available test suites:")
        for suite_name, suite_config in tester.config['test_suites'].items():
            print(f"  {suite_name}: {suite_config['description']}")
        return
    
    if args.validate:
        if tester.validate_rule_syntax(args.validate):
            print(f"✓ Rule syntax validation passed: {args.validate}")
        else:
            print(f"✗ Rule syntax validation failed: {args.validate}")
            sys.exit(1)
        return
    
    if args.performance:
        benchmark_result = tester.run_performance_benchmark(args.rule, args.performance)
        print(f"Performance Benchmark Results:")
        print(f"  Execution Time: {benchmark_result['execution_time']:.2f}s")
        print(f"  Memory Usage: {benchmark_result['memory_usage_mb']:.2f}MB")
        print(f"  Findings: {benchmark_result['findings_count']}")
        print(f"  Success: {benchmark_result['success']}")
        return
    
    if args.suite:
        results = tester.run_test_suite(args.suite)
        tester.save_test_results(results, args.suite)
        
        # Print summary
        passed = len([r for r in results if r.status == 'pass'])
        total = len(results)
        print(f"\nTest Suite Summary: {passed}/{total} tests passed")
        
        if passed < total:
            sys.exit(1)
    else:
        # Run all test suites
        all_results = []
        for suite_name in tester.config['test_suites']:
            results = tester.run_test_suite(suite_name)
            all_results.extend(results)
            tester.save_test_results(results, suite_name)
        
        # Print overall summary
        passed = len([r for r in all_results if r.status == 'pass'])
        total = len(all_results)
        print(f"\nOverall Summary: {passed}/{total} tests passed")
        
        if passed < total:
            sys.exit(1)

if __name__ == "__main__":
    main()
