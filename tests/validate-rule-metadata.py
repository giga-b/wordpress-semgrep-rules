#!/usr/bin/env python3
"""
Rule Metadata Validation Script
Validates that all rules have the required metadata for quality gates.
"""

import yaml
import json
import sys
from pathlib import Path
from typing import Dict, List, Set
import argparse

class RuleMetadataValidator:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.required_fields = {
            'id': str,
            'message': str,
            'severity': str,
            'metadata': dict
        }
        
        self.required_metadata = {
            'confidence': str,
            'cwe': str,
            'category': str,
            'tags': list,
            'vuln_class': str
        }
        
        self.valid_severities = {'INFO', 'WARNING', 'ERROR'}
        self.valid_confidences = {'low', 'medium', 'high'}
        self.valid_vuln_classes = {
            'xss', 'sqli', 'csrf', 'authz', 'file_upload', 
            'deserialization', 'secrets_storage', 'rest_ajax', 'other'
        }
        
        self.results = {
            'total_rules': 0,
            'valid_rules': 0,
            'invalid_rules': 0,
            'errors': [],
            'warnings': []
        }
    
    def validate_rule_file(self, rule_file: Path) -> Dict:
        """Validate a single rule file."""
        result = {
            'file': str(rule_file),
            'valid': True,
            'errors': [],
            'warnings': []
        }
        
        try:
            with open(rule_file, 'r', encoding='utf-8') as f:
                content = yaml.safe_load(f)
            
            if not content or 'rules' not in content:
                result['valid'] = False
                result['errors'].append("No rules found in file")
                return result
            
            rules = content['rules']
            if not rules:
                result['valid'] = False
                result['errors'].append("Empty rules array")
                return result
            
            # Validate each rule
            for i, rule in enumerate(rules):
                rule_result = self.validate_single_rule(rule, i)
                result['errors'].extend(rule_result['errors'])
                result['warnings'].extend(rule_result['warnings'])
                if not rule_result['valid']:
                    result['valid'] = False
            
        except yaml.YAMLError as e:
            result['valid'] = False
            result['errors'].append(f"YAML parsing error: {e}")
        except Exception as e:
            result['valid'] = False
            result['errors'].append(f"Unexpected error: {e}")
        
        return result
    
    def validate_single_rule(self, rule: Dict, index: int) -> Dict:
        """Validate a single rule object."""
        result = {
            'valid': True,
            'errors': [],
            'warnings': []
        }
        
        # Check required fields
        for field, expected_type in self.required_fields.items():
            if field not in rule:
                result['valid'] = False
                result['errors'].append(f"Missing required field: {field}")
            elif not isinstance(rule[field], expected_type):
                result['valid'] = False
                result['errors'].append(f"Field {field} has wrong type: expected {expected_type.__name__}")
        
        # Validate severity
        if 'severity' in rule:
            severity = rule['severity']
            if severity not in self.valid_severities:
                result['valid'] = False
                result['errors'].append(f"Invalid severity: {severity}. Must be one of {self.valid_severities}")
        
        # Validate metadata
        if 'metadata' in rule:
            metadata = rule['metadata']
            if not isinstance(metadata, dict):
                result['valid'] = False
                result['errors'].append("Metadata must be a dictionary")
            else:
                metadata_result = self.validate_metadata(metadata)
                result['errors'].extend(metadata_result['errors'])
                result['warnings'].extend(metadata_result['warnings'])
                if not metadata_result['valid']:
                    result['valid'] = False
        else:
            result['valid'] = False
            result['errors'].append("Missing metadata section")
        
        # Add rule index to error messages
        if index > 0:
            for i, error in enumerate(result['errors']):
                result['errors'][i] = f"Rule {index}: {error}"
            for i, warning in enumerate(result['warnings']):
                result['warnings'][i] = f"Rule {index}: {warning}"
        
        return result
    
    def validate_metadata(self, metadata: Dict) -> Dict:
        """Validate metadata section."""
        result = {
            'valid': True,
            'errors': [],
            'warnings': []
        }
        
        # Check required metadata fields
        for field, expected_type in self.required_metadata.items():
            if field not in metadata:
                result['valid'] = False
                result['errors'].append(f"Missing required metadata field: {field}")
            elif not isinstance(metadata[field], expected_type):
                result['valid'] = False
                result['errors'].append(f"Metadata field {field} has wrong type: expected {expected_type.__name__}")
        
        # Validate confidence
        if 'confidence' in metadata:
            confidence = metadata['confidence']
            if confidence not in self.valid_confidences:
                result['valid'] = False
                result['errors'].append(f"Invalid confidence: {confidence}. Must be one of {self.valid_confidences}")
            elif confidence != 'high':
                result['warnings'].append(f"Confidence is {confidence}, but 'high' is recommended for production rules")
        
        # Validate vuln_class
        if 'vuln_class' in metadata:
            vuln_class = metadata['vuln_class']
            if vuln_class not in self.valid_vuln_classes:
                result['valid'] = False
                result['errors'].append(f"Invalid vuln_class: {vuln_class}. Must be one of {self.valid_vuln_classes}")
        
        # Validate CWE format
        if 'cwe' in metadata:
            cwe = metadata['cwe']
            if not cwe.startswith('CWE-'):
                result['warnings'].append(f"CWE should start with 'CWE-': {cwe}")
        
        # Validate tags
        if 'tags' in metadata:
            tags = metadata['tags']
            if not isinstance(tags, list):
                result['valid'] = False
                result['errors'].append("Tags must be a list")
            elif not tags:
                result['warnings'].append("Tags list is empty")
        
        return result
    
    def find_rule_files(self) -> List[Path]:
        """Find all rule files in the project."""
        rule_files = []
        pack_dirs = ['packs/wp-core-security', 'packs/wp-core-quality', 'packs/experimental']
        
        for pack_dir in pack_dirs:
            pack_path = self.project_root / pack_dir
            if pack_path.exists():
                rule_files.extend(pack_path.glob('*.yaml'))
        
        return rule_files
    
    def run_validation(self) -> Dict:
        """Run validation on all rule files."""
        rule_files = self.find_rule_files()
        self.results['total_rules'] = len(rule_files)
        
        print(f"Found {len(rule_files)} rule files to validate...")
        
        for rule_file in rule_files:
            print(f"Validating {rule_file}...")
            result = self.validate_rule_file(rule_file)
            
            if result['valid']:
                self.results['valid_rules'] += 1
                print(f"✅ {rule_file.name} - VALID")
            else:
                self.results['invalid_rules'] += 1
                print(f"❌ {rule_file.name} - INVALID")
                for error in result['errors']:
                    print(f"    Error: {error}")
            
            # Collect all errors and warnings
            self.results['errors'].extend(result['errors'])
            self.results['warnings'].extend(result['warnings'])
        
        return self.results
    
    def print_report(self):
        """Print validation report."""
        print("\n" + "="*80)
        print("RULE METADATA VALIDATION REPORT")
        print("="*80)
        
        print(f"\nOverall Results:")
        print(f"  Total Rules: {self.results['total_rules']}")
        print(f"  Valid: {self.results['valid_rules']}")
        print(f"  Invalid: {self.results['invalid_rules']}")
        print(f"  Success Rate: {self.results['valid_rules']/self.results['total_rules']*100:.1f}%" if self.results['total_rules'] > 0 else "  Success Rate: N/A")
        
        if self.results['errors']:
            print(f"\nErrors ({len(self.results['errors'])}):")
            for error in self.results['errors']:
                print(f"  ❌ {error}")
        
        if self.results['warnings']:
            print(f"\nWarnings ({len(self.results['warnings'])}):")
            for warning in self.results['warnings']:
                print(f"  ⚠️  {warning}")
        
        print("\n" + "="*80)
    
    def save_results(self, output_file: str = None):
        """Save validation results to file."""
        if output_file is None:
            output_file = self.project_root / "results" / "rule-metadata-validation.json"
        
        output_file = Path(output_file)
        output_file.parent.mkdir(parents=True, exist_ok=True)
        
        with open(output_file, 'w') as f:
            json.dump(self.results, f, indent=2)
        
        print(f"Results saved to: {output_file}")

def main():
    parser = argparse.ArgumentParser(description='Validate rule metadata for quality gates')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--output', help='Output file for results')
    
    args = parser.parse_args()
    
    # Initialize validator
    validator = RuleMetadataValidator(args.project_root)
    
    # Run validation
    results = validator.run_validation()
    
    # Print report
    validator.print_report()
    
    # Save results
    validator.save_results(args.output)
    
    # Exit with appropriate code
    if results['invalid_rules'] > 0:
        print(f"\n❌ Validation failed for {results['invalid_rules']} rules")
        sys.exit(1)
    else:
        print(f"\n✅ All rules passed metadata validation!")
        sys.exit(0)

if __name__ == '__main__':
    main()
