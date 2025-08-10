#!/usr/bin/env python3
"""
WordPress Semgrep Rules - Regression Testing Framework

This script provides regression testing capabilities to track test results
over time and detect when rules break or performance degrades.

Usage:
    python regression-tests.py [options]

Options:
    --baseline <file>   Baseline results file to compare against
    --current <file>    Current test results file
    --output <file>     Output file for regression analysis
    --threshold <float> Performance degradation threshold (default: 0.1)
    --verbose           Verbose output
"""

import os
import sys
import json
import time
import argparse
import datetime
from pathlib import Path
from typing import Dict, List, Any, Optional
from dataclasses import dataclass, asdict
import hashlib

@dataclass
class RegressionResult:
    """Represents a regression test result"""
    test_file: str
    rule_file: str
    baseline_findings: int
    current_findings: int
    baseline_duration: float
    current_duration: float
    findings_regression: bool
    performance_regression: bool
    findings_diff: int
    performance_diff: float
    severity: str  # 'none', 'low', 'medium', 'high'

@dataclass
class RegressionReport:
    """Comprehensive regression report"""
    timestamp: str
    baseline_file: str
    current_file: str
    total_tests: int
    regressions_found: int
    performance_regressions: int
    findings_regressions: int
    regression_results: List[RegressionResult]
    summary: Dict[str, Any]
    recommendations: List[str]

