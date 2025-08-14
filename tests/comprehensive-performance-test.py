#!/usr/bin/env python3
"""
WordPress Semgrep Rules - Comprehensive Performance Testing Framework

This script conducts comprehensive performance testing and optimization for WordPress Semgrep rules,
including scan time analysis, memory usage monitoring, CPU utilization, and optimization recommendations.

Usage:
    python comprehensive-performance-test.py [options]

Options:
    --config <file>     Use custom test configuration file
    --rules <path>      Path to rules directory
    --tests <path>      Path to test files directory
    --output <file>     Output file for test results
    --iterations <int>  Number of iterations per test (default: 10)
    --warmup <int>      Number of warmup runs (default: 3)
    --verbose           Verbose output
    --json              Output results in JSON format
    --html              Generate HTML report
    --optimize          Run optimization analysis
    --baseline          Establish performance baseline
    --compare           Compare against baseline
"""

import os
import sys
import json
import time
import subprocess
import argparse
import datetime
import statistics
try:
    import psutil  # type: ignore
except Exception:
    psutil = None
import threading
import queue
import sqlite3
from pathlib import Path
from typing import Dict, List, Any, Optional, Tuple
from dataclasses import dataclass, asdict
import matplotlib.pyplot as plt
import numpy as np

@dataclass
class PerformanceMetrics:
    """Performance metrics for a single test run"""
    scan_time: float
    memory_peak: float
    memory_final: float
    cpu_percent: float
    cpu_time: float
    io_read_bytes: int
    io_write_bytes: int
    findings_count: int
    files_scanned: int
    rules_executed: int
    cache_hits: int
    cache_misses: int

@dataclass
class TestResult:
    """Result of a single performance test"""
    config_name: str
    test_path: str
    iteration: int
    metrics: PerformanceMetrics
    success: bool
    error_message: Optional[str] = None
    timestamp: str = None

@dataclass
class TestSummary:
    """Summary statistics for a test configuration"""
    config_name: str
    test_path: str
    iterations: int
    mean_scan_time: float
    median_scan_time: float
    std_scan_time: float
    min_scan_time: float
    max_scan_time: float
    mean_memory_peak: float
    mean_memory_final: float
    mean_cpu_percent: float
    mean_findings_count: int
    success_rate: float
    total_duration: float
    throughput_files_per_second: float
    throughput_rules_per_second: float

@dataclass
class PerformanceReport:
    """Comprehensive performance report"""
    timestamp: str
    duration: float
    total_tests: int
    successful_tests: int
    failed_tests: int
    test_summaries: List[TestSummary]
    performance_rankings: Dict[str, Any]
    optimization_recommendations: List[str]
    baseline_comparison: Dict[str, Any]
    performance_trends: Dict[str, Any]

