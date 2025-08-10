#!/usr/bin/env python3
"""
WordPress Semgrep Rules - Performance Benchmarking Framework

This script provides comprehensive performance benchmarking for WordPress Semgrep rules,
measuring scan times, memory usage, and other performance metrics.

Usage:
    python performance-benchmarks.py [options]

Options:
    --config <file>     Use custom benchmark configuration file
    --rules <path>      Path to rules directory
    --tests <path>      Path to test files directory
    --output <file>     Output file for benchmark results
    --iterations <int>  Number of iterations per benchmark (default: 5)
    --warmup <int>      Number of warmup runs (default: 2)
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
import statistics
from pathlib import Path
from typing import Dict, List, Any, Optional
from dataclasses import dataclass, asdict
import psutil
import threading
import queue

@dataclass
class BenchmarkResult:
    """Represents a single benchmark result"""
    config_name: str
    test_path: str
    iteration: int
    duration: float
    memory_peak: float
    memory_final: float
    cpu_percent: float
    findings_count: int
    success: bool
    error_message: Optional[str] = None

@dataclass
class BenchmarkSummary:
    """Represents benchmark summary statistics"""
    config_name: str
    test_path: str
    iterations: int
    mean_duration: float
    median_duration: float
    std_duration: float
    min_duration: float
    max_duration: float
    mean_memory_peak: float
    mean_memory_final: float
    mean_cpu_percent: float
    mean_findings_count: int
    success_rate: float
    total_duration: float

@dataclass
class BenchmarkReport:
    """Comprehensive benchmark report"""
    timestamp: str
    duration: float
    total_benchmarks: int
    successful_benchmarks: int
    failed_benchmarks: int
    benchmark_summaries: List[BenchmarkSummary]
    performance_rankings: Dict[str, Any]
    recommendations: List[str]

class PerformanceBenchmarker:
    """Performance benchmarking framework"""
    
    def __init__(self, config_path: Optional[str] = None):
        self.config = self._load_config(config_path)
        self.results: List[BenchmarkResult] = []
        self.start_time = time.time()
        self.monitoring_active = False
        self.monitoring_thread = None
        self.monitoring_queue = queue.Queue()
        
    def _load_config(self, config_path: Optional[str] = None) -> Dict[str, Any]:
        """Load benchmark configuration"""
        default_config = {
            'semgrep_binary': 'semgrep',
            'rules_path': '../packs/',
            'tests_path': './',
            'configs_path': '../configs/',
            'output_path': './benchmark-results/',
            'iterations': 5,
            'warmup_runs': 2,
            'timeout': 300,  # 5 minutes
            'benchmark_configs': [
                'basic.yaml',
                'strict.yaml',
                'plugin-development.yaml',
                'optimized-15s.yaml',
                'optimized-30s.yaml'
            ],
            'test_scenarios': [
                {
                    'name': 'small_test',
                    'path': 'vulnerable-examples/',
                    'description': 'Small test files'
                },
                {
                    'name': 'medium_test',
                    'path': './',
                    'description': 'All test files'
                },
                {
                    'name': 'large_test',
                    'path': '../',
                    'description': 'Entire project'
                }
            ]
        }
        
        if config_path and os.path.exists(config_path):
            with open(config_path, 'r') as f:
                custom_config = json.load(f)
                default_config.update(custom_config)
                
        return default_config
    
    def run_all_benchmarks(self) -> BenchmarkReport:
        """Run all performance benchmarks"""
        print("Starting Performance Benchmarking...")
        print("=" * 50)
        
        # Create output directory
        os.makedirs(self.config['output_path'], exist_ok=True)
        
        # Run warmup runs
        if self.config['warmup_runs'] > 0:
            print(f"\nRunning {self.config['warmup_runs']} warmup runs...")
            self._run_warmup()
        
        # Run benchmarks for each configuration and test scenario
        for config_name in self.config['benchmark_configs']:
            config_path = os.path.join(self.config['configs_path'], config_name)
            
            if not os.path.exists(config_path):
                print(f"Warning: Configuration not found: {config_path}")
                continue
            
            for scenario in self.config['test_scenarios']:
                test_path = os.path.join(self.config['tests_path'], scenario['path'])
                
                if not os.path.exists(test_path):
                    print(f"Warning: Test path not found: {test_path}")
                    continue
                
                print(f"\nBenchmarking {config_name} on {scenario['name']}...")
                self._run_benchmark(config_name, config_path, test_path, scenario['name'])
        
        # Generate comprehensive report
        return self._generate_report()
    
    def _run_warmup(self):
        """Run warmup runs to stabilize performance"""
        warmup_config = os.path.join(self.config['configs_path'], 'basic.yaml')
        warmup_path = os.path.join(self.config['tests_path'], 'vulnerable-examples/')
        
        for i in range(self.config['warmup_runs']):
            print(f"  Warmup run {i + 1}/{self.config['warmup_runs']}...")
            self._run_single_benchmark('warmup', warmup_config, warmup_path, 0)
    
    def _run_benchmark(self, config_name: str, config_path: str, test_path: str, scenario_name: str):
        """Run benchmark for a specific configuration and test scenario"""
        for iteration in range(self.config['iterations']):
            result = self._run_single_benchmark(config_name, config_path, test_path, iteration)
            self.results.append(result)
    
    def _run_single_benchmark(self, config_name: str, config_path: str, test_path: str, 
                            iteration: int) -> BenchmarkResult:
        """Run a single benchmark iteration"""
        start_time = time.time()
        
        # Start monitoring thread
        self._start_monitoring()
        
        try:
            # Run semgrep
            cmd = [
                self.config['semgrep_binary'],
                '--config', config_path,
                '--json',
                '--quiet',
                test_path
            ]
            
            result = subprocess.run(cmd, capture_output=True, text=True, 
                                  timeout=self.config['timeout'])
            
            # Stop monitoring
            self._stop_monitoring()
            
            # Get monitoring data
            monitoring_data = self._get_monitoring_data()
            
            # Parse results
            if result.returncode == 0:
                findings = json.loads(result.stdout) if result.stdout.strip() else []
                findings_count = len(findings)
                success = True
                error_message = None
            else:
                findings_count = 0
                success = False
                error_message = result.stderr
            
            duration = time.time() - start_time
            
            return BenchmarkResult(
                config_name=config_name,
                test_path=test_path,
                iteration=iteration,
                duration=duration,
                memory_peak=monitoring_data.get('memory_peak', 0.0),
                memory_final=monitoring_data.get('memory_final', 0.0),
                cpu_percent=monitoring_data.get('cpu_percent', 0.0),
                findings_count=findings_count,
                success=success,
                error_message=error_message
            )
            
        except subprocess.TimeoutExpired:
            self._stop_monitoring()
            monitoring_data = self._get_monitoring_data()
            
            return BenchmarkResult(
                config_name=config_name,
                test_path=test_path,
                iteration=iteration,
                duration=self.config['timeout'],
                memory_peak=monitoring_data.get('memory_peak', 0.0),
                memory_final=monitoring_data.get('memory_final', 0.0),
                cpu_percent=monitoring_data.get('cpu_percent', 0.0),
                findings_count=0,
                success=False,
                error_message='Benchmark timeout'
            )
        except Exception as e:
            self._stop_monitoring()
            
            return BenchmarkResult(
                config_name=config_name,
                test_path=test_path,
                iteration=iteration,
                duration=time.time() - start_time,
                memory_peak=0.0,
                memory_final=0.0,
                cpu_percent=0.0,
                findings_count=0,
                success=False,
                error_message=str(e)
            )
    
    def _start_monitoring(self):
        """Start performance monitoring in background thread"""
        self.monitoring_active = True
        self.monitoring_thread = threading.Thread(target=self._monitor_performance)
        self.monitoring_thread.daemon = True
        self.monitoring_thread.start()
    
    def _stop_monitoring(self):
        """Stop performance monitoring"""
        self.monitoring_active = False
        if self.monitoring_thread:
            self.monitoring_thread.join(timeout=1.0)
    
    def _monitor_performance(self):
        """Monitor performance metrics in background thread"""
        process = psutil.Process()
        memory_peak = 0.0
        cpu_samples = []
        
        while self.monitoring_active:
            try:
                # Memory monitoring
                memory_info = process.memory_info()
                current_memory = memory_info.rss / 1024 / 1024  # MB
                memory_peak = max(memory_peak, current_memory)
                
                # CPU monitoring
                cpu_percent = process.cpu_percent(interval=0.1)
                if cpu_percent > 0:
                    cpu_samples.append(cpu_percent)
                
                time.sleep(0.1)
            except (psutil.NoSuchProcess, psutil.AccessDenied):
                break
        
        # Store monitoring data
        self.monitoring_queue.put({
            'memory_peak': memory_peak,
            'memory_final': current_memory if 'current_memory' in locals() else 0.0,
            'cpu_percent': statistics.mean(cpu_samples) if cpu_samples else 0.0
        })
    
    def _get_monitoring_data(self) -> Dict[str, float]:
        """Get monitoring data from queue"""
        try:
            return self.monitoring_queue.get_nowait()
        except queue.Empty:
            return {'memory_peak': 0.0, 'memory_final': 0.0, 'cpu_percent': 0.0}
    
    def _generate_report(self) -> BenchmarkReport:
        """Generate comprehensive benchmark report"""
        # Group results by configuration and test path
        grouped_results = {}
        for result in self.results:
            key = f"{result.config_name}:{result.test_path}"
            if key not in grouped_results:
                grouped_results[key] = []
            grouped_results[key].append(result)
        
        # Generate summaries for each group
        benchmark_summaries = []
        for key, results in grouped_results.items():
            config_name, test_path = key.split(':', 1)
            summary = self._generate_summary(config_name, test_path, results)
            benchmark_summaries.append(summary)
        
        # Generate performance rankings
        performance_rankings = self._generate_performance_rankings(benchmark_summaries)
        
        # Generate recommendations
        recommendations = self._generate_recommendations(benchmark_summaries)
        
        return BenchmarkReport(
            timestamp=datetime.datetime.now().isoformat(),
            duration=time.time() - self.start_time,
            total_benchmarks=len(self.results),
            successful_benchmarks=len([r for r in self.results if r.success]),
            failed_benchmarks=len([r for r in self.results if not r.success]),
            benchmark_summaries=benchmark_summaries,
            performance_rankings=performance_rankings,
            recommendations=recommendations
        )
    
    def _generate_summary(self, config_name: str, test_path: str, 
                         results: List[BenchmarkResult]) -> BenchmarkSummary:
        """Generate summary statistics for a group of benchmark results"""
        successful_results = [r for r in results if r.success]
        
        if not successful_results:
            return BenchmarkSummary(
                config_name=config_name,
                test_path=test_path,
                iterations=len(results),
                mean_duration=0.0,
                median_duration=0.0,
                std_duration=0.0,
                min_duration=0.0,
                max_duration=0.0,
                mean_memory_peak=0.0,
                mean_memory_final=0.0,
                mean_cpu_percent=0.0,
                mean_findings_count=0,
                success_rate=0.0,
                total_duration=sum(r.duration for r in results)
            )
        
        durations = [r.duration for r in successful_results]
        memory_peaks = [r.memory_peak for r in successful_results]
        memory_finals = [r.memory_final for r in successful_results]
        cpu_percents = [r.cpu_percent for r in successful_results]
        findings_counts = [r.findings_count for r in successful_results]
        
        return BenchmarkSummary(
            config_name=config_name,
            test_path=test_path,
            iterations=len(results),
            mean_duration=statistics.mean(durations),
            median_duration=statistics.median(durations),
            std_duration=statistics.stdev(durations) if len(durations) > 1 else 0.0,
            min_duration=min(durations),
            max_duration=max(durations),
            mean_memory_peak=statistics.mean(memory_peaks),
            mean_memory_final=statistics.mean(memory_finals),
            mean_cpu_percent=statistics.mean(cpu_percents),
            mean_findings_count=statistics.mean(findings_counts),
            success_rate=len(successful_results) / len(results),
            total_duration=sum(r.duration for r in results)
        )
    
    def _generate_performance_rankings(self, summaries: List[BenchmarkSummary]) -> Dict[str, Any]:
        """Generate performance rankings"""
        rankings = {
            'fastest_configs': [],
            'most_memory_efficient': [],
            'most_cpu_efficient': [],
            'most_findings': []
        }
        
        # Group by test path for fair comparison
        test_paths = set(summary.test_path for summary in summaries)
        
        for test_path in test_paths:
            path_summaries = [s for s in summaries if s.test_path == test_path]
            
            # Fastest configs
            fastest = sorted(path_summaries, key=lambda x: x.mean_duration)[:3]
            rankings['fastest_configs'].extend([
                {'config': s.config_name, 'test_path': s.test_path, 'duration': s.mean_duration}
                for s in fastest
            ])
            
            # Most memory efficient
            memory_efficient = sorted(path_summaries, key=lambda x: x.mean_memory_peak)[:3]
            rankings['most_memory_efficient'].extend([
                {'config': s.config_name, 'test_path': s.test_path, 'memory': s.mean_memory_peak}
                for s in memory_efficient
            ])
            
            # Most CPU efficient
            cpu_efficient = sorted(path_summaries, key=lambda x: x.mean_cpu_percent)[:3]
            rankings['most_cpu_efficient'].extend([
                {'config': s.config_name, 'test_path': s.test_path, 'cpu': s.mean_cpu_percent}
                for s in cpu_efficient
            ])
            
            # Most findings
            most_findings = sorted(path_summaries, key=lambda x: x.mean_findings_count, reverse=True)[:3]
            rankings['most_findings'].extend([
                {'config': s.config_name, 'test_path': s.test_path, 'findings': s.mean_findings_count}
                for s in most_findings
            ])
        
        return rankings
    
    def _generate_recommendations(self, summaries: List[BenchmarkSummary]) -> List[str]:
        """Generate recommendations based on benchmark results"""
        recommendations = []
        
        # Performance recommendations
        slow_configs = [s for s in summaries if s.mean_duration > 30.0]
        if slow_configs:
            recommendations.append(f"Optimize {len(slow_configs)} slow configurations (>30s)")
        
        # Memory recommendations
        high_memory_configs = [s for s in summaries if s.mean_memory_peak > 500.0]
        if high_memory_configs:
            recommendations.append(f"Optimize {len(high_memory_configs)} high-memory configurations (>500MB)")
        
        # Success rate recommendations
        low_success_configs = [s for s in summaries if s.success_rate < 0.8]
        if low_success_configs:
            recommendations.append(f"Investigate {len(low_success_configs)} configurations with low success rate (<80%)")
        
        # Variability recommendations
        high_variability_configs = [s for s in summaries if s.std_duration > s.mean_duration * 0.2]
        if high_variability_configs:
            recommendations.append(f"Investigate {len(high_variability_configs)} configurations with high performance variability")
        
        return recommendations
    
    def save_report(self, report: BenchmarkReport, output_file: str):
        """Save benchmark report to file"""
        report_dict = asdict(report)
        
        with open(output_file, 'w') as f:
            json.dump(report_dict, f, indent=2)
        
        print(f"\nBenchmark report saved to: {output_file}")
    
    def generate_html_report(self, report: BenchmarkReport, output_file: str):
        """Generate HTML benchmark report"""
        html_content = self._generate_html_content(report)
        
        with open(output_file, 'w') as f:
            f.write(html_content)
        
        print(f"HTML benchmark report generated: {output_file}")
    
    def _generate_html_content(self, report: BenchmarkReport) -> str:
        """Generate HTML content for the benchmark report"""
        return f"""
