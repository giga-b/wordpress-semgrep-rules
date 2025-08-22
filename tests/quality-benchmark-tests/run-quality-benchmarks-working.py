#!/usr/bin/env python3
"""
Quality Benchmark Testing for WordPress Semgrep Rules
Modified version that uses working rules for accurate testing
"""

import os
import sys
import json
import subprocess
import time
from datetime import datetime
from pathlib import Path

def run_semgrep_scan(config_file, test_files, project_root):
    """Run Semgrep scan with specific configuration"""
    try:
        # Build the command
        cmd = [
            'semgrep',
            '--config', config_file,
            '--json',
            '--quiet'
        ] + test_files
        
        print(f"Running Semgrep with config: {os.path.basename(config_file)}")
        print(f"Command: {' '.join(cmd)}")
        
        # Run the scan
        start_time = time.time()
        result = subprocess.run(cmd, capture_output=True, text=True, cwd=project_root)
        scan_time = time.time() - start_time
        
        if result.returncode == 0:
            # Parse results
            try:
                scan_data = json.loads(result.stdout)
                findings = scan_data.get('results', [])
                
                print(f"Scan completed in {scan_time:.2f} seconds")
                print(f"Total findings: {len(findings)}")
                
                # Count by vulnerability type
                vuln_counts = {}
                for finding in findings:
                    check_id = finding.get('check_id', 'unknown')
                    vuln_type = check_id.split('.')[1] if '.' in check_id else 'unknown'
                    vuln_counts[vuln_type] = vuln_counts.get(vuln_type, 0) + 1
                
                print("Vulnerability breakdown:")
                for vuln_type, count in vuln_counts.items():
                    print(f"  {vuln_type}: {count}")
                
                return {
                    'success': True,
                    'findings': findings,
                    'scan_time': scan_time,
                    'vuln_counts': vuln_counts
                }
                
            except json.JSONDecodeError:
                print("Failed to parse JSON output")
                return {'success': False, 'error': 'JSON parse error'}
        else:
            print(f"Analysis failed: Semgrep failed with return code {result.returncode}")
            return {'success': False, 'error': f'Semgrep failed: {result.returncode}'}
            
    except Exception as e:
        print(f"Error running scan: {e}")
        return {'success': False, 'error': str(e)}

def main():
    if len(sys.argv) != 2:
        print("Usage: python run-quality-benchmarks-working.py <project_root>")
        sys.exit(1)
    
    project_root = sys.argv[1]
    
    # Test files directory
    test_dir = os.path.join(project_root, 'tests', 'quality-benchmark-tests')
    
    # Find all PHP test files
    test_files = list(Path(test_dir).glob('*.php'))
    print(f"Found {len(test_files)} test files")
    
    # Our working rules configuration
    working_rules = os.path.join(test_dir, 'working-rules.yaml')
    
    if not os.path.exists(working_rules):
        print(f"Working rules file not found: {working_rules}")
        sys.exit(1)
    
    print("\n" + "="*60)
    print("Testing with Working Rules Configuration")
    print("="*60)
    
    # Run scan with working rules
    results = run_semgrep_scan(working_rules, [str(f) for f in test_files], project_root)
    
    if results['success']:
        total_findings = len(results['findings'])
        print(f"\n{'='*60}")
        print("QUALITY BENCHMARK RESULTS")
        print(f"{'='*60}")
        print(f"Total vulnerabilities detected: {total_findings}")
        print(f"Scan time: {results['scan_time']:.2f} seconds")
        print(f"Files scanned: {len(test_files)}")
        
        # Calculate detection rate (assuming our test files have vulnerabilities)
        # Based on our test file registry, we expect around 255 vulnerabilities
        expected_vulnerabilities = 255
        detection_rate = (total_findings / expected_vulnerabilities) * 100 if expected_vulnerabilities > 0 else 0
        
        print(f"Detection rate: {detection_rate:.1f}% ({total_findings}/{expected_vulnerabilities})")
        
        # Quality assessment
        if detection_rate >= 90:
            quality_status = "EXCELLENT"
        elif detection_rate >= 80:
            quality_status = "GOOD"
        elif detection_rate >= 70:
            quality_status = "FAIR"
        else:
            quality_status = "NEEDS IMPROVEMENT"
        
        print(f"Quality status: {quality_status}")
        
        # Save detailed results
        timestamp = int(datetime.now().timestamp())
        results_file = os.path.join(test_dir, 'results', f'working-rules-results-{timestamp}.json')
        report_file = os.path.join(test_dir, 'results', f'working-rules-report-{timestamp}.md')
        
        os.makedirs(os.path.dirname(results_file), exist_ok=True)
        
        with open(results_file, 'w') as f:
            json.dump(results, f, indent=2)
        
        # Generate markdown report
        with open(report_file, 'w') as f:
            f.write(f"# Working Rules Quality Benchmark Report\n\n")
            f.write(f"**Generated**: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n\n")
            f.write(f"## Summary\n\n")
            f.write(f"- **Total vulnerabilities detected**: {total_findings}\n")
            f.write(f"- **Expected vulnerabilities**: {expected_vulnerabilities}\n")
            f.write(f"- **Detection rate**: {detection_rate:.1f}%\n")
            f.write(f"- **Quality status**: {quality_status}\n")
            f.write(f"- **Scan time**: {results['scan_time']:.2f} seconds\n")
            f.write(f"- **Files scanned**: {len(test_files)}\n\n")
            
            f.write(f"## Vulnerability Breakdown\n\n")
            for vuln_type, count in results['vuln_counts'].items():
                f.write(f"- **{vuln_type}**: {count}\n")
            
            f.write(f"\n## Detailed Findings\n\n")
            for finding in results['findings']:
                f.write(f"### {finding['check_id']}\n")
                f.write(f"- **File**: {finding['path']}\n")
                f.write(f"- **Line**: {finding['start']['line']}\n")
                f.write(f"- **Message**: {finding['extra']['message']}\n")
                f.write(f"- **Severity**: {finding['extra']['severity']}\n\n")
        
        print(f"\nDetailed results saved to: {results_file}")
        print(f"Report saved to: {report_file}")
        
    else:
        print(f"Scan failed: {results.get('error', 'Unknown error')}")
        sys.exit(1)

if __name__ == "__main__":
    main()
