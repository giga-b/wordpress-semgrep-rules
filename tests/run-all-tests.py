#!/usr/bin/env python3
"""
Master Test Runner for WordPress Semgrep Rules
Runs all testing components in the correct order with proper error handling.
"""

import json
import yaml
import subprocess
import sys
import os
from pathlib import Path
from typing import Dict, List, Optional
import argparse
import time
from datetime import datetime
import concurrent.futures

class MasterTestRunner:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.results_dir = self.project_root / "results"
        self.results_dir.mkdir(parents=True, exist_ok=True)
        
        # Test components and their execution order
        self.test_components = [
            {
                'name': 'quality-gates',
                'script': 'tests/quality-gates-working.py',
                'description': 'Quality Gates Validation',
                'required': True
            },
            {
                'name': 'rule-validation',
                'script': 'tests/validate-rule-metadata.py',
                'description': 'Rule Metadata Validation',
                'required': True
            },
            {
                'name': 'corpus-validation',
                'script': 'tests/validate-corpus.py',
                'description': 'Corpus Validation',
                'required': False
            },
            {
                'name': 'corpus-scans',
                'script': 'tests/run-corpus-scans.py',
                'description': 'Corpus Scanning',
                'required': False
            },
            {
                'name': 'security-review',
                'script': 'tests/security-review.py',
                'description': 'Security Review',
                'required': False
            },
            {
                'name': 'advanced-testing',
                'script': 'tests/advanced-testing-framework.py',
                'description': 'Advanced Testing Framework',
                'required': False
            },
            {
                'name': 'final-validation',
                'script': 'tests/final-validation.py',
                'description': 'Final Validation',
                'required': False
            },
            {
                'name': 'curated-eval',
                'script': 'tests/eval-new-files.py',
                'description': 'Curated Generic Evaluation',
                'required': True
            }
        ]
        
        # Report generation components
        self.report_components = [
            {
                'name': 'security-report',
                'script': 'tests/generate-security-report.py',
                'description': 'Security Report Generation',
                'required': False
            },
            {
                'name': 'final-report',
                'script': 'tests/generate-final-report.py',
                'description': 'Final Report Generation',
                'required': False
            }
        ]
    
    def run_component(self, component: Dict, args: List[str] = None) -> Dict:
        """Run a single test component."""
        script_path = self.project_root / component['script']
        
        if not script_path.exists():
            return {
                'name': component['name'],
                'success': False,
                'error': f"Script not found: {script_path}",
                'duration': 0
            }
        
        # Build command
        cmd = ['python', str(script_path), '--project-root', str(self.project_root)]
        if args:
            cmd.extend(args)
        
        result = {
            'name': component['name'],
            'description': component['description'],
            'script': str(script_path),
            'command': ' '.join(cmd),
            'success': False,
            'error': None,
            'duration': 0,
            'return_code': None
        }
        
        try:
            start_time = time.time()
            
            # Run the component
            # Ensure UTF-8 for child process stdout/stderr to avoid Windows cp1252 issues
            child_env = os.environ.copy()
            child_env.setdefault('PYTHONUTF8', '1')
            child_env.setdefault('PYTHONIOENCODING', 'utf-8')
            child_env.setdefault('LC_ALL', 'C.UTF-8')
            child_env.setdefault('LANG', 'C.UTF-8')

            process = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                cwd=self.project_root,
                timeout=1800,  # 30 minute timeout
                encoding='utf-8',
                errors='replace',
                env=child_env
            )
            
            end_time = time.time()
            result['duration'] = end_time - start_time
            result['return_code'] = process.returncode
            result['stdout'] = process.stdout
            result['stderr'] = process.stderr
            
            # Determine success
            result['success'] = process.returncode == 0
            
            if not result['success']:
                result['error'] = process.stderr or f"Process returned code {process.returncode}"
            
        except subprocess.TimeoutExpired:
            result['error'] = f"Component timed out after 30 minutes"
        except Exception as e:
            result['error'] = str(e)
        
        return result
    
    def run_components_sequential(self, components: List[Dict], args: List[str] = None) -> List[Dict]:
        """Run components sequentially in order."""
        results = []
        
        for i, component in enumerate(components, 1):
            print(f"\n[{i}/{len(components)}] Running {component['description']}...")
            print(f"Script: {component['script']}")
            
            result = self.run_component(component, args)
            results.append(result)
            
            if result['success']:
                print(f"✅ {component['description']} completed successfully ({result['duration']:.2f}s)")
            else:
                print(f"❌ {component['description']} failed: {result['error']}")
                
                # If this is a required component, we might want to stop
                if component.get('required', False):
                    print(f"⚠️ Required component failed - continuing with remaining components")
            
            # Small delay between components
            time.sleep(1)
        
        return results
    
    def run_components_parallel(self, components: List[Dict], max_workers: int = 2, args: List[str] = None) -> List[Dict]:
        """Run components in parallel (for non-dependent components)."""
        print(f"\nRunning {len(components)} components in parallel with {max_workers} workers...")
        
        results = []
        
        with concurrent.futures.ThreadPoolExecutor(max_workers=max_workers) as executor:
            future_to_component = {
                executor.submit(self.run_component, component, args): component
                for component in components
            }
            
            for future in concurrent.futures.as_completed(future_to_component):
                component = future_to_component[future]
                try:
                    result = future.result()
                    results.append(result)
                    
                    if result['success']:
                        print(f"✅ {component['description']} completed ({result['duration']:.2f}s)")
                    else:
                        print(f"❌ {component['description']} failed: {result['error']}")
                        
                except Exception as e:
                    component = future_to_component[future]
                    results.append({
                        'name': component['name'],
                        'description': component['description'],
                        'success': False,
                        'error': str(e),
                        'duration': 0
                    })
                    print(f"❌ {component['description']} failed with exception: {e}")
        
        return results
    
    def generate_summary_report(self, component_results: List[Dict], report_results: List[Dict]) -> Dict:
        """Generate a comprehensive summary report."""
        total_components = len(component_results)
        successful_components = [r for r in component_results if r['success']]
        failed_components = [r for r in component_results if not r['success']]
        
        total_reports = len(report_results)
        successful_reports = [r for r in report_results if r['success']]
        failed_reports = [r for r in report_results if not r['success']]
        
        # Calculate total execution time
        total_duration = sum(r['duration'] for r in component_results + report_results)
        
        # Group by component type
        required_components = [r for r in component_results if r.get('required', False)]
        optional_components = [r for r in component_results if not r.get('required', False)]
        
        required_success = [r for r in required_components if r['success']]
        optional_success = [r for r in optional_components if r['success']]
        
        return {
            'timestamp': datetime.now().isoformat(),
            'summary': {
                'total_components': total_components,
                'successful_components': len(successful_components),
                'failed_components': len(failed_components),
                'component_success_rate': len(successful_components) / total_components if total_components > 0 else 0,
                'total_reports': total_reports,
                'successful_reports': len(successful_reports),
                'failed_reports': len(failed_reports),
                'report_success_rate': len(successful_reports) / total_reports if total_reports > 0 else 0,
                'total_duration': total_duration,
                'required_components': len(required_components),
                'required_success': len(required_success),
                'optional_components': len(optional_components),
                'optional_success': len(optional_success)
            },
            'component_results': component_results,
            'report_results': report_results,
            'overall_success': len(required_success) == len(required_components) if required_components else True
        }
    
    def save_summary_report(self, report: Dict):
        """Save the summary report to file."""
        timestamp = int(time.time())
        report_file = self.results_dir / f"master-test-run-{timestamp}.json"
        
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2)
        
        print(f"\nSummary report saved: {report_file}")
        return report_file
    
    def print_summary(self, report: Dict):
        """Print a formatted summary of all test results."""
        summary = report['summary']
        
        print("\n" + "="*80)
        print("MASTER TEST RUNNER SUMMARY")
        print("="*80)
        
        print(f"\nOverall Status: {'✅ SUCCESS' if report['overall_success'] else '❌ FAILED'}")
        print(f"Timestamp: {report['timestamp']}")
        print(f"Total Duration: {summary['total_duration']:.2f} seconds")
        
        print(f"\nComponent Results:")
        print(f"  Total Components: {summary['total_components']}")
        print(f"  Successful: {summary['successful_components']}")
        print(f"  Failed: {summary['failed_components']}")
        print(f"  Success Rate: {summary['component_success_rate']:.1%}")
        
        print(f"\nRequired Components:")
        print(f"  Total Required: {summary['required_components']}")
        print(f"  Successful: {summary['required_success']}")
        print(f"  Failed: {summary['required_components'] - summary['required_success']}")
        
        print(f"\nOptional Components:")
        print(f"  Total Optional: {summary['optional_components']}")
        print(f"  Successful: {summary['optional_success']}")
        print(f"  Failed: {summary['optional_components'] - summary['optional_success']}")
        
        print(f"\nReport Generation:")
        print(f"  Total Reports: {summary['total_reports']}")
        print(f"  Successful: {summary['successful_reports']}")
        print(f"  Failed: {summary['failed_reports']}")
        print(f"  Success Rate: {summary['report_success_rate']:.1%}")
        
        print(f"\nDetailed Results:")
        for result in report['component_results']:
            status = "✅ PASS" if result['success'] else "❌ FAIL"
            print(f"  {status} {result['description']} ({result['duration']:.2f}s)")
            if not result['success'] and result['error']:
                print(f"    Error: {result['error']}")
        
        print("\n" + "="*80)
    
    def run_all_tests(self, parallel: bool = False, max_workers: int = 2, args: List[str] = None) -> Dict:
        """Run all test components and generate reports."""
        print("Starting Master Test Runner for WordPress Semgrep Rules")
        print(f"Project Root: {self.project_root}")
        print(f"Parallel Execution: {parallel}")
        print(f"Max Workers: {max_workers}")
        
        # Run test components
        if parallel:
            component_results = self.run_components_parallel(self.test_components, max_workers, args)
        else:
            component_results = self.run_components_sequential(self.test_components, args)
        
        # Run report generation components
        if parallel:
            report_results = self.run_components_parallel(self.report_components, max_workers, args)
        else:
            report_results = self.run_components_sequential(self.report_components, args)
        
        # Generate summary report
        summary_report = self.generate_summary_report(component_results, report_results)
        
        # Save and print summary
        self.save_summary_report(summary_report)
        self.print_summary(summary_report)
        
        return summary_report

def main():
    parser = argparse.ArgumentParser(description='Run all tests for WordPress Semgrep Rules')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--parallel', action='store_true', help='Run components in parallel')
    parser.add_argument('--workers', type=int, default=2, help='Number of parallel workers')
    parser.add_argument('--components', nargs='+', help='Specific components to run')
    parser.add_argument('--skip-reports', action='store_true', help='Skip report generation')
    
    args = parser.parse_args()
    
    # Initialize runner
    runner = MasterTestRunner(args.project_root)
    
    # Filter components if specified
    if args.components:
        runner.test_components = [c for c in runner.test_components if c['name'] in args.components]
        if not args.skip_reports:
            runner.report_components = [c for c in runner.report_components if c['name'] in args.components]
    
    # Skip reports if requested
    if args.skip_reports:
        runner.report_components = []
    
    # Run all tests
    summary_report = runner.run_all_tests(
        parallel=args.parallel,
        max_workers=args.workers
    )
    
    # Exit with appropriate code
    if summary_report['overall_success']:
        print(f"\n✅ All required tests completed successfully!")
        sys.exit(0)
    else:
        print(f"\n❌ Some required tests failed!")
        sys.exit(1)

if __name__ == '__main__':
    main()