<!DOCTYPE html>
<html>
<head>
    <title>WordPress Semgrep Rules - Performance Benchmark Report</title>
    <style>
        body {{ font-family: Arial, sans-serif; margin: 20px; }}
        .header {{ background-color: #f0f0f0; padding: 20px; border-radius: 5px; }}
        .summary {{ margin: 20px 0; }}
        .benchmark-summary {{ margin: 10px 0; padding: 15px; border-radius: 5px; background-color: #f8f9fa; }}
        .rankings {{ margin: 20px 0; }}
        .ranking-item {{ margin: 5px 0; padding: 5px; background-color: #e9ecef; }}
        .recommendations {{ background-color: #e2e3e5; padding: 15px; border-radius: 5px; }}
    </style>
</head>
<body>
    <div class="header">
        <h1>WordPress Semgrep Rules - Performance Benchmark Report</h1>
        <p>Generated: {report.timestamp}</p>
        <p>Duration: {report.duration:.2f} seconds</p>
    </div>
    
    <div class="summary">
        <h2>Benchmark Summary</h2>
        <p>Total Benchmarks: {report.total_benchmarks}</p>
        <p>Successful: {report.successful_benchmarks}</p>
        <p>Failed: {report.failed_benchmarks}</p>
        <p>Success Rate: {(report.successful_benchmarks / report.total_benchmarks * 100):.1f}%</p>
    </div>
    
    <div class="rankings">
        <h2>Performance Rankings</h2>
        <h3>Fastest Configurations</h3>
        {''.join(f'<div class="ranking-item">{item["config"]} on {item["test_path"]}: {item["duration"]:.2f}s</div>' for item in report.performance_rankings['fastest_configs'][:5])}
        
        <h3>Most Memory Efficient</h3>
        {''.join(f'<div class="ranking-item">{item["config"]} on {item["test_path"]}: {item["memory"]:.1f}MB</div>' for item in report.performance_rankings['most_memory_efficient'][:5])}
        
        <h3>Most CPU Efficient</h3>
        {''.join(f'<div class="ranking-item">{item["config"]} on {item["test_path"]}: {item["cpu"]:.1f}%</div>' for item in report.performance_rankings['most_cpu_efficient'][:5])}
    </div>
    
    <div class="recommendations">
        <h2>Recommendations</h2>
        <ul>
            {''.join(f'<li>{rec}</li>' for rec in report.recommendations)}
        </ul>
    </div>
    
    <h2>Detailed Benchmark Results</h2>
    {''.join(self._generate_benchmark_summary_html(summary) for summary in report.benchmark_summaries)}
</body>
</html>
"""
    
    def _generate_benchmark_summary_html(self, summary: BenchmarkSummary) -> str:
        """Generate HTML for a single benchmark summary"""
        return f"""
    <div class="benchmark-summary">
        <h3>{summary.config_name} - {os.path.basename(summary.test_path)}</h3>
        <p>Duration: {summary.mean_duration:.2f}s ± {summary.std_duration:.2f}s (min: {summary.min_duration:.2f}s, max: {summary.max_duration:.2f}s)</p>
        <p>Memory: {summary.mean_memory_peak:.1f}MB peak, {summary.mean_memory_final:.1f}MB final</p>
        <p>CPU: {summary.mean_cpu_percent:.1f}%</p>
        <p>Findings: {summary.mean_findings_count:.1f}</p>
        <p>Success Rate: {summary.success_rate:.1%}</p>
    </div>
"""

def main():
    """Main entry point"""
    parser = argparse.ArgumentParser(description='WordPress Semgrep Rules Performance Benchmarking')
    parser.add_argument('--config', help='Custom benchmark configuration file')
    parser.add_argument('--rules', help='Path to rules directory')
    parser.add_argument('--tests', help='Path to test files directory')
    parser.add_argument('--output', help='Output file for benchmark results')
    parser.add_argument('--iterations', type=int, default=5, help='Number of iterations per benchmark')
    parser.add_argument('--warmup', type=int, default=2, help='Number of warmup runs')
    parser.add_argument('--verbose', action='store_true', help='Verbose output')
    parser.add_argument('--json', action='store_true', help='Output results in JSON format')
    parser.add_argument('--html', action='store_true', help='Generate HTML report')
    
    args = parser.parse_args()
    
    # Initialize benchmarker
    benchmarker = PerformanceBenchmarker(args.config)
    
    # Override config with command line arguments
    if args.rules:
        benchmarker.config['rules_path'] = args.rules
    if args.tests:
        benchmarker.config['tests_path'] = args.tests
    if args.iterations:
        benchmarker.config['iterations'] = args.iterations
    if args.warmup:
        benchmarker.config['warmup_runs'] = args.warmup
    
    # Run benchmarks
    report = benchmarker.run_all_benchmarks()
    
    # Output results
    output_file = args.output or 'benchmark-results/performance-benchmark-report.json'
    
    if args.json:
        benchmarker.save_report(report, output_file)
    
    if args.html:
        html_file = output_file.replace('.json', '.html')
        benchmarker.generate_html_report(report, html_file)
    
    # Print summary
    print(f"\nBenchmark Summary:")
    print(f"Total Benchmarks: {report.total_benchmarks}")
    print(f"Successful: {report.successful_benchmarks}")
    print(f"Failed: {report.failed_benchmarks}")
    print(f"Success Rate: {(report.successful_benchmarks / report.total_benchmarks * 100):.1f}%")
    print(f"Duration: {report.duration:.2f} seconds")
    
    # Print top performers
    print(f"\nTop Performers:")
    for item in report.performance_rankings['fastest_configs'][:3]:
        print(f"  {item['config']}: {item['duration']:.2f}s")

if __name__ == '__main__':
    main()