class ComprehensivePerformanceTester:
    """Comprehensive performance testing framework"""
    
    def __init__(self, config_path: Optional[str] = None):
        self.config = self._load_config(config_path)
        self.results: List[TestResult] = []
        self.start_time = time.time()
        self.monitoring_active = False
        self.monitoring_thread = None
        self.monitoring_queue = queue.Queue()
        self.monitoring_data = []
        
        # Regression thresholds
        self.regression_thresholds = {
            'max_scan_time_regression_pct': 15.0,
            'max_memory_regression_pct': 10.0
        }
    
    def _get_semgrep_memory_mb(self) -> float:
        """Return total RSS memory in MB for semgrep child processes.

        If psutil is unavailable or no semgrep child is found, returns 0.0.
        """
        if psutil is None:
            return 0.0
        try:
            parent = psutil.Process()
            total_rss = 0
            for child in parent.children(recursive=True):
                try:
                    name = ''
                    cmdline = ''
                    try:
                        name = (child.name() or '').lower()
                    except Exception:
                        pass
                    try:
                        cmdline = ' '.join(child.cmdline()).lower()
                    except Exception:
                        pass
                    if 'semgrep' in name or 'semgrep' in cmdline:
                        mi = child.memory_info()
                        total_rss += getattr(mi, 'rss', 0)
                except (psutil.NoSuchProcess, psutil.AccessDenied):
                    continue
            return float(total_rss) / 1024.0 / 1024.0
        except Exception:
            return 0.0

    def _get_semgrep_cpu(self) -> Tuple[float, float]:
        """Return (cpu_percent_sum, cpu_time_sum) for semgrep child processes.

        If no semgrep children are found, falls back to current process values.
        """
        if psutil is None:
            return (0.0, 0.0)
        try:
            parent = psutil.Process()
            total_percent = 0.0
            total_time = 0.0
            found = False
            for child in parent.children(recursive=True):
                try:
                    name = ''
                    cmdline = ''
                    try:
                        name = (child.name() or '').lower()
                    except Exception:
                        pass
                    try:
                        cmdline = ' '.join(child.cmdline()).lower()
                    except Exception:
                        pass
                    if 'semgrep' in name or 'semgrep' in cmdline:
                        found = True
                        total_percent += float(child.cpu_percent(interval=0.0))
                        c = child.cpu_times()
                        total_time += float(getattr(c, 'user', 0.0) + getattr(c, 'system', 0.0))
                except (psutil.NoSuchProcess, psutil.AccessDenied):
                    continue
            if not found:
                try:
                    p = parent
                    percent = float(p.cpu_percent(interval=0.0))
                    c = p.cpu_times()
                    ctime = float(getattr(c, 'user', 0.0) + getattr(c, 'system', 0.0))
                    return (percent, ctime)
                except Exception:
                    return (0.0, 0.0)
            return (total_percent, total_time)
        except Exception:
            return (0.0, 0.0)
        
    def _load_config(self, config_path: Optional[str] = None) -> Dict[str, Any]:
        """Load test configuration and normalize critical paths"""
        base_dir = Path(__file__).parent.resolve()
        project_root = base_dir.parent

        default_config = {
            'semgrep_binary': 'semgrep',
            'rules_path': str(project_root / 'packs'),
            'tests_path': str(base_dir),
            'configs_path': str(project_root / 'configs'),
            'output_path': str(base_dir / 'performance-results'),
            'iterations': 10,
            'warmup_runs': 3,
            'timeout': 300,
            'performance_thresholds': {
                'max_scan_time': 30.0,
                'max_memory_usage': 500.0,
                'max_cpu_percent': 80.0,
                'min_success_rate': 0.95,
                'min_throughput_files_per_second': 0.1
            },
            'test_scenarios': {
                'small_project': {
                    'name': 'Small WordPress Plugin',
                    'description': 'Testing with small plugin codebase',
                    'test_files': [
                        'safe-examples/nonce-safe.php',
                        'safe-examples/capability-safe.php',
                        'safe-examples/sanitization-safe.php'
                    ],
                    'configs': ['basic.yaml', 'strict.yaml']
                },
                'medium_project': {
                    'name': 'Medium WordPress Plugin',
                    'description': 'Testing with medium-sized plugin codebase',
                    'test_files': [
                        'vulnerable-examples/*.php',
                        'safe-examples/*.php'
                    ],
                    'configs': ['basic.yaml', 'strict.yaml', 'plugin-development.yaml']
                },
                'large_project': {
                    'name': 'Large WordPress Project',
                    'description': 'Testing with large project codebase',
                    'test_files': [
                        'vulnerable-examples/*.php',
                        'safe-examples/*.php',
                        'tests/**/*.php'
                    ],
                    'configs': ['basic.yaml', 'strict.yaml', 'plugin-development.yaml', 'optimized-30s.yaml']
                }
            }
        }
        
        if config_path and os.path.exists(config_path):
            with open(config_path, 'r', encoding='utf-8') as f:
                user_config = json.load(f)
                default_config.update(user_config)
        
        # Normalize paths to absolute strings
        for key in ['rules_path', 'tests_path', 'configs_path', 'output_path']:
            if key in default_config:
                default_config[key] = str(Path(default_config[key]).resolve())

        return default_config
    
    def run_comprehensive_tests(self) -> PerformanceReport:
        """Run comprehensive performance tests"""
        print("Starting comprehensive performance testing...")
        
        # Create output directory
        os.makedirs(self.config['output_path'], exist_ok=True)
        
        # Run warmup
        self._run_warmup()
        
        # Run all test scenarios
        for scenario_name, scenario_config in self.config['test_scenarios'].items():
            print(f"\nTesting scenario: {scenario_config['name']}")
            
            for config_name in scenario_config['configs']:
                config_path = os.path.join(self.config['configs_path'], config_name)
                if os.path.exists(config_path):
                    self._run_scenario_tests(scenario_name, config_path, scenario_config['test_files'])
        
        # Generate comprehensive report
        report = self._generate_comprehensive_report()
        
        # Save results
        self._save_results(report)
        
        return report
    
    def _run_warmup(self):
        """Run warmup tests to stabilize performance"""
        print("Running warmup tests...")
        
        for i in range(self.config['warmup_runs']):
            print(f"Warmup run {i + 1}/{self.config['warmup_runs']}")
            
            # Run a simple test to warm up the system
            test_files = ['safe-examples/nonce-safe.php']
            config_path = os.path.join(self.config['configs_path'], 'basic.yaml')
            
            if os.path.exists(config_path):
                self._run_single_test('warmup', config_path, test_files, -1)
    
    def _run_scenario_tests(self, scenario_name: str, config_path: str, test_files: List[str]):
        """Run tests for a specific scenario"""
        config_name = os.path.basename(config_path)
        
        for iteration in range(self.config['iterations']):
            print(f"  Running {config_name} - iteration {iteration + 1}/{self.config['iterations']}")
            
            result = self._run_single_test(scenario_name, config_path, test_files, iteration)
            self.results.append(result)
    
    def _run_single_test(self, scenario_name: str, config_path: str, test_files: List[str], iteration: int) -> TestResult:
        """Run a single performance test"""
        start_time = time.time()
        
        # Start monitoring
        self._start_monitoring()
        
        try:
            # Prepare test files
            test_paths = []
            for pattern in test_files:
                if '*' in pattern:
                    # Handle glob patterns
                    test_paths.extend(Path(self.config['tests_path']).glob(pattern))
                else:
                    test_paths.append(Path(self.config['tests_path']) / pattern)
            
            # Run semgrep scan
            cmd = [
                self.config['semgrep_binary'], 'scan',
                '--config', config_path,
                '--json',
                '--timeout', str(self.config['timeout']),
                '--max-memory', '1000'
            ]
            
            # Add test files
            for test_path in test_paths:
                if test_path.exists():
                    cmd.append(str(test_path))
            
            # Run the command
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                cwd=self.config['tests_path'],
                encoding='utf-8',
                errors='replace'
            )
            
            end_time = time.time()
            scan_time = end_time - start_time
            
            # Stop monitoring and get metrics
            self._stop_monitoring()
            monitoring_data = self._get_monitoring_data()
            
            if result.returncode == 0:
                # Parse results
                scan_results = json.loads(result.stdout)
                
                metrics = PerformanceMetrics(
                    scan_time=scan_time,
                    memory_peak=monitoring_data.get('memory_peak', 0),
                    memory_final=monitoring_data.get('memory_final', 0),
                    cpu_percent=monitoring_data.get('cpu_percent', 0),
                    cpu_time=monitoring_data.get('cpu_time', 0),
                    io_read_bytes=monitoring_data.get('io_read_bytes', 0),
                    io_write_bytes=monitoring_data.get('io_write_bytes', 0),
                    findings_count=len(scan_results.get('results', [])),
                    files_scanned=len(scan_results.get('paths', {}).get('scanned', [])),
                    rules_executed=len(scan_results.get('results', [])),
                    cache_hits=0,  # Will be updated if cache data available
                    cache_misses=0
                )
                
                return TestResult(
                    config_name=os.path.basename(config_path),
                    test_path=scenario_name,
                    iteration=iteration,
                    metrics=metrics,
                    success=True,
                    timestamp=datetime.datetime.now().isoformat()
                )
            else:
                return TestResult(
                    config_name=os.path.basename(config_path),
                    test_path=scenario_name,
                    iteration=iteration,
                    metrics=PerformanceMetrics(
                        scan_time=scan_time,
                        memory_peak=0,
                        memory_final=0,
                        cpu_percent=0,
                        cpu_time=0,
                        io_read_bytes=0,
                        io_write_bytes=0,
                        findings_count=0,
                        files_scanned=0,
                        rules_executed=0,
                        cache_hits=0,
                        cache_misses=0
                    ),
                    success=False,
                    error_message=result.stderr,
                    timestamp=datetime.datetime.now().isoformat()
                )
                
        except Exception as e:
            self._stop_monitoring()
            return TestResult(
                config_name=os.path.basename(config_path),
                test_path=scenario_name,
                iteration=iteration,
                metrics=PerformanceMetrics(
                    scan_time=time.time() - start_time,
                    memory_peak=0,
                    memory_final=0,
                    cpu_percent=0,
                    cpu_time=0,
                    io_read_bytes=0,
                    io_write_bytes=0,
                    findings_count=0,
                    files_scanned=0,
                    rules_executed=0,
                    cache_hits=0,
                    cache_misses=0
                ),
                success=False,
                error_message=str(e),
                timestamp=datetime.datetime.now().isoformat()
            )
    
    def _start_monitoring(self):
        """Start performance monitoring"""
        self.monitoring_active = True
        self.monitoring_data = []
        self.monitoring_thread = threading.Thread(target=self._monitor_performance)
        self.monitoring_thread.start()
    
    def _stop_monitoring(self):
        """Stop performance monitoring"""
        self.monitoring_active = False
        if self.monitoring_thread:
            self.monitoring_thread.join()
    
    def _monitor_performance(self):
        """Monitor system performance during test execution"""
        if psutil is None:
            return
        process = psutil.Process()
        
        while self.monitoring_active:
            try:
                # Get process metrics
                # Memory from semgrep child processes; fall back to current process RSS if zero
                semgrep_mb = self._get_semgrep_memory_mb()
                memory_rss_mb = semgrep_mb if semgrep_mb > 0 else (process.memory_info().rss / 1024 / 1024)
                cpu_percent, cpu_time_abs = self._get_semgrep_cpu()
                cpu_times = process.cpu_times()
                io_counters = process.io_counters()
                
                monitoring_point = {
                    'timestamp': time.time(),
                    'memory_rss': memory_rss_mb,  # MB
                    'memory_vms': getattr(process.memory_info(), 'vms', 0) / 1024 / 1024,  # MB
                    'cpu_percent': cpu_percent,
                    'cpu_time': cpu_time_abs if cpu_percent > 0 else (cpu_times.user + cpu_times.system),
                    'io_read_bytes': io_counters.read_bytes,
                    'io_write_bytes': io_counters.write_bytes
                }
                
                self.monitoring_data.append(monitoring_point)
                time.sleep(0.1)  # Sample every 100ms
                
            except (psutil.NoSuchProcess, psutil.AccessDenied):
                break
    
    def _get_monitoring_data(self) -> Dict[str, float]:
        """Get aggregated monitoring data"""
        if not self.monitoring_data:
            return {}
        
        memory_peaks = [point['memory_rss'] for point in self.monitoring_data]
        cpu_percents = [point['cpu_percent'] for point in self.monitoring_data]
        
        return {
            'memory_peak': max(memory_peaks) if memory_peaks else 0,
            'memory_final': memory_peaks[-1] if memory_peaks else 0,
            'cpu_percent': statistics.mean(cpu_percents) if cpu_percents else 0,
            'cpu_time': self.monitoring_data[-1]['cpu_time'] - self.monitoring_data[0]['cpu_time'] if len(self.monitoring_data) > 1 else 0,
            'io_read_bytes': self.monitoring_data[-1]['io_read_bytes'] - self.monitoring_data[0]['io_read_bytes'] if len(self.monitoring_data) > 1 else 0,
            'io_write_bytes': self.monitoring_data[-1]['io_write_bytes'] - self.monitoring_data[0]['io_write_bytes'] if len(self.monitoring_data) > 1 else 0
        }
    
    def _generate_comprehensive_report(self) -> PerformanceReport:
        """Generate comprehensive performance report"""
        print("Generating comprehensive performance report...")
        
        # Group results by configuration and test path
        grouped_results = {}
        for result in self.results:
            key = (result.config_name, result.test_path)
            if key not in grouped_results:
                grouped_results[key] = []
            grouped_results[key].append(result)
        
        # Generate summaries
        summaries = []
        for (config_name, test_path), results in grouped_results.items():
            summary = self._generate_test_summary(config_name, test_path, results)
            summaries.append(summary)
        
        # Generate rankings and recommendations
        rankings = self._generate_performance_rankings(summaries)
        recommendations = self._generate_optimization_recommendations(summaries)
        baseline_comparison = self._compare_with_baseline(summaries)
        trends = self._analyze_performance_trends(summaries)
        
        return PerformanceReport(
            timestamp=datetime.datetime.now().isoformat(),
            duration=time.time() - self.start_time,
            total_tests=len(self.results),
            successful_tests=sum(1 for r in self.results if r.success),
            failed_tests=sum(1 for r in self.results if not r.success),
            test_summaries=summaries,
            performance_rankings=rankings,
            optimization_recommendations=recommendations,
            baseline_comparison=baseline_comparison,
            performance_trends=trends
        )
    
    def _generate_test_summary(self, config_name: str, test_path: str, results: List[TestResult]) -> TestSummary:
        """Generate summary statistics for a test configuration"""
        successful_results = [r for r in results if r.success]
        
        if not successful_results:
            return TestSummary(
                config_name=config_name,
                test_path=test_path,
                iterations=len(results),
                mean_scan_time=0,
                median_scan_time=0,
                std_scan_time=0,
                min_scan_time=0,
                max_scan_time=0,
                mean_memory_peak=0,
                mean_memory_final=0,
                mean_cpu_percent=0,
                mean_findings_count=0,
                success_rate=0,
                total_duration=0,
                throughput_files_per_second=0,
                throughput_rules_per_second=0
            )
        
        scan_times = [r.metrics.scan_time for r in successful_results]
        memory_peaks = [r.metrics.memory_peak for r in successful_results]
        memory_finals = [r.metrics.memory_final for r in successful_results]
        cpu_percents = [r.metrics.cpu_percent for r in successful_results]
        findings_counts = [r.metrics.findings_count for r in successful_results]
        files_scanned = [r.metrics.files_scanned for r in successful_results]
        rules_executed = [r.metrics.rules_executed for r in successful_results]
        
        mean_scan_time = statistics.mean(scan_times)
        mean_files_per_second = statistics.mean([files / time for files, time in zip(files_scanned, scan_times) if time > 0])
        mean_rules_per_second = statistics.mean([rules / time for rules, time in zip(rules_executed, scan_times) if time > 0])
        
        return TestSummary(
            config_name=config_name,
            test_path=test_path,
            iterations=len(results),
            mean_scan_time=mean_scan_time,
            median_scan_time=statistics.median(scan_times),
            std_scan_time=statistics.stdev(scan_times) if len(scan_times) > 1 else 0,
            min_scan_time=min(scan_times),
            max_scan_time=max(scan_times),
            mean_memory_peak=statistics.mean(memory_peaks),
            mean_memory_final=statistics.mean(memory_finals),
            mean_cpu_percent=statistics.mean(cpu_percents),
            mean_findings_count=statistics.mean(findings_counts),
            success_rate=len(successful_results) / len(results),
            total_duration=sum(scan_times),
            throughput_files_per_second=mean_files_per_second,
            throughput_rules_per_second=mean_rules_per_second
        )
    
    def _generate_performance_rankings(self, summaries: List[TestSummary]) -> Dict[str, Any]:
        """Generate performance rankings"""
        # Rank by scan time (lower is better)
        scan_time_rankings = sorted(summaries, key=lambda s: s.mean_scan_time)
        
        # Rank by throughput (higher is better)
        throughput_rankings = sorted(summaries, key=lambda s: s.throughput_files_per_second, reverse=True)
        
        # Rank by memory efficiency (lower is better)
        memory_rankings = sorted(summaries, key=lambda s: s.mean_memory_peak)
        
        return {
            'fastest_configs': [s.config_name for s in scan_time_rankings[:5]],
            'highest_throughput': [s.config_name for s in throughput_rankings[:5]],
            'most_memory_efficient': [s.config_name for s in memory_rankings[:5]],
            'scan_time_rankings': [(s.config_name, s.mean_scan_time) for s in scan_time_rankings],
            'throughput_rankings': [(s.config_name, s.throughput_files_per_second) for s in throughput_rankings]
        }
    
    def _generate_optimization_recommendations(self, summaries: List[TestSummary]) -> List[str]:
        """Generate optimization recommendations"""
        recommendations = []
        thresholds = self.config['performance_thresholds']
        
        for summary in summaries:
            # Check scan time threshold
            if summary.mean_scan_time > thresholds['max_scan_time']:
                recommendations.append(
                    f"Configuration '{summary.config_name}' exceeds scan time threshold "
                    f"({summary.mean_scan_time:.2f}s > {thresholds['max_scan_time']}s). "
                    "Consider rule optimization or caching."
                )
            
            # Check memory threshold
            if summary.mean_memory_peak > thresholds['max_memory_usage']:
                recommendations.append(
                    f"Configuration '{summary.config_name}' exceeds memory threshold "
                    f"({summary.mean_memory_peak:.2f}MB > {thresholds['max_memory_usage']}MB). "
                    "Consider memory optimization."
                )
            
            # Check throughput threshold
            if summary.throughput_files_per_second < thresholds['min_throughput_files_per_second']:
                recommendations.append(
                    f"Configuration '{summary.config_name}' has low throughput "
                    f"({summary.throughput_files_per_second:.3f} files/s < {thresholds['min_throughput_files_per_second']}). "
                    "Consider performance optimization."
                )
        
        # General recommendations
        if not recommendations:
            recommendations.append("All configurations meet performance thresholds. Consider further optimization for edge cases.")
        
        return recommendations
    
    def _compare_with_baseline(self, summaries: List[TestSummary]) -> Dict[str, Any]:
        """Compare current results with baseline"""
        baseline_file = 'performance-baseline.json'
        
        if not os.path.exists(baseline_file):
            return {'status': 'no_baseline', 'message': 'No baseline file found'}
        
        try:
            with open(baseline_file, 'r') as f:
                baseline_data = json.load(f)
            
            # Extract baseline metrics
            baseline_time = baseline_data.get('time', {})
            baseline_scan_time = baseline_time.get('profiling_times', {}).get('total_time', 0)
            baseline_memory = baseline_time.get('max_memory_bytes', 0) / 1024 / 1024  # Convert to MB
            
            # Compare with current results
            current_basic = next((s for s in summaries if s.config_name == 'basic.yaml'), None)
            
            if current_basic:
                time_improvement = ((baseline_scan_time - current_basic.mean_scan_time) / baseline_scan_time * 100) if baseline_scan_time > 0 else 0
                memory_improvement = ((baseline_memory - current_basic.mean_memory_peak) / baseline_memory * 100) if baseline_memory > 0 else 0
                
                return {
                    'status': 'comparison_available',
                    'baseline_scan_time': baseline_scan_time,
                    'current_scan_time': current_basic.mean_scan_time,
                    'time_improvement_percent': time_improvement,
                    'baseline_memory': baseline_memory,
                    'current_memory': current_basic.mean_memory_peak,
                    'memory_improvement_percent': memory_improvement
                }
            
            return {'status': 'no_matching_config', 'message': 'No matching configuration found for comparison'}
            
        except Exception as e:
            return {'status': 'error', 'message': f'Error reading baseline: {str(e)}'}
    
    def _analyze_performance_trends(self, summaries: List[TestSummary]) -> Dict[str, Any]:
        """Analyze performance trends across configurations"""
        trends = {}
        
        # Analyze scan time trends
        scan_times = [(s.config_name, s.mean_scan_time) for s in summaries]
        trends['scan_time_trends'] = scan_times
        
        # Analyze memory usage trends
        memory_usage = [(s.config_name, s.mean_memory_peak) for s in summaries]
        trends['memory_usage_trends'] = memory_usage
        
        # Analyze throughput trends
        throughput = [(s.config_name, s.throughput_files_per_second) for s in summaries]
        trends['throughput_trends'] = throughput
        
        return trends
    
    def _save_results(self, report: PerformanceReport):
        """Save test results"""
        # Save JSON report
        os.makedirs(self.config['output_path'], exist_ok=True)
        output_file = os.path.join(self.config['output_path'], 'comprehensive-performance-report.json')
        with open(output_file, 'w') as f:
            json.dump(asdict(report), f, indent=2, default=str)
        
        # Save detailed results
        results_file = os.path.join(self.config['output_path'], 'detailed-test-results.json')
        with open(results_file, 'w') as f:
            json.dump([asdict(r) for r in self.results], f, indent=2, default=str)
        
        print(f"Results saved to {self.config['output_path']}")

    def export_csv(self, report: PerformanceReport):
        """Export test summaries to CSV in the output directory"""
        import csv
        csv_path = os.path.join(self.config['output_path'], 'comprehensive-performance-report.csv')
        rows = []
        for s in report.test_summaries:
            rows.append({
                'config_name': s.config_name,
                'test_path': s.test_path,
                'iterations': s.iterations,
                'mean_scan_time': f"{s.mean_scan_time:.6f}",
                'std_scan_time': f"{s.std_scan_time:.6f}",
                'min_scan_time': f"{s.min_scan_time:.6f}",
                'max_scan_time': f"{s.max_scan_time:.6f}",
                'mean_memory_peak': f"{s.mean_memory_peak:.3f}",
                'mean_cpu_percent': f"{s.mean_cpu_percent:.2f}",
                'mean_findings_count': f"{s.mean_findings_count:.2f}",
                'success_rate': f"{s.success_rate:.4f}",
                'throughput_files_per_second': f"{s.throughput_files_per_second:.6f}",
                'throughput_rules_per_second': f"{s.throughput_rules_per_second:.6f}"
            })
        fieldnames = list(rows[0].keys()) if rows else [
            'config_name','test_path','iterations','mean_scan_time','std_scan_time','min_scan_time','max_scan_time','mean_memory_peak','mean_cpu_percent','mean_findings_count','success_rate','throughput_files_per_second','throughput_rules_per_second']
        with open(csv_path, 'w', newline='') as f:
            writer = csv.DictWriter(f, fieldnames=fieldnames)
            writer.writeheader()
            for r in rows:
                writer.writerow(r)
        print(f"CSV saved to: {csv_path}")

    def export_markdown(self, report: PerformanceReport):
        """Export a concise Markdown report to the output directory"""
        md_path = os.path.join(self.config['output_path'], 'comprehensive-performance-report.md')
        lines = []
        lines.append("# Comprehensive Performance Report\n\n")
        lines.append(f"- Timestamp: {report.timestamp}\n")
        lines.append(f"- Duration: {report.duration:.2f} seconds\n")
        lines.append(f"- Total Tests: {report.total_tests}\n")
        lines.append(f"- Success Rate: {(report.successful_tests / report.total_tests * 100 if report.total_tests else 0):.1f}%\n\n")
        lines.append("## Top Fastest Configurations\n")
        for cfg in report.performance_rankings.get('fastest_configs', [])[:5]:
            lines.append(f"- {cfg}\n")
        lines.append("\n## Summary Table\n")
        lines.append("| Config | Test Path | Iter | Mean Time (s) | Peak Mem (MB) | CPU (%) | Findings | Files/s | Rules/s |\n")
        lines.append("|---|---|---:|---:|---:|---:|---:|---:|---:|\n")
        for s in report.test_summaries:
            lines.append(f"| {s.config_name} | {s.test_path} | {s.iterations} | {s.mean_scan_time:.2f} | {s.mean_memory_peak:.1f} | {s.mean_cpu_percent:.1f} | {s.mean_findings_count:.1f} | {s.throughput_files_per_second:.2f} | {s.throughput_rules_per_second:.2f} |\n")
        with open(md_path, 'w', encoding='utf-8') as f:
            f.writelines(lines)
        print(f"Markdown saved to: {md_path}")

    def save_baseline(self, report: PerformanceReport, baseline_file: Optional[str] = None):
        """Save performance baseline for regression checks."""
        baseline_path = baseline_file or os.path.join(self.config['output_path'], 'comprehensive-performance-baseline.json')
        with open(baseline_path, 'w') as f:
            json.dump(asdict(report), f, indent=2, default=str)
        print(f"Baseline saved to: {baseline_path}")

    def compare_with_baseline(self, report: PerformanceReport, baseline_file: Optional[str] = None) -> Dict[str, Any]:
        """Compare current test summaries with baseline and detect regressions."""
        baseline_path = baseline_file or os.path.join(self.config['output_path'], 'comprehensive-performance-baseline.json')
        if not os.path.exists(baseline_path):
            return {'status': 'no_baseline', 'message': 'Baseline not found', 'baseline_path': baseline_path}
        try:
            with open(baseline_path, 'r') as f:
                baseline = json.load(f)
        except Exception as e:
            return {'status': 'error', 'message': f'Failed to read baseline: {e}'}

        def idx(summaries: List[Dict[str, Any]]) -> Dict[str, Dict[str, Any]]:
            d = {}
            for s in summaries:
                key = f"{s['config_name']}::{s['test_path']}"
                d[key] = s
            return d

        current_idx = idx([asdict(s) for s in report.test_summaries])
        baseline_idx = idx(baseline.get('test_summaries', []))

        max_time_pct = self.regression_thresholds['max_scan_time_regression_pct']
        max_mem_pct = self.regression_thresholds['max_memory_regression_pct']

        regressions = []
        for key, base in baseline_idx.items():
            cur = current_idx.get(key)
            if not cur:
                continue
            btime = max(float(base.get('mean_scan_time', 0.0)), 0.0)
            ctime = max(float(cur.get('mean_scan_time', 0.0)), 0.0)
            t_pct = ((ctime - btime) / btime * 100.0) if btime > 0 else 0.0
            bmem = max(float(base.get('mean_memory_peak', 0.0)), 0.0)
            cmem = max(float(cur.get('mean_memory_peak', 0.0)), 0.0)
            m_pct = ((cmem - bmem) / bmem * 100.0) if bmem > 0 else 0.0
            dims = []
            if t_pct > max_time_pct:
                dims.append('time')
            if m_pct > max_mem_pct:
                dims.append('memory')
            if dims:
                cfg, tpath = key.split('::', 1)
                regressions.append({
                    'config_name': cfg,
                    'test_path': tpath,
                    'time_baseline': btime,
                    'time_current': ctime,
                    'time_regression_pct': t_pct,
                    'memory_baseline': bmem,
                    'memory_current': cmem,
                    'memory_regression_pct': m_pct,
                    'dimensions': dims
                })

        return {
            'status': 'regressions_found' if regressions else 'no_regressions',
            'regressions': regressions,
            'thresholds': {'time_pct': max_time_pct, 'memory_pct': max_mem_pct},
            'baseline_path': baseline_path
        }
    
    def generate_visualizations(self, report: PerformanceReport):
        """Generate performance visualizations"""
        try:
            # Create visualizations directory
            viz_dir = os.path.join(self.config['output_path'], 'visualizations')
            os.makedirs(viz_dir, exist_ok=True)
            
            # Scan time comparison
            self._create_scan_time_chart(report, viz_dir)
            
            # Memory usage comparison
            self._create_memory_usage_chart(report, viz_dir)
            
            # Throughput comparison
            self._create_throughput_chart(report, viz_dir)
            
            # Performance trends
            self._create_trends_chart(report, viz_dir)
            
            print(f"Visualizations saved to {viz_dir}")
            
        except ImportError:
            print("Matplotlib not available. Skipping visualizations.")
    
    def _create_scan_time_chart(self, report: PerformanceReport, output_dir: str):
        """Create scan time comparison chart"""
        configs = [s.config_name for s in report.test_summaries]
        scan_times = [s.mean_scan_time for s in report.test_summaries]
        
        plt.figure(figsize=(12, 6))
        bars = plt.bar(configs, scan_times)
        plt.title('Mean Scan Time by Configuration')
        plt.xlabel('Configuration')
        plt.ylabel('Scan Time (seconds)')
        plt.xticks(rotation=45)
        
        # Add threshold line
        threshold = self.config['performance_thresholds']['max_scan_time']
        plt.axhline(y=threshold, color='r', linestyle='--', label=f'Threshold ({threshold}s)')
        plt.legend()
        
        # Color bars based on threshold
        for bar, time in zip(bars, scan_times):
            if time > threshold:
                bar.set_color('red')
            else:
                bar.set_color('green')
        
        plt.tight_layout()
        plt.savefig(os.path.join(output_dir, 'scan_time_comparison.png'), dpi=300, bbox_inches='tight')
        plt.close()
    
    def _create_memory_usage_chart(self, report: PerformanceReport, output_dir: str):
        """Create memory usage comparison chart"""
        configs = [s.config_name for s in report.test_summaries]
        memory_usage = [s.mean_memory_peak for s in report.test_summaries]
        
        plt.figure(figsize=(12, 6))
        bars = plt.bar(configs, memory_usage)
        plt.title('Mean Memory Usage by Configuration')
        plt.xlabel('Configuration')
        plt.ylabel('Memory Usage (MB)')
        plt.xticks(rotation=45)
        
        # Add threshold line
        threshold = self.config['performance_thresholds']['max_memory_usage']
        plt.axhline(y=threshold, color='r', linestyle='--', label=f'Threshold ({threshold}MB)')
        plt.legend()
        
        # Color bars based on threshold
        for bar, memory in zip(bars, memory_usage):
            if memory > threshold:
                bar.set_color('red')
            else:
                bar.set_color('green')
        
        plt.tight_layout()
        plt.savefig(os.path.join(output_dir, 'memory_usage_comparison.png'), dpi=300, bbox_inches='tight')
        plt.close()
    
    def _create_throughput_chart(self, report: PerformanceReport, output_dir: str):
        """Create throughput comparison chart"""
        configs = [s.config_name for s in report.test_summaries]
        throughput = [s.throughput_files_per_second for s in report.test_summaries]
        
        plt.figure(figsize=(12, 6))
        bars = plt.bar(configs, throughput)
        plt.title('File Processing Throughput by Configuration')
        plt.xlabel('Configuration')
        plt.ylabel('Files per Second')
        plt.xticks(rotation=45)
        
        # Add threshold line
        threshold = self.config['performance_thresholds']['min_throughput_files_per_second']
        plt.axhline(y=threshold, color='r', linestyle='--', label=f'Threshold ({threshold} files/s)')
        plt.legend()
        
        # Color bars based on threshold
        for bar, tp in zip(bars, throughput):
            if tp < threshold:
                bar.set_color('red')
            else:
                bar.set_color('green')
        
        plt.tight_layout()
        plt.savefig(os.path.join(output_dir, 'throughput_comparison.png'), dpi=300, bbox_inches='tight')
        plt.close()
    
    def _create_trends_chart(self, report: PerformanceReport, output_dir: str):
        """Create performance trends chart"""
        configs = [s.config_name for s in report.test_summaries]
        scan_times = [s.mean_scan_time for s in report.test_summaries]
        memory_usage = [s.mean_memory_peak for s in report.test_summaries]
        
        fig, (ax1, ax2) = plt.subplots(2, 1, figsize=(12, 10))
        
        # Scan time trends
        ax1.plot(configs, scan_times, 'b-o', linewidth=2, markersize=6)
        ax1.set_title('Scan Time Trends')
        ax1.set_ylabel('Scan Time (seconds)')
        ax1.grid(True, alpha=0.3)
        ax1.tick_params(axis='x', rotation=45)
        
        # Memory usage trends
        ax2.plot(configs, memory_usage, 'r-s', linewidth=2, markersize=6)
        ax2.set_title('Memory Usage Trends')
        ax2.set_xlabel('Configuration')
        ax2.set_ylabel('Memory Usage (MB)')
        ax2.grid(True, alpha=0.3)
        ax2.tick_params(axis='x', rotation=45)
        
        plt.tight_layout()
        plt.savefig(os.path.join(output_dir, 'performance_trends.png'), dpi=300, bbox_inches='tight')
        plt.close()

