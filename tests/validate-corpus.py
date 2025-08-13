#!/usr/bin/env python3
"""
Corpus Validation Script for WordPress Semgrep Rules
Validates the attack corpus structure and content for testing.
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

class CorpusValidator:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.corpus_path = self.project_root / "corpus"
        self.results_dir = self.project_root / "results" / "corpus-validation"
        self.results_dir.mkdir(parents=True, exist_ok=True)
        
        # Load corpus metadata if it exists
        self.metadata_file = self.corpus_path / "metadata.json"
        self.metadata = self.load_metadata()
    
    def load_metadata(self) -> Dict:
        """Load corpus metadata."""
        if self.metadata_file.exists():
            try:
                with open(self.metadata_file, 'r', encoding='utf-8') as f:
                    return json.load(f)
            except Exception as e:
                print(f"Warning: Could not load metadata: {e}")
                return {}
        return {}
    
    def validate_corpus_structure(self) -> Dict:
        """Validate the corpus directory structure."""
        result = {
            'valid': True,
            'errors': [],
            'warnings': [],
            'components': [],
            'total_size': 0
        }
        
        if not self.corpus_path.exists():
            result['valid'] = False
            result['errors'].append("Corpus directory does not exist")
            return result
        
        # Check for expected subdirectories
        expected_dirs = ['wordpress-plugins', 'wordpress-themes']
        for expected_dir in expected_dirs:
            dir_path = self.corpus_path / expected_dir
            if not dir_path.exists():
                result['warnings'].append(f"Expected directory {expected_dir} not found")
        
        # Scan for components
        components = []
        total_size = 0
        
        for item in self.corpus_path.rglob('*'):
            if item.is_file():
                total_size += item.stat().st_size
                
                # Check if this is a PHP file (potential component)
                if item.suffix == '.php':
                    component_path = item.relative_to(self.corpus_path)
                    components.append({
                        'path': str(component_path),
                        'size': item.stat().st_size,
                        'type': 'php_file'
                    })
        
        result['components'] = components
        result['total_size'] = total_size
        
        # Validate metadata if available
        if self.metadata:
            result['metadata_valid'] = self.validate_metadata_structure()
        else:
            result['warnings'].append("No metadata.json found")
        
        return result
    
    def validate_metadata_structure(self) -> bool:
        """Validate metadata structure."""
        required_fields = ['components', 'total_size', 'last_updated']
        
        for field in required_fields:
            if field not in self.metadata:
                return False
        
        return True
    
    def validate_component_integrity(self) -> Dict:
        """Validate individual component integrity."""
        result = {
            'valid': True,
            'errors': [],
            'warnings': [],
            'components_checked': 0,
            'components_valid': 0
        }
        
        if not self.metadata or 'components' not in self.metadata:
            result['warnings'].append("No component metadata available")
            return result
        
        for component in self.metadata.get('components', []):
            result['components_checked'] += 1
            
            # Check if component path exists
            component_path = self.corpus_path / component.get('path', '')
            if not component_path.exists():
                result['errors'].append(f"Component path not found: {component.get('path', '')}")
                continue
            
            # Check if it's a directory (expected for plugins/themes)
            if not component_path.is_dir():
                result['warnings'].append(f"Component is not a directory: {component.get('path', '')}")
                continue
            
            # Check for basic WordPress structure
            if self.has_wordpress_structure(component_path):
                result['components_valid'] += 1
            else:
                result['warnings'].append(f"Component lacks WordPress structure: {component.get('path', '')}")
        
        result['valid'] = len(result['errors']) == 0
        return result
    
    def has_wordpress_structure(self, component_path: Path) -> bool:
        """Check if a component has basic WordPress structure."""
        # Look for common WordPress files
        wordpress_files = [
            'readme.txt',
            '*.php',
            'style.css',
            'index.php'
        ]
        
        found_files = 0
        for pattern in wordpress_files:
            if list(component_path.glob(pattern)):
                found_files += 1
        
        # Consider it valid if at least 2 WordPress files are found
        return found_files >= 2
    
    def run_basic_semgrep_scan(self) -> Dict:
        """Run a basic Semgrep scan on the corpus to validate it's scannable."""
        result = {
            'success': False,
            'findings_count': 0,
            'scan_time': 0,
            'error': None
        }
        
        try:
            start_time = time.time()
            
            # Run a basic scan with a simple rule
            cmd = [
                'semgrep', '--config', 'packs/wp-core-security/nonce-verification.yaml',
                '--json', '--quiet', '--no-git-ignore',
                str(self.corpus_path)
            ]
            
            process = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                cwd=self.project_root,
                timeout=300  # 5 minute timeout
            )
            
            scan_time = time.time() - start_time
            result['scan_time'] = scan_time
            
            if process.returncode == 0:
                try:
                    findings = json.loads(process.stdout)
                    result['success'] = True
                    result['findings_count'] = len(findings.get('results', []))
                except json.JSONDecodeError:
                    result['error'] = "Failed to parse Semgrep output"
            else:
                result['error'] = process.stderr
            
        except subprocess.TimeoutExpired:
            result['error'] = "Scan timed out after 5 minutes"
        except Exception as e:
            result['error'] = str(e)
        
        return result
    
    def generate_report(self, structure_result: Dict, integrity_result: Dict, scan_result: Dict) -> Dict:
        """Generate comprehensive validation report."""
        report = {
            'timestamp': datetime.now().isoformat(),
            'corpus_path': str(self.corpus_path),
            'structure_validation': structure_result,
            'integrity_validation': integrity_result,
            'scan_validation': scan_result,
            'overall_valid': (
                structure_result.get('valid', False) and
                integrity_result.get('valid', False) and
                scan_result.get('success', False)
            ),
            'summary': {
                'total_components': len(structure_result.get('components', [])),
                'valid_components': integrity_result.get('components_valid', 0),
                'total_size_mb': structure_result.get('total_size', 0) / (1024 * 1024),
                'scan_findings': scan_result.get('findings_count', 0),
                'scan_time_seconds': scan_result.get('scan_time', 0)
            }
        }
        
        return report
    
    def run_validation(self) -> Dict:
        """Run complete corpus validation."""
        print("Validating corpus structure...")
        structure_result = self.validate_corpus_structure()
        
        print("Validating component integrity...")
        integrity_result = self.validate_component_integrity()
        
        print("Running basic Semgrep scan...")
        scan_result = self.run_basic_semgrep_scan()
        
        print("Generating validation report...")
        report = self.generate_report(structure_result, integrity_result, scan_result)
        
        # Save report
        report_file = self.results_dir / f"corpus-validation-{int(time.time())}.json"
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2)
        
        return report
    
    def print_report(self, report: Dict):
        """Print a formatted validation report."""
        print("\n" + "="*80)
        print("CORPUS VALIDATION REPORT")
        print("="*80)
        
        print(f"\nCorpus Path: {report['corpus_path']}")
        print(f"Timestamp: {report['timestamp']}")
        print(f"Overall Valid: {'✅ YES' if report['overall_valid'] else '❌ NO'}")
        
        print(f"\nSummary:")
        summary = report['summary']
        print(f"  Total Components: {summary['total_components']}")
        print(f"  Valid Components: {summary['valid_components']}")
        print(f"  Total Size: {summary['total_size_mb']:.2f} MB")
        print(f"  Scan Findings: {summary['scan_findings']}")
        print(f"  Scan Time: {summary['scan_time_seconds']:.2f} seconds")
        
        # Structure validation
        structure = report['structure_validation']
        print(f"\nStructure Validation: {'✅ PASS' if structure['valid'] else '❌ FAIL'}")
        if structure['errors']:
            print("  Errors:")
            for error in structure['errors']:
                print(f"    - {error}")
        if structure['warnings']:
            print("  Warnings:")
            for warning in structure['warnings']:
                print(f"    - {warning}")
        
        # Integrity validation
        integrity = report['integrity_validation']
        print(f"\nIntegrity Validation: {'✅ PASS' if integrity['valid'] else '❌ FAIL'}")
        if integrity['errors']:
            print("  Errors:")
            for error in integrity['errors']:
                print(f"    - {error}")
        if integrity['warnings']:
            print("  Warnings:")
            for warning in integrity['warnings']:
                print(f"    - {warning}")
        
        # Scan validation
        scan = report['scan_validation']
        print(f"\nScan Validation: {'✅ PASS' if scan['success'] else '❌ FAIL'}")
        if scan['error']:
            print(f"  Error: {scan['error']}")
        
        print("\n" + "="*80)

def main():
    parser = argparse.ArgumentParser(description='Validate WordPress Semgrep Rules corpus')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--output', help='Output file for results')
    
    args = parser.parse_args()
    
    # Initialize validator
    validator = CorpusValidator(args.project_root)
    
    # Run validation
    report = validator.run_validation()
    
    # Print report
    validator.print_report(report)
    
    # Exit with appropriate code
    if report['overall_valid']:
        print(f"\n✅ Corpus validation passed!")
        sys.exit(0)
    else:
        print(f"\n❌ Corpus validation failed!")
        sys.exit(1)

if __name__ == '__main__':
    main()
