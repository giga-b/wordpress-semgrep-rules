#!/usr/bin/env python3
"""
Baseline Scanning Pipeline for WordPress Semgrep Rules
Part of Task 1.3: Baseline Scanning Pipeline

This tool provides automated scanning of the plugin corpus to establish baseline results
for comparison and regression testing.
"""

import os
import sys
import json
import subprocess
import time
import multiprocessing
from pathlib import Path
from typing import Dict, List, Optional, Tuple
from dataclasses import dataclass
from datetime import datetime
import logging

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

@dataclass
class ScanResult:
    """Represents the result of scanning a single plugin"""
    plugin_slug: str
    scan_time: float
    findings_count: int
    findings: List[Dict]
    error_message: Optional[str] = None
    memory_usage: float = 0.0

@dataclass
class BaselineResult:
    """Represents baseline scanning results"""
    scan_date: str
    total_plugins: int
    successful_scans: int
    failed_scans: int
    total_findings: int
    total_scan_time: float
    average_scan_time: float
    results: Dict[str, ScanResult]

class BaselineScanner:
    """Manages baseline scanning of WordPress plugin corpus"""
    
    def __init__(self, corpus_path: str = "corpus/wordpress-plugins", 
                 results_path: str = "results/baseline"):
        self.corpus_path = Path(corpus_path)
        self.results_path = Path(results_path)
        self.results_path.mkdir(parents=True, exist_ok=True)
        self.baseline_file = self.results_path / "baseline-results.json"
        
    def scan_single_plugin(self, plugin_path: Path, config_path: str) -> ScanResult:
        """Scan a single plugin with Semgrep"""
        start_time = time.time()
        
        try:
            # Run Semgrep scan
            cmd = [
                "semgrep",
                "--json",
                "--config", config_path,
                str(plugin_path)
            ]
            
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                timeout=300  # 5 minutes timeout per plugin
            )
            
            scan_time = time.time() - start_time
            
            # Parse results
            findings = []
            if result.stdout.strip():
                try:
                    semgrep_results = json.loads(result.stdout)
                    findings = semgrep_results.get('results', [])
                except json.JSONDecodeError:
                    logger.error(f"Failed to parse Semgrep results for {plugin_path.name}")
            
            return ScanResult(
                plugin_slug=plugin_path.name,
                scan_time=scan_time,
                findings_count=len(findings),
                findings=findings
            )
            
        except subprocess.TimeoutExpired:
            return ScanResult(
                plugin_slug=plugin_path.name,
                scan_time=300,
                findings_count=0,
                findings=[],
                error_message="Scan timeout"
            )
        except Exception as e:
            return ScanResult(
                plugin_slug=plugin_path.name,
                scan_time=time.time() - start_time,
                findings_count=0,
                findings=[],
                error_message=str(e)
            )
    
    def scan_plugin_worker(self, args: Tuple[Path, str]) -> ScanResult:
        """Worker function for parallel scanning"""
        plugin_path, config_path = args
        return self.scan_single_plugin(plugin_path, config_path)
    
    def run_baseline_scan(self, config_path: str = "configs/ci-blocking.yaml", 
                         max_workers: int = None) -> BaselineResult:
        """Run baseline scan on the entire corpus"""
        logger.info("Starting baseline scan of plugin corpus")
        
        if not self.corpus_path.exists():
            logger.error(f"Corpus path does not exist: {self.corpus_path}")
            return None
        
        # Get all plugin paths
        plugin_paths = []
        for item in self.corpus_path.iterdir():
            if item.is_dir() and item.name != "__pycache__":
                plugin_paths.append(item)
        
        if not plugin_paths:
            logger.error("No plugins found in corpus")
            return None
        
        logger.info(f"Found {len(plugin_paths)} plugins to scan")
        
        # Set up parallel processing
        if max_workers is None:
            max_workers = min(multiprocessing.cpu_count(), 8)  # Cap at 8 workers
        
        logger.info(f"Using {max_workers} workers for parallel scanning")
        
        # Prepare scan arguments
        scan_args = [(plugin_path, config_path) for plugin_path in plugin_paths]
        
        # Run parallel scans
        start_time = time.time()
        results = {}
        
        with multiprocessing.Pool(max_workers) as pool:
            scan_results = pool.map(self.scan_plugin_worker, scan_args)
        
        total_scan_time = time.time() - start_time
        
        # Process results
        successful_scans = 0
        failed_scans = 0
        total_findings = 0
        
        for result in scan_results:
            results[result.plugin_slug] = result
            
            if result.error_message:
                failed_scans += 1
                logger.warning(f"Scan failed for {result.plugin_slug}: {result.error_message}")
            else:
                successful_scans += 1
                total_findings += result.findings_count
                
                if result.findings_count > 0:
                    logger.info(f"Found {result.findings_count} issues in {result.plugin_slug}")
        
        # Calculate averages
        average_scan_time = total_scan_time / len(plugin_paths) if plugin_paths else 0
        
        # Create baseline result
        baseline_result = BaselineResult(
            scan_date=datetime.now().isoformat(),
            total_plugins=len(plugin_paths),
            successful_scans=successful_scans,
            failed_scans=failed_scans,
            total_findings=total_findings,
            total_scan_time=total_scan_time,
            average_scan_time=average_scan_time,
            results={slug: result for slug, result in results.items()}
        )
        
        logger.info("Baseline scan complete:")
        logger.info(f"  Total plugins: {baseline_result.total_plugins}")
        logger.info(f"  Successful scans: {baseline_result.successful_scans}")
        logger.info(f"  Failed scans: {baseline_result.failed_scans}")
        logger.info(f"  Total findings: {baseline_result.total_findings}")
        logger.info(f"  Total scan time: {baseline_result.total_scan_time:.2f}s")
        logger.info(f"  Average scan time: {baseline_result.average_scan_time:.2f}s")
        
        return baseline_result
    
    def save_baseline_results(self, baseline_result: BaselineResult):
        """Save baseline results to file"""
        # Convert to JSON-serializable format
        json_data = {
            "scan_date": baseline_result.scan_date,
            "total_plugins": baseline_result.total_plugins,
            "successful_scans": baseline_result.successful_scans,
            "failed_scans": baseline_result.failed_scans,
            "total_findings": baseline_result.total_findings,
            "total_scan_time": baseline_result.total_scan_time,
            "average_scan_time": baseline_result.average_scan_time,
            "results": {}
        }
        
        for slug, result in baseline_result.results.items():
            json_data["results"][slug] = {
                "plugin_slug": result.plugin_slug,
                "scan_time": result.scan_time,
                "findings_count": result.findings_count,
                "findings": result.findings,
                "error_message": result.error_message,
                "memory_usage": result.memory_usage
            }
        
        # Save to file
        with open(self.baseline_file, 'w') as f:
            json.dump(json_data, f, indent=2)
        
        logger.info(f"Baseline results saved to: {self.baseline_file}")
        
        # Also save a summary report
        self.generate_summary_report(baseline_result)
    
    def generate_summary_report(self, baseline_result: BaselineResult):
        """Generate a human-readable summary report"""
        report_file = self.results_path / "baseline-summary.md"
        
        report = f"""# Baseline Scan Summary Report
Generated: {baseline_result.scan_date}

## Overview
- **Total Plugins Scanned**: {baseline_result.total_plugins}
- **Successful Scans**: {baseline_result.successful_scans}
- **Failed Scans**: {baseline_result.failed_scans}
- **Total Findings**: {baseline_result.total_findings}
- **Total Scan Time**: {baseline_result.total_scan_time:.2f} seconds
- **Average Scan Time**: {baseline_result.average_scan_time:.2f} seconds per plugin

## Performance Metrics
- **Scan Success Rate**: {(baseline_result.successful_scans / baseline_result.total_plugins * 100):.1f}%
- **Findings per Plugin**: {(baseline_result.total_findings / baseline_result.successful_scans):.2f} (average)
- **Scan Throughput**: {(baseline_result.total_plugins / baseline_result.total_scan_time * 60):.1f} plugins/minute

## Top Plugins by Findings
"""
        
        # Sort plugins by findings count
        sorted_plugins = sorted(
            baseline_result.results.items(),
            key=lambda x: x[1].findings_count,
            reverse=True
        )
        
        for i, (slug, result) in enumerate(sorted_plugins[:10], 1):
            if result.findings_count > 0:
                report += f"{i}. **{slug}**: {result.findings_count} findings\n"
        
        report += "\n## Failed Scans\n"
        failed_plugins = [slug for slug, result in baseline_result.results.items() 
                         if result.error_message]
        
        if failed_plugins:
            for slug in failed_plugins:
                result = baseline_result.results[slug]
                report += f"- **{slug}**: {result.error_message}\n"
        else:
            report += "No failed scans.\n"
        
        # Save report
        with open(report_file, 'w') as f:
            f.write(report)
        
        logger.info(f"Summary report saved to: {report_file}")
    
    def compare_with_baseline(self, new_results: BaselineResult) -> Dict:
        """Compare new scan results with baseline"""
        if not self.baseline_file.exists():
            logger.error("No baseline file found for comparison")
            return {}
        
        # Load baseline
        with open(self.baseline_file, 'r') as f:
            baseline_data = json.load(f)
        
        comparison = {
            "baseline_date": baseline_data["scan_date"],
            "new_scan_date": new_results.scan_date,
            "changes": {
                "total_findings": new_results.total_findings - baseline_data["total_findings"],
                "successful_scans": new_results.successful_scans - baseline_data["successful_scans"],
                "average_scan_time": new_results.average_scan_time - baseline_data["average_scan_time"]
            },
            "regressions": [],
            "improvements": []
        }
        
        # Compare individual plugin results
        baseline_results = baseline_data["results"]
        
        for slug, new_result in new_results.results.items():
            if slug in baseline_results:
                baseline_result = baseline_results[slug]
                baseline_findings = baseline_result["findings_count"]
                new_findings = new_result.findings_count
                
                if new_findings > baseline_findings:
                    comparison["regressions"].append({
                        "plugin": slug,
                        "baseline_findings": baseline_findings,
                        "new_findings": new_findings,
                        "increase": new_findings - baseline_findings
                    })
                elif new_findings < baseline_findings:
                    comparison["improvements"].append({
                        "plugin": slug,
                        "baseline_findings": baseline_findings,
                        "new_findings": new_findings,
                        "decrease": baseline_findings - new_findings
                    })
        
        return comparison