def main():
    """Main function"""
    parser = argparse.ArgumentParser(description='Comprehensive Performance Testing for WordPress Semgrep Rules')
    parser.add_argument('--config', help='Custom test configuration file')
    parser.add_argument('--rules', help='Path to rules directory')
    parser.add_argument('--tests', help='Path to test files directory')
    parser.add_argument('--output', help='Output file for test results')
    parser.add_argument('--iterations', type=int, default=10, help='Number of iterations per test')
    parser.add_argument('--warmup', type=int, default=3, help='Number of warmup runs')
    parser.add_argument('--verbose', action='store_true', help='Verbose output')
    parser.add_argument('--json', action='store_true', help='Output results in JSON format')
    parser.add_argument('--html', action='store_true', help='Generate HTML report')
    parser.add_argument('--optimize', action='store_true', help='Run optimization analysis')
    # Deprecated placeholders (kept for backward compatibility, will be overridden below)
    parser.add_argument('--baseline', action='store_true', help='Save current results as baseline and exit')
    parser.add_argument('--compare', action='store_true', help='Compare against baseline and fail on regression')
    parser.add_argument('--visualize', action='store_true', help='Generate performance visualizations')
    parser.add_argument('--csv', action='store_true', help='Export CSV report')
    parser.add_argument('--md', action='store_true', help='Export Markdown report')
    parser.add_argument('--baseline-file', help='Custom baseline file path for save/compare')
    
    args = parser.parse_args()
    
    # Initialize tester
    tester = ComprehensivePerformanceTester(args.config)
    
    # Override config with command line arguments
    if args.rules:
        tester.config['rules_path'] = args.rules
    if args.tests:
        tester.config['tests_path'] = args.tests
    if args.output:
        tester.config['output_path'] = args.output
    if args.iterations is not None:
        tester.config['iterations'] = args.iterations
    if args.warmup is not None:
        tester.config['warmup_runs'] = args.warmup
    
    # Run comprehensive tests
    report = tester.run_comprehensive_tests()
    
    # Generate visualizations if requested
    if args.visualize:
        tester.generate_visualizations(report)
    if args.csv:
        tester.export_csv(report)
    if args.md:
        tester.export_markdown(report)
    
    # Print summary
    print(f"\n{'='*60}")
    print("COMPREHENSIVE PERFORMANCE TESTING SUMMARY")
    print(f"{'='*60}")
    print(f"Total tests run: {report.total_tests}")
    print(f"Successful tests: {report.successful_tests}")
    print(f"Failed tests: {report.failed_tests}")
    print(f"Success rate: {report.successful_tests/report.total_tests*100:.1f}%")
    print(f"Total duration: {report.duration:.2f} seconds")
    
    print(f"\nTop performing configurations:")
    for i, config in enumerate(report.performance_rankings['fastest_configs'][:3], 1):
        print(f"  {i}. {config}")
    
    print(f"\nOptimization recommendations:")
    for i, rec in enumerate(report.optimization_recommendations[:3], 1):
        print(f"  {i}. {rec}")
    
    if args.json:
        print(f"\nDetailed results saved to: {tester.config['output_path']}/comprehensive-performance-report.json")

    # Baseline handling
    if args.baseline:
        tester.save_baseline(report, args.baseline_file)
        print("Baseline saved. Exiting as requested.")
        sys.exit(0)

    if args.compare:
        comparison = tester.compare_with_baseline(report, args.baseline_file)
        if comparison.get('status') == 'no_baseline':
            print(f"No baseline found at {comparison.get('baseline_path')}")
            sys.exit(2)
        if comparison.get('status') == 'error':
            print(f"Error comparing with baseline: {comparison.get('message')}")
            sys.exit(2)
        regs = comparison.get('regressions', [])
        if regs:
            print("\nPerformance regressions detected:")
            for r in regs:
                print(f"  {r['config_name']} on {r['test_path']}: time +{r['time_regression_pct']:.1f}%, memory +{r['memory_regression_pct']:.1f}%")
            sys.exit(1)
        else:
            print("No performance regressions detected.")

if __name__ == '__main__':
    main()
