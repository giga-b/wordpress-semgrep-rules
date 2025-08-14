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
try:
    import psutil  # type: ignore
except Exception:
    psutil = None  # Monitoring will be disabled if psutil is unavailable
import threading
import queue
import concurrent.futures

# Add tooling directory to path for cache utilities
try:
    TOOLING_DIR = str((Path(__file__).parent.parent / 'tooling').resolve())
    if TOOLING_DIR not in sys.path:
        sys.path.insert(0, TOOLING_DIR)
    from cache_manager import get_cache_manager  # type: ignore
except Exception:
    get_cache_manager = None  # Caching will be disabled if unavailable

@dataclass
class BenchmarkResult:
    """Represents a single benchmark result"""
    config_name: str
    test_path: str
    scenario_name: str
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
    scenario_name: str
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
    # Added explicit averages for easier JSON consumption
    avg_duration: float
    avg_memory_peak: float

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
    
    def __init__(self, config_path: Optional[str] = None, use_cache: bool = True):
        self.config = self._load_config(config_path)
        self.results: List[BenchmarkResult] = []
        self.start_time = time.time()
        self.monitoring_active = False
        self.monitoring_thread = None
        self.monitoring_queue = queue.Queue()
        self.use_cache = use_cache and (get_cache_manager is not None)
        self.cache_manager = get_cache_manager() if self.use_cache else None
        self._semgrep_version_cache: Optional[str] = None
        
    def _get_semgrep_memory_mb(self) -> float:
        """Return total RSS memory in MB for semgrep child processes.

        Falls back to current process RSS if no semgrep child is found or psutil is unavailable.
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
            if total_rss == 0:
                # Fallback to current process memory if no semgrep processes detected
                try:
                    mi = parent.memory_info()
                    total_rss = getattr(mi, 'rss', 0)
                except Exception:
                    return 0.0
            return float(total_rss) / 1024.0 / 1024.0
        except Exception:
            return 0.0

    def _get_semgrep_cpu_percent(self) -> float:
        """Return summed CPU percent for semgrep child processes.

        Falls back to current process CPU percent if no semgrep child is found or psutil is unavailable.
        Uses non-blocking sampling (interval=0.0) and relies on outer loop sleep for cadence.
        """
        if psutil is None:
            return 0.0
        try:
            parent = psutil.Process()
            total_percent = 0.0
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
                except (psutil.NoSuchProcess, psutil.AccessDenied):
                    continue
            if not found:
                try:
                    return float(parent.cpu_percent(interval=0.0))
                except Exception:
                    return 0.0
            return total_percent
        except Exception:
            return 0.0

    def _load_config(self, config_path: Optional[str] = None) -> Dict[str, Any]:
        """Load benchmark configuration and normalize paths relative to script directory"""
        base_dir = Path(__file__).parent.resolve()
        project_root = base_dir.parent

        default_config = {
            'semgrep_binary': 'semgrep',
            'rules_path': str(project_root / 'packs'),
            'tests_path': str(base_dir),
            'configs_path': str(project_root / 'configs'),
            'output_path': str(base_dir / 'benchmark-results'),
            # Limit scope for performance runs to typical PHP targets and exclude heavy dirs
            'include_globs': ['**/*.php'],
            'exclude_paths': ['.git', 'results', 'corpus', 'node_modules', 'vendor'],
            'jobs': 1,
            'max_target_bytes': 5000000,
            'iterations': 5,
            'warmup_runs': 2,
            'timeout': 300,  # 5 minutes
            'regression_thresholds': {
                'max_duration_regression_pct': 15.0,
                'max_memory_regression_pct': 10.0
            },
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
                    'path': 'vulnerable-examples',
                    'description': 'Small test files'
                },
                {
                    'name': 'medium_test',
                    'path': '.',
                    'description': 'All test files'
                },
                {
                    'name': 'large_test',
                    'path': str(project_root),
                    'description': 'Entire project'
                }
            ]
        }
        
        if config_path and os.path.exists(config_path):
            with open(config_path, 'r', encoding='utf-8') as f:
                custom_config = json.load(f)
                default_config.update(custom_config)

        # Normalize critical paths to absolute strings
        for key in ['rules_path', 'tests_path', 'configs_path', 'output_path']:
            if key in default_config:
                default_config[key] = str(Path(default_config[key]).resolve())
        
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
        
        # Build task list for non-cached scenarios
        tasks_to_run = []
        for config_name in self.config['benchmark_configs']:
            config_path = os.path.join(self.config['configs_path'], config_name)
            if not os.path.exists(config_path):
                print(f"Warning: Configuration not found: {config_path}")
                continue
            for scenario in self.config['test_scenarios']:
                scenario_path = scenario['path']
                test_path = scenario_path if os.path.isabs(scenario_path) else os.path.join(self.config['tests_path'], scenario_path)
                if not os.path.exists(test_path):
                    print(f"Warning: Test path not found: {test_path}")
                    continue
                print(f"\nBenchmarking {config_name} on {scenario['name']}...")
                # Cache check
                cached_block = None
                cache_key = self._make_cache_key(config_name, config_path, test_path)
                if self.use_cache and self.cache_manager is not None:
                    try:
                        cached_block = self.cache_manager.get('performance_data', cache_key)
                    except Exception:
                        cached_block = None
                if cached_block and isinstance(cached_block, list):
                    for item in cached_block:
                        try:
                            self.results.append(BenchmarkResult(**item))
                        except Exception:
                            pass
                    print("Using cached benchmark results for this scenario.")
                else:
                    tasks_to_run.append((config_name, config_path, test_path, scenario['name'], cache_key))

        # Execute pending tasks (optionally in parallel)
        if tasks_to_run:
            enable_parallel = bool(self.config.get('enable_parallel', False))
            workers = int(self.config.get('parallel_workers', 0) or 0)
            if enable_parallel and workers > 1:
                print(f"\nRunning {len(tasks_to_run)} benchmark task(s) in parallel with {workers} worker(s)...")
                with concurrent.futures.ThreadPoolExecutor(max_workers=workers) as executor:
                    future_to_task = {
                        executor.submit(self._run_and_cache_block, *t): t for t in tasks_to_run
                    }
                    for future in concurrent.futures.as_completed(future_to_task):
                        _ = future_to_task[future]
                        try:
                            future.result()
                        except Exception as e:
                            print(f"Parallel task failed: {e}")
            else:
                for t in tasks_to_run:
                    self._run_and_cache_block(*t)
        
        # Generate comprehensive report
        return self._generate_report()

    def _run_and_cache_block(self, config_name: str, config_path: str, test_path: str, scenario_name: str, cache_key: str) -> None:
        """Run a benchmark block and cache its results."""
        prev_len = len(self.results)
        self._run_benchmark(config_name, config_path, test_path, scenario_name)
        new_block = [asdict(r) for r in self.results[prev_len:]]
        if self.use_cache and self.cache_manager is not None and new_block:
            try:
                self.cache_manager.set('performance_data', new_block, cache_key)
            except Exception:
                pass
    
    def _run_warmup(self):
        """Run warmup runs to stabilize performance"""
        warmup_config = os.path.join(self.config['configs_path'], 'basic.yaml')
        warmup_path = os.path.join(self.config['tests_path'], 'vulnerable-examples')
        
        for i in range(self.config['warmup_runs']):
            print(f"  Warmup run {i + 1}/{self.config['warmup_runs']}...")
            self._run_single_benchmark('warmup', warmup_config, warmup_path, 'warmup', 0)
    
    def _run_benchmark(self, config_name: str, config_path: str, test_path: str, scenario_name: str):
        """Run benchmark for a specific configuration and test scenario"""
        for iteration in range(self.config['iterations']):
            result = self._run_single_benchmark(config_name, config_path, test_path, scenario_name, iteration)
            self.results.append(result)
    
    def _run_single_benchmark(self, config_name: str, config_path: str, test_path: str, 
                            scenario_name: str, iteration: int) -> BenchmarkResult:
        """Run a single benchmark iteration"""
        start_time = time.time()
        
        # Start monitoring thread
        self._start_monitoring()
        
        try:
            # Run semgrep
            cmd = [
                self.config['semgrep_binary'], 'scan',
                '--config', config_path,
                '--json',
                '--quiet',
            ]

            # Metrics off to reduce overhead
            cmd += ['--metrics=off']

            # Apply include globs
            for g in self.config.get('include_globs', []):
                cmd += ['--include', g]

            # Apply excludes
            for ex in self.config.get('exclude_paths', []):
                cmd += ['--exclude', ex]

            # Constrain resource usage
            jobs = int(self.config.get('jobs', 0) or 0)
            if jobs > 0:
                cmd += ['--jobs', str(jobs)]
            mtb = int(self.config.get('max_target_bytes', 0) or 0)
            if mtb > 0:
                cmd += ['--max-target-bytes', str(mtb)]

            # Target path last
            cmd += [test_path]
            
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                cwd=self.config['tests_path'],
                timeout=self.config['timeout'],
                encoding='utf-8',
                errors='replace'
            )
            
            # Stop monitoring
            self._stop_monitoring()
            
            # Get monitoring data
            monitoring_data = self._get_monitoring_data()
            
            # Parse results
            if result.returncode == 0:
                parsed = json.loads(result.stdout) if result.stdout.strip() else {}
                if isinstance(parsed, dict):
                    findings_count = len(parsed.get('results', []))
                elif isinstance(parsed, list):
                    findings_count = len(parsed)
                else:
                    findings_count = 0
                success = True
                error_message = None
            else:
                findings_count = 0
                success = False
                error_message = result.stderr or 'Semgrep scan failed'
            
            duration = time.time() - start_time
            
            return BenchmarkResult(
                config_name=config_name,
                test_path=test_path,
                scenario_name=scenario_name,
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
                scenario_name=scenario_name,
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
                scenario_name=scenario_name,
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
        # Only start monitoring if psutil is available
        if psutil is None:
            return
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
        if psutil is None:
            return
        memory_peak = 0.0
        cpu_samples = []
        last_memory = 0.0
        
        while self.monitoring_active:
            try:
                # Memory monitoring (sum of semgrep child processes)
                current_memory = self._get_semgrep_memory_mb()
                memory_peak = max(memory_peak, current_memory)
                last_memory = current_memory
                
                # CPU monitoring
                cpu_percent = self._get_semgrep_cpu_percent()
                if cpu_percent > 0:
                    cpu_samples.append(cpu_percent)
                
                time.sleep(0.1)
            except (psutil.NoSuchProcess, psutil.AccessDenied):
                break
        
        # Store monitoring data
        self.monitoring_queue.put({
            'memory_peak': memory_peak,
            'memory_final': last_memory,
            'cpu_percent': statistics.mean(cpu_samples) if cpu_samples else 0.0
        })
    
    def _get_monitoring_data(self) -> Dict[str, float]:
        """Get monitoring data from queue"""
        try:
            return self.monitoring_queue.get_nowait()
        except queue.Empty:
            return {'memory_peak': 0.0, 'memory_final': 0.0, 'cpu_percent': 0.0}

    def _get_semgrep_version(self) -> str:
        """Get semgrep version string for cache key stability."""
        if self._semgrep_version_cache is not None:
            return self._semgrep_version_cache
        try:
            result = subprocess.run([self.config['semgrep_binary'], '--version'], capture_output=True, text=True)
            if result.returncode == 0:
                self._semgrep_version_cache = (result.stdout or '').strip()
            else:
                self._semgrep_version_cache = 'unknown'
        except Exception:
            self._semgrep_version_cache = 'unknown'
        return self._semgrep_version_cache

    def _make_cache_key(self, config_name: str, config_path: str, test_path: str) -> str:
        """Create a stable cache key for a (config, scenario) tuple."""
        try:
            with open(config_path, 'rb') as f:
                cfg_hash = __import__('hashlib').sha256(f.read()).hexdigest()[:16]
        except Exception:
            cfg_hash = 'nohash'
        parts = [
            'v1',
            config_name,
            cfg_hash,
            os.path.abspath(test_path),
            ','.join(self.config.get('include_globs', [])),
            ','.join(self.config.get('exclude_paths', [])),
            str(self.config.get('jobs', '')),
            str(self.config.get('max_target_bytes', '')),
            self._get_semgrep_version(),
        ]
        return '|'.join(parts)
    
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
            scenario_name = results[0].scenario_name if results else ''
            summary = self._generate_summary(config_name, test_path, scenario_name, results)
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
                         scenario_name: str, results: List[BenchmarkResult]) -> BenchmarkSummary:
        """Generate summary statistics for a group of benchmark results"""
        successful_results = [r for r in results if r.success]
        
        if not successful_results:
            return BenchmarkSummary(
                config_name=config_name,
                test_path=test_path,
                scenario_name=scenario_name,
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
                total_duration=sum(r.duration for r in results),
                avg_duration=0.0,
                avg_memory_peak=0.0
            )
        
        durations = [r.duration for r in successful_results]
        memory_peaks = [r.memory_peak for r in successful_results]
        memory_finals = [r.memory_final for r in successful_results]
        cpu_percents = [r.cpu_percent for r in successful_results]
        findings_counts = [r.findings_count for r in successful_results]
        
        return BenchmarkSummary(
            config_name=config_name,
            test_path=test_path,
            scenario_name=scenario_name,
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
            total_duration=sum(r.duration for r in results),
            avg_duration=statistics.mean(durations),
            avg_memory_peak=statistics.mean(memory_peaks)
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
        
        # Ensure output directory exists
        output_dir = os.path.dirname(output_file)
        if output_dir:
            os.makedirs(output_dir, exist_ok=True)
        
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(report_dict, f, indent=2)
        
        print(f"\nBenchmark report saved to: {output_file}")

    def save_baseline(self, report: BenchmarkReport, baseline_file: Optional[str] = None):
        """Save a baseline file for future regression comparisons."""
        output_dir = self.config['output_path']
        os.makedirs(output_dir, exist_ok=True)
        baseline_path = baseline_file or os.path.join(output_dir, 'performance-baseline.json')
        with open(baseline_path, 'w', encoding='utf-8') as f:
            json.dump(asdict(report), f, indent=2)
        print(f"Baseline saved to: {baseline_path}")

    def compare_with_baseline(self, report: BenchmarkReport, baseline_file: Optional[str] = None) -> Dict[str, Any]:
        """Compare current report with stored baseline and detect regressions."""
        baseline_path = baseline_file or os.path.join(self.config['output_path'], 'performance-baseline.json')
        if not os.path.exists(baseline_path):
            return {'status': 'no_baseline', 'message': f'Baseline not found at {baseline_path}'}
        try:
            with open(baseline_path, 'r', encoding='utf-8') as f:
                baseline = json.load(f)
        except Exception as e:
            return {'status': 'error', 'message': f'Failed to read baseline: {e}'}

        thresholds = self.config.get('regression_thresholds', {})
        max_dur_pct = thresholds.get('max_duration_regression_pct', 15.0)
        max_mem_pct = thresholds.get('max_memory_regression_pct', 10.0)

        # Index summaries by (config_name, test_path)
        def index_summaries(summaries: List[Dict[str, Any]]) -> Dict[str, Dict[str, Any]]:
            idx: Dict[str, Dict[str, Any]] = {}
            for s in summaries:
                key = f"{s['config_name']}::{s['test_path']}"
                idx[key] = s
            return idx

        current_idx = index_summaries([asdict(s) for s in report.benchmark_summaries])
        baseline_idx = index_summaries(baseline.get('benchmark_summaries', []))

        regressions: List[Dict[str, Any]] = []

        for key, base in baseline_idx.items():
            curr = current_idx.get(key)
            if not curr:
                continue
            # Duration regression
            bdur = max(float(base.get('mean_duration', 0.0)), 0.0)
            cdur = max(float(curr.get('mean_duration', 0.0)), 0.0)
            dur_pct = ((cdur - bdur) / bdur * 100.0) if bdur > 0 else 0.0

            # Memory regression (peak)
            bmem = max(float(base.get('mean_memory_peak', 0.0)), 0.0)
            cmem = max(float(curr.get('mean_memory_peak', 0.0)), 0.0)
            mem_pct = ((cmem - bmem) / bmem * 100.0) if bmem > 0 else 0.0

            reg_flags = []
            if dur_pct > max_dur_pct:
                reg_flags.append('duration')
            if mem_pct > max_mem_pct:
                reg_flags.append('memory')
            if reg_flags:
                config_name, test_path = key.split('::', 1)
                regressions.append({
                    'config_name': config_name,
                    'test_path': test_path,
                    'duration_baseline': bdur,
                    'duration_current': cdur,
                    'duration_regression_pct': dur_pct,
                    'memory_baseline': bmem,
                    'memory_current': cmem,
                    'memory_regression_pct': mem_pct,
                    'dimensions': reg_flags
                })

        status = 'regressions_found' if regressions else 'no_regressions'
        return {
            'status': status,
            'thresholds': {'duration_pct': max_dur_pct, 'memory_pct': max_mem_pct},
            'regressions': regressions,
            'baseline_path': baseline_path
        }
    
    def generate_html_report(self, report: BenchmarkReport, output_file: str):
        """Generate HTML benchmark report"""
        html_content = self._generate_html_content(report)
        
        with open(output_file, 'w') as f:
            f.write(html_content)
        
        print(f"HTML benchmark report generated: {output_file}")

    def export_csv(self, report: BenchmarkReport, output_file: str):
        """Export benchmark summaries to CSV"""
        import csv
        rows = []
        for s in report.benchmark_summaries:
            rows.append({
                'config_name': s.config_name,
                'test_path': s.test_path,
                'scenario_name': s.scenario_name,
                'iterations': s.iterations,
                'mean_duration': f"{s.mean_duration:.6f}",
                'median_duration': f"{s.median_duration:.6f}",
                'std_duration': f"{s.std_duration:.6f}",
                'min_duration': f"{s.min_duration:.6f}",
                'max_duration': f"{s.max_duration:.6f}",
                'mean_memory_peak': f"{s.mean_memory_peak:.3f}",
                'mean_memory_final': f"{s.mean_memory_final:.3f}",
                'mean_cpu_percent': f"{s.mean_cpu_percent:.2f}",
                'mean_findings_count': f"{s.mean_findings_count:.2f}",
                'success_rate': f"{s.success_rate:.4f}",
                'total_duration': f"{s.total_duration:.6f}"
            })
        fieldnames = list(rows[0].keys()) if rows else [
            'config_name','test_path','scenario_name','iterations','mean_duration','median_duration','std_duration','min_duration','max_duration','mean_memory_peak','mean_memory_final','mean_cpu_percent','mean_findings_count','success_rate','total_duration']
        with open(output_file, 'w', newline='', encoding='utf-8') as f:
            writer = csv.DictWriter(f, fieldnames=fieldnames)
            writer.writeheader()
            for r in rows:
                writer.writerow(r)
        print(f"CSV benchmark report generated: {output_file}")

    def export_markdown(self, report: BenchmarkReport, output_file: str):
        """Export a concise Markdown summary report"""
        lines = []
        lines.append(f"# Performance Benchmark Report\n")
        lines.append(f"- Timestamp: {report.timestamp}\n")
        lines.append(f"- Duration: {report.duration:.2f} seconds\n")
        lines.append(f"- Total Benchmarks: {report.total_benchmarks}\n")
        lines.append(f"- Success Rate: {((report.successful_benchmarks / report.total_benchmarks) * 100 if report.total_benchmarks else 0):.1f}%\n")
        lines.append("\n## Top Fastest Configurations\n")
        for item in report.performance_rankings.get('fastest_configs', [])[:5]:
            lines.append(f"- {item['config']} on {os.path.basename(item['test_path'])}: {item['duration']:.2f}s\n")
        lines.append("\n## Most Memory Efficient\n")
        for item in report.performance_rankings.get('most_memory_efficient', [])[:5]:
            lines.append(f"- {item['config']} on {os.path.basename(item['test_path'])}: {item['memory']:.1f}MB\n")
        lines.append("\n## Summary Table\n")
        lines.append("| Config | Test Path | Iter | Mean Time (s) | Peak Mem (MB) | CPU (%) | Findings |\n")
        lines.append("|---|---|---:|---:|---:|---:|---:|\n")
        for s in report.benchmark_summaries:
            lines.append(f"| {s.config_name} | {s.scenario_name} | {s.iterations} | {s.mean_duration:.2f} | {s.mean_memory_peak:.1f} | {s.mean_cpu_percent:.1f} | {s.mean_findings_count:.1f} |\n")
        with open(output_file, 'w', encoding='utf-8') as f:
            f.writelines(lines)
        print(f"Markdown benchmark report generated: {output_file}")
    
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
        <h3>{summary.config_name} - {summary.scenario_name}</h3>
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
    parser.add_argument('--no-cache', action='store_true', help='Disable benchmark result caching')
    parser.add_argument('--csv', action='store_true', help='Export CSV report alongside JSON')
    parser.add_argument('--md', action='store_true', help='Export Markdown report alongside JSON')
    parser.add_argument('--baseline', action='store_true', help='Save current run as performance baseline and exit')
    parser.add_argument('--compare', action='store_true', help='Compare current run against saved baseline and fail on regression')
    parser.add_argument('--baseline-file', help='Custom baseline file path for save/compare')
    
    args = parser.parse_args()
    
    # Initialize benchmarker
    benchmarker = PerformanceBenchmarker(args.config, use_cache=(not args.no_cache))
    
    # Override config with command line arguments
    if args.rules:
        benchmarker.config['rules_path'] = args.rules
    if args.tests:
        benchmarker.config['tests_path'] = args.tests
    if args.iterations is not None:
        benchmarker.config['iterations'] = args.iterations
    if args.warmup is not None:
        benchmarker.config['warmup_runs'] = args.warmup
    
    # Run benchmarks
    report = benchmarker.run_all_benchmarks()
    
    # Output results
    output_file = args.output or 'benchmark-results/performance-benchmark-report.json'
    
    # Always save JSON report to the specified output path
    benchmarker.save_report(report, output_file)
    
    if args.html:
        html_file = output_file.replace('.json', '.html')
        benchmarker.generate_html_report(report, html_file)
    if args.csv:
        csv_file = output_file.replace('.json', '.csv')
        benchmarker.export_csv(report, csv_file)
    if args.md:
        md_file = output_file.replace('.json', '.md')
        benchmarker.export_markdown(report, md_file)
    
    # Baseline handling
    if args.baseline:
        benchmarker.save_baseline(report, args.baseline_file)
        print("Baseline saved. Exiting as requested.")
        sys.exit(0)

    if args.compare:
        comparison = benchmarker.compare_with_baseline(report, args.baseline_file)
        if comparison.get('status') == 'no_baseline':
            print(f"No baseline found at {comparison.get('baseline_path', args.baseline_file)}")
            sys.exit(2)
        if comparison.get('status') == 'error':
            print(f"Error comparing with baseline: {comparison.get('message')}")
            sys.exit(2)
        regressions = comparison.get('regressions', [])
        if regressions:
            print("\nPerformance regressions detected:")
            for r in regressions:
                print(f"  {r['config_name']} on {r['test_path']}: "
                      f"duration +{r['duration_regression_pct']:.1f}%, memory +{r['memory_regression_pct']:.1f}%")
            sys.exit(1)
        else:
            print("No performance regressions detected.")

    # Print summary
    print(f"\nBenchmark Summary:")
    print(f"Total Benchmarks: {report.total_benchmarks}")
    print(f"Successful: {report.successful_benchmarks}")
    print(f"Failed: {report.failed_benchmarks}")
    success_rate = (report.successful_benchmarks / report.total_benchmarks * 100) if report.total_benchmarks else 0.0
    print(f"Success Rate: {success_rate:.1f}%")
    print(f"Duration: {report.duration:.2f} seconds")
    
    # Print top performers
    print(f"\nTop Performers:")
    for item in report.performance_rankings['fastest_configs'][:3]:
        print(f"  {item['config']}: {item['duration']:.2f}s")

if __name__ == '__main__':
    main()