def main():
    """Main entry point for the baseline scanner"""
    import argparse
    
    parser = argparse.ArgumentParser(description='WordPress Plugin Baseline Scanner')
    parser.add_argument('--config', default='configs/ci-blocking.yaml',
                       help='Semgrep configuration file to use')
    parser.add_argument('--corpus-path', default='corpus/wordpress-plugins',
                       help='Path to plugin corpus')
    parser.add_argument('--results-path', default='results/baseline',
                       help='Path to save results')
    parser.add_argument('--workers', type=int, default=None,
                       help='Number of worker processes')
    parser.add_argument('--compare', action='store_true',
                       help='Compare with existing baseline')
    
    args = parser.parse_args()
    
    scanner = BaselineScanner(args.corpus_path, args.results_path)
    
    # Run baseline scan
    baseline_result = scanner.run_baseline_scan(args.config, args.workers)
    
    if baseline_result:
        # Save results
        scanner.save_baseline_results(baseline_result)
        
        # Compare with existing baseline if requested
        if args.compare:
            comparison = scanner.compare_with_baseline(baseline_result)
            if comparison:
                print("\nComparison with Baseline:")
                print(f"  Baseline Date: {comparison['baseline_date']}")
                print(f"  New Scan Date: {comparison['new_scan_date']}")
                print(f"  Findings Change: {comparison['changes']['total_findings']:+d}")
                print(f"  Scan Time Change: {comparison['changes']['average_scan_time']:+.2f}s")
                print(f"  Regressions: {len(comparison['regressions'])}")
                print(f"  Improvements: {len(comparison['improvements'])}")
    else:
        logger.error("Baseline scan failed")
        sys.exit(1)

if __name__ == "__main__":
    main()