class RegressionTester:
    """Regression testing framework"""
    
    def __init__(self, baseline_file: str, current_file: str, threshold: float = 0.1):
        self.baseline_file = baseline_file
        self.current_file = current_file
        self.threshold = threshold
        self.baseline_data = self._load_test_data(baseline_file)
        self.current_data = self._load_test_data(current_file)
        
    def _load_test_data(self, file_path: str) -> Dict[str, Any]:
        """Load test data from file"""
        if not os.path.exists(file_path):
            raise FileNotFoundError(f"Test data file not found: {file_path}")
        
        with open(file_path, 'r') as f:
            return json.load(f)
    
    def run_regression_analysis(self) -> RegressionReport:
        """Run comprehensive regression analysis"""
        print("Starting Regression Analysis...")
        print("=" * 40)
        
        regression_results = []
        
        # Create mapping of test results
        baseline_map = self._create_test_map(self.baseline_data['test_results'])
        current_map = self._create_test_map(self.current_data['test_results'])
        
        # Analyze each test
        for test_key, baseline_result in baseline_map.items():
            if test_key in current_map:
                current_result = current_map[test_key]
                regression_result = self._analyze_single_test(
                    baseline_result, current_result
                )
                regression_results.append(regression_result)
            else:
                # Test missing in current results
                regression_result = RegressionResult(
                    test_file=baseline_result['test_file'],
                    rule_file=baseline_result['rule_file'],
                    baseline_findings=baseline_result['actual_findings'],
                    current_findings=0,
                    baseline_duration=baseline_result['duration'],
                    current_duration=0.0,
                    findings_regression=True,
                    performance_regression=False,
                    findings_diff=-baseline_result['actual_findings'],
                    performance_diff=-baseline_result['duration'],
                    severity='high'
                )
                regression_results.append(regression_result)
        
        # Check for new tests in current results
        for test_key, current_result in current_map.items():
            if test_key not in baseline_map:
                regression_result = RegressionResult(
                    test_file=current_result['test_file'],
                    rule_file=current_result['rule_file'],
                    baseline_findings=0,
                    current_findings=current_result['actual_findings'],
                    baseline_duration=0.0,
                    current_duration=current_result['duration'],
                    findings_regression=False,
                    performance_regression=False,
                    findings_diff=current_result['actual_findings'],
                    performance_diff=current_result['duration'],
                    severity='none'
                )
                regression_results.append(regression_result)
        
        return self._generate_regression_report(regression_results)
    
    def _create_test_map(self, test_results: List[Dict[str, Any]]) -> Dict[str, Dict[str, Any]]:
        """Create a map of test results by test key"""
        test_map = {}
        for result in test_results:
            test_key = f"{result['test_file']}:{result['rule_file']}"
            test_map[test_key] = result
        return test_map
    
    def _analyze_single_test(self, baseline_result: Dict[str, Any], 
                           current_result: Dict[str, Any]) -> RegressionResult:
        """Analyze a single test for regressions"""
        baseline_findings = baseline_result['actual_findings']
        current_findings = current_result['actual_findings']
        baseline_duration = baseline_result['duration']
        current_duration = current_result['duration']
        
        # Check for findings regression
        findings_diff = current_findings - baseline_findings
        findings_regression = findings_diff < 0  # Fewer findings than baseline
        
        # Check for performance regression
        if baseline_duration > 0:
            performance_diff = (current_duration - baseline_duration) / baseline_duration
            performance_regression = performance_diff > self.threshold
        else:
            performance_diff = current_duration
            performance_regression = current_duration > 1.0  # More than 1 second
        
        # Determine severity
        severity = self._determine_severity(findings_regression, performance_regression, 
                                          findings_diff, performance_diff)
        
        return RegressionResult(
            test_file=baseline_result['test_file'],
            rule_file=baseline_result['rule_file'],
            baseline_findings=baseline_findings,
            current_findings=current_findings,
            baseline_duration=baseline_duration,
            current_duration=current_duration,
            findings_regression=findings_regression,
            performance_regression=performance_regression,
            findings_diff=findings_diff,
            performance_diff=performance_diff,
            severity=severity
        )
    
    def _determine_severity(self, findings_regression: bool, performance_regression: bool,
                          findings_diff: int, performance_diff: float) -> str:
        """Determine regression severity"""
        if findings_regression and abs(findings_diff) > 5:
            return 'high'
        elif findings_regression and abs(findings_diff) > 2:
            return 'medium'
        elif performance_regression and performance_diff > 0.5:
            return 'high'
        elif performance_regression and performance_diff > 0.2:
            return 'medium'
        elif findings_regression or performance_regression:
            return 'low'
        else:
            return 'none'
    
    def _generate_regression_report(self, regression_results: List[RegressionResult]) -> RegressionReport:
        """Generate comprehensive regression report"""
        total_tests = len(regression_results)
        regressions_found = len([r for r in regression_results if r.severity != 'none'])
        performance_regressions = len([r for r in regression_results if r.performance_regression])
        findings_regressions = len([r for r in regression_results if r.findings_regression])
        
        # Generate summary
        summary = {
            'total_tests': total_tests,
            'regressions_found': regressions_found,
            'performance_regressions': performance_regressions,
            'findings_regressions': findings_regressions,
            'high_severity': len([r for r in regression_results if r.severity == 'high']),
            'medium_severity': len([r for r in regression_results if r.severity == 'medium']),
            'low_severity': len([r for r in regression_results if r.severity == 'low']),
            'regression_rate': (regressions_found / total_tests * 100) if total_tests > 0 else 0
        }
        
        # Generate recommendations
        recommendations = self._generate_recommendations(regression_results, summary)
        
        return RegressionReport(
            timestamp=datetime.datetime.now().isoformat(),
            baseline_file=self.baseline_file,
            current_file=self.current_file,
            total_tests=total_tests,
            regressions_found=regressions_found,
            performance_regressions=performance_regressions,
            findings_regressions=findings_regressions,
            regression_results=regression_results,
            summary=summary,
            recommendations=recommendations
        )
    
    def _generate_recommendations(self, regression_results: List[RegressionResult], 
                                summary: Dict[str, Any]) -> List[str]:
        """Generate recommendations based on regression analysis"""
        recommendations = []
        
        # High severity regressions
        high_severity = [r for r in regression_results if r.severity == 'high']
        if high_severity:
            recommendations.append(f"Investigate {len(high_severity)} high-severity regressions immediately")
        
        # Findings regressions
        if summary['findings_regressions'] > 0:
            recommendations.append(f"Review {summary['findings_regressions']} findings regressions - rules may be broken")
        
        # Performance regressions
        if summary['performance_regressions'] > 0:
            recommendations.append(f"Optimize {summary['performance_regressions']} performance regressions")
        
        # Overall regression rate
        if summary['regression_rate'] > 10:
            recommendations.append("High regression rate detected - consider rolling back recent changes")
        elif summary['regression_rate'] > 5:
            recommendations.append("Moderate regression rate - review recent rule changes")
        
        # Missing tests
        missing_tests = [r for r in regression_results if r.baseline_findings > 0 and r.current_findings == 0]
        if missing_tests:
            recommendations.append(f"Investigate {len(missing_tests)} tests with missing findings")
        
        return recommendations
    
    def save_report(self, report: RegressionReport, output_file: str):
        """Save regression report to file"""
        report_dict = asdict(report)
        
        with open(output_file, 'w') as f:
            json.dump(report_dict, f, indent=2)
        
        print(f"\nRegression report saved to: {output_file}")
    
    def generate_html_report(self, report: RegressionReport, output_file: str):
        """Generate HTML regression report"""
        html_content = self._generate_html_content(report)
        
        with open(output_file, 'w') as f:
            f.write(html_content)
        
        print(f"HTML regression report generated: {output_file}")
    
    def _generate_html_content(self, report: RegressionReport) -> str:
        """Generate HTML content for the regression report"""
        return f"""
<!DOCTYPE html>
<html>
<head>
    <title>WordPress Semgrep Rules - Regression Report</title>
    <style>
        body {{ font-family: Arial, sans-serif; margin: 20px; }}
        .header {{ background-color: #f0f0f0; padding: 20px; border-radius: 5px; }}
        .summary {{ margin: 20px 0; }}
        .regression-result {{ margin: 10px 0; padding: 10px; border-radius: 3px; }}
        .none {{ background-color: #d4edda; border: 1px solid #c3e6cb; }}
        .low {{ background-color: #fff3cd; border: 1px solid #ffeaa7; }}
        .medium {{ background-color: #f8d7da; border: 1px solid #f5c6cb; }}
        .high {{ background-color: #f8d7da; border: 1px solid #f5c6cb; color: white; }}
        .recommendations {{ background-color: #e2e3e5; padding: 15px; border-radius: 5px; }}
        .severity-high {{ background-color: #dc3545; }}
        .severity-medium {{ background-color: #fd7e14; }}
        .severity-low {{ background-color: #ffc107; }}
    </style>
</head>
<body>
    <div class="header">
        <h1>WordPress Semgrep Rules - Regression Report</h1>
        <p>Generated: {report.timestamp}</p>
        <p>Baseline: {os.path.basename(report.baseline_file)}</p>
        <p>Current: {os.path.basename(report.current_file)}</p>
    </div>
    
    <div class="summary">
        <h2>Regression Summary</h2>
        <p>Total Tests: {report.total_tests}</p>
        <p>Regressions Found: {report.regressions_found}</p>
        <p>Performance Regressions: {report.performance_regressions}</p>
        <p>Findings Regressions: {report.findings_regressions}</p>
        <p>Regression Rate: {report.summary['regression_rate']:.1f}%</p>
        <p>High Severity: {report.summary['high_severity']}</p>
        <p>Medium Severity: {report.summary['medium_severity']}</p>
        <p>Low Severity: {report.summary['low_severity']}</p>
    </div>
    
    <div class="recommendations">
        <h2>Recommendations</h2>
        <ul>
            {''.join(f'<li>{rec}</li>' for rec in report.recommendations)}
        </ul>
    </div>
    
    <h2>Regression Results</h2>
    {''.join(self._generate_regression_result_html(result) for result in report.regression_results)}
</body>
</html>
"""
    
    def _generate_regression_result_html(self, result: RegressionResult) -> str:
        """Generate HTML for a single regression result"""
        severity_class = f"severity-{result.severity}" if result.severity != 'none' else ''
        
        return f"""
    <div class="regression-result {result.severity} {severity_class}">
        <h3>{os.path.basename(result.test_file)} - {os.path.basename(result.rule_file)}</h3>
        <p>Severity: {result.severity.upper()}</p>
        <p>Findings: {result.baseline_findings} → {result.current_findings} (diff: {result.findings_diff:+d})</p>
        <p>Duration: {result.baseline_duration:.2f}s → {result.current_duration:.2f}s (diff: {result.performance_diff:+.1%})</p>
        <p>Findings Regression: {'Yes' if result.findings_regression else 'No'}</p>
        <p>Performance Regression: {'Yes' if result.performance_regression else 'No'}</p>
    </div>
"""

