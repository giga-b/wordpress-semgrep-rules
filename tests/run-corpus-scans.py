#!/usr/bin/env python3
"""
Corpus Scanning Script for WordPress Semgrep Rules
Runs comprehensive scans against the attack corpus to validate rule effectiveness.
"""

import json
import yaml
import subprocess
import sys
import os
from pathlib import Path
from typing import Dict, List, Tuple, Optional
import argparse
import time
from datetime import datetime
import concurrent.futures
import statistics

class CorpusScanner:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.corpus_path = self.project_root / "corpus"
        self.results_dir = self.project_root / "results" / "corpus-scans"
        self.results_dir.mkdir(parents=True, exist_ok=True)
        
        # Load quality configuration
        self.quality_config = self.project_root / ".rule-quality.yml"
        if self.quality_config.exists():
            with open(self.quality_config, 'r', encoding='utf-8') as f:
                self.config = yaml.safe_load(f)
        else:
            self.config = {}

        # Perf utilities
        try:
            from tests._lib.perf import start_perf, stop_perf
        except Exception:
            # Fallback path adjust
            from pathlib import Path as _P
            import sys as _sys
            _sys.path.append(str((_P(__file__).parent / '_lib').resolve()))
            from perf import start_perf, stop_perf
        self._start_perf = start_perf
        self._stop_perf = stop_perf
    
    def get_rule_files(self) -> List[Path]:
        """Get all rule files to scan."""
        rule_files = []
        
        # Scan all rule packs
        rule_packs = ['packs/wp-core-security', 'packs/wp-core-quality', 'packs/experimental']
        
        for pack in rule_packs:
            pack_path = self.project_root / pack
            if pack_path.exists():
                rule_files.extend(pack_path.glob('*.yaml'))
        
        return rule_files
    
    def extract_rule_metadata(self, rule_file: Path) -> Dict:
        """Extract metadata from a rule file."""
        try:
            with open(rule_file, 'r', encoding='utf-8') as f:
                content = yaml.safe_load(f)
            
            if not content or 'rules' not in content:
                return {
                    'id': rule_file.stem,
                    'vuln_class': 'other',
                    'confidence': 'medium'
                }
            
            rule = content['rules'][0]
            metadata = rule.get('metadata', {})
            
            return {
                'id': rule.get('id', rule_file.stem),
                'message': rule.get('message', ''),
                'severity': rule.get('severity', ''),
                'confidence': metadata.get('confidence', 'medium'),
                'cwe': metadata.get('cwe', ''),
                'category': metadata.get('category', ''),
                'vuln_class': metadata.get('vuln_class', 'other'),
                'tags': metadata.get('tags', [])
            }
        except Exception as e:
            return {
                'id': rule_file.stem,
                'error': str(e),
                'vuln_class': 'other',
                'confidence': 'medium'
            }
    
    def scan_rule_against_corpus(self, rule_file: Path) -> Dict:
        """Scan a single rule against the corpus."""
        metadata = self.extract_rule_metadata(rule_file)
        
        result = {
            'rule_file': str(rule_file),
            'rule_id': metadata.get('id', ''),
            'vuln_class': metadata.get('vuln_class', 'other'),
            'confidence': metadata.get('confidence', 'medium'),
            'success': False,
            'findings_count': 0,
            'findings': [],
            'scan_time': 0,
            'error': None,
            'timestamp': datetime.now().isoformat()
        }
        
        try:
            perf_t = self._start_perf()
            
            # Run Semgrep scan
            cmd = [
                'semgrep',
                '--config', str(rule_file),
                '--json',
                '--quiet',
                '--no-git-ignore',
                str(self.corpus_path)
            ]
            
            process = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                cwd=self.project_root,
                timeout=300,  # 5 minute timeout
                encoding='utf-8',
                errors='replace'
            )
            
            perf = self._stop_perf(perf_t)
            result['scan_time'] = perf.get('wall_time_seconds', 0)
            result['memory_usage'] = perf.get('process_rss_delta_bytes', 0)
            result['cpu_time'] = perf.get('process_cpu_time_seconds', 0.0)
            result['cpu_utilization'] = perf.get('cpu_utilization_estimate', 0.0)
            
            if process.returncode == 0:
                try:
                    findings = json.loads(process.stdout)
                    result['success'] = True
                    result['findings_count'] = len(findings.get('results', []))
                    result['findings'] = findings.get('results', [])
                except json.JSONDecodeError as e:
                    result['error'] = f"Failed to parse Semgrep output: {e}"
            else:
                result['error'] = process.stderr or "Semgrep scan failed"
        
        except subprocess.TimeoutExpired:
            result['error'] = "Scan timed out after 5 minutes"
        except Exception as e:
            result['error'] = str(e)
        
        return result
    
    def scan_rule_parallel(self, rule_file: Path) -> Dict:
        """Scan a rule with progress reporting."""
        print(f"  Scanning {rule_file.name}...")
        result = self.scan_rule_against_corpus(rule_file)
        
        if result['success']:
            print(f"    OK {rule_file.name}: {result['findings_count']} findings ({result['scan_time']:.2f}s)")
        else:
            print(f"    FAIL {rule_file.name}: {result['error']}")
        
        return result
    
    def run_corpus_scans(self, max_workers: int = 4) -> Dict:
        """Run comprehensive corpus scans."""
        rule_files = self.get_rule_files()
        
        if not rule_files:
            return {
                'success': False,
                'error': 'No rule files found',
                'scans': [],
                'summary': {}
            }
        
        print(f"Found {len(rule_files)} rule files to scan")
        print(f"Corpus path: {self.corpus_path}")
        print(f"Using {max_workers} parallel workers")
        
        # Run scans in parallel
        scan_results = []
        
        with concurrent.futures.ThreadPoolExecutor(max_workers=max_workers) as executor:
            future_to_rule = {
                executor.submit(self.scan_rule_parallel, rule_file): rule_file
                for rule_file in rule_files
            }
            
            for future in concurrent.futures.as_completed(future_to_rule):
                try:
                    result = future.result()
                    scan_results.append(result)
                except Exception as e:
                    rule_file = future_to_rule[future]
                    scan_results.append({
                        'rule_file': str(rule_file),
                        'success': False,
                        'error': str(e),
                        'findings_count': 0,
                        'scan_time': 0
                    })
        
        # Generate summary
        summary = self.generate_scan_summary(scan_results)
        
        return {
            'success': True,
            'scans': scan_results,
            'summary': summary,
            'timestamp': datetime.now().isoformat()
        }
    
    def generate_scan_summary(self, scan_results: List[Dict]) -> Dict:
        """Generate summary statistics from scan results."""
        successful_scans = [r for r in scan_results if r['success']]
        failed_scans = [r for r in scan_results if not r['success']]
        
        # Calculate statistics
        total_findings = sum(r['findings_count'] for r in successful_scans)
        scan_times = [r['scan_time'] for r in successful_scans]
        
        # Group by vulnerability class
        vuln_class_stats = {}
        for result in successful_scans:
            vuln_class = result.get('vuln_class', 'other')
            if vuln_class not in vuln_class_stats:
                vuln_class_stats[vuln_class] = {
                    'count': 0,
                    'findings': 0,
                    'avg_scan_time': 0
                }
            
            vuln_class_stats[vuln_class]['count'] += 1
            vuln_class_stats[vuln_class]['findings'] += result['findings_count']
        
        # Calculate averages
        for vuln_class in vuln_class_stats:
            class_scans = [r for r in successful_scans if r.get('vuln_class') == vuln_class]
            if class_scans:
                vuln_class_stats[vuln_class]['avg_scan_time'] = statistics.mean(
                    r['scan_time'] for r in class_scans
                )
        
        return {
            'total_rules': len(scan_results),
            'successful_scans': len(successful_scans),
            'failed_scans': len(failed_scans),
            'success_rate': len(successful_scans) / len(scan_results) if scan_results else 0,
            'total_findings': total_findings,
            'avg_findings_per_rule': total_findings / len(successful_scans) if successful_scans else 0,
            'avg_scan_time': statistics.mean(scan_times) if scan_times else 0,
            'min_scan_time': min(scan_times) if scan_times else 0,
            'max_scan_time': max(scan_times) if scan_times else 0,
            'vuln_class_stats': vuln_class_stats,
            'top_finding_rules': sorted(
                successful_scans,
                key=lambda x: x['findings_count'],
                reverse=True
            )[:10]
        }
    
    def save_results(self, results: Dict):
        """Save scan results to file."""
        timestamp = int(time.time())
        results_file = self.results_dir / f"corpus-scans-{timestamp}.json"
        
        with open(results_file, 'w', encoding='utf-8') as f:
            json.dump(results, f, indent=2)
        
        print(f"\nResults saved to: {results_file}")
        return results_file
    
    def print_summary(self, results: Dict):
        """Print a formatted summary of scan results."""
        if not results['success']:
            print(f"❌ Corpus scans failed: {results['error']}")
            return
        
        summary = results['summary']
        
        print("\n" + "="*80)
        print("CORPUS SCAN SUMMARY")
        print("="*80)
        
        print(f"\nOverall Results:")
        print(f"  Total Rules Scanned: {summary['total_rules']}")
        print(f"  Successful Scans: {summary['successful_scans']}")
        print(f"  Failed Scans: {summary['failed_scans']}")
        print(f"  Success Rate: {summary['success_rate']:.1%}")
        
        print(f"\nFindings Summary:")
        print(f"  Total Findings: {summary['total_findings']}")
        print(f"  Average Findings per Rule: {summary['avg_findings_per_rule']:.1f}")
        
        print(f"\nPerformance Summary:")
        print(f"  Average Scan Time: {summary['avg_scan_time']:.2f} seconds")
        print(f"  Min Scan Time: {summary['min_scan_time']:.2f} seconds")
        print(f"  Max Scan Time: {summary['max_scan_time']:.2f} seconds")
        
        print(f"\nVulnerability Class Breakdown:")
        for vuln_class, stats in summary['vuln_class_stats'].items():
            print(f"  {vuln_class.upper()}:")
            print(f"    Rules: {stats['count']}")
            print(f"    Findings: {stats['findings']}")
            print(f"    Avg Scan Time: {stats['avg_scan_time']:.2f}s")
        
        print(f"\nTop Finding Rules:")
        for i, rule in enumerate(summary['top_finding_rules'][:5], 1):
            print(f"  {i}. {rule['rule_id']}: {rule['findings_count']} findings")
        
        print("\n" + "="*80)

def main():
    parser = argparse.ArgumentParser(description='Run corpus scans for WordPress Semgrep Rules')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--workers', type=int, default=4, help='Number of parallel workers')
    parser.add_argument('--output', help='Output file for results')
    
    args = parser.parse_args()
    
    # Initialize scanner
    scanner = CorpusScanner(args.project_root)
    
    # Run scans
    results = scanner.run_corpus_scans(max_workers=args.workers)
    
    # Save results
    if results['success']:
        scanner.save_results(results)
    
    # Print summary
    scanner.print_summary(results)
    
    # Exit with appropriate code
    if results['success'] and results['summary']['success_rate'] > 0.8:
        print(f"\n✅ Corpus scans completed successfully!")
        sys.exit(0)
    else:
        print(f"\n❌ Corpus scans had issues!")
        sys.exit(1)

if __name__ == '__main__':
    main()