def main():
    """Main entry point"""
    parser = argparse.ArgumentParser(description='WordPress Semgrep Rules Regression Testing')
    parser.add_argument('--baseline', required=True, help='Baseline results file')
    parser.add_argument('--current', required=True, help='Current test results file')
    parser.add_argument('--output', help='Output file for regression analysis')
    parser.add_argument('--threshold', type=float, default=0.1, help='Performance degradation threshold')
    parser.add_argument('--verbose', action='store_true', help='Verbose output')
    parser.add_argument('--html', action='store_true', help='Generate HTML report')
    
    args = parser.parse_args()
    
    # Initialize regression tester
    tester = RegressionTester(args.baseline, args.current, args.threshold)
    
    # Run regression analysis
    report = tester.run_regression_analysis()
    
    # Output results
    output_file = args.output or 'test-results/regression-report.json'
    
    tester.save_report(report, output_file)
    
    if args.html:
        html_file = output_file.replace('.json', '.html')
        tester.generate_html_report(report, html_file)
    
    # Print summary
    print(f"\nRegression Analysis Summary:")
    print(f"Total Tests: {report.total_tests}")
    print(f"Regressions Found: {report.regressions_found}")
    print(f"Performance Regressions: {report.performance_regressions}")
    print(f"Findings Regressions: {report.findings_regressions}")
    print(f"Regression Rate: {report.summary['regression_rate']:.1f}%")
    print(f"High Severity: {report.summary['high_severity']}")
    print(f"Medium Severity: {report.summary['medium_severity']}")
    print(f"Low Severity: {report.summary['low_severity']}")
    
    # Exit with error code if there are high-severity regressions
    if report.summary['high_severity'] > 0:
        sys.exit(1)

if __name__ == '__main__':
    main()
