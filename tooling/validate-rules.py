#!/usr/bin/env python3
"""
WordPress Semgrep Rule Validator

This script validates Semgrep rule files to ensure they meet the project's
standards and requirements. It's used as part of the pre-commit hook to catch
rule issues before they're committed.

Usage:
    python tooling/validate-rules.py [rule_file]
"""

import sys
import os
import yaml
import argparse
import re
from pathlib import Path
from typing import Dict, List, Any, Optional


class RuleValidator:
    """Validates Semgrep rule files."""
    
    def __init__(self):
        self.errors = []
        self.warnings = []
        self.project_root = Path(__file__).parent.parent
        
    def validate_rule_file(self, rule_path: str) -> bool:
        """Validate a single rule file."""
        rule_file = Path(rule_path)
        
        if not rule_file.exists():
            self.errors.append(f"Rule file not found: {rule_path}")
            return False
            
        try:
            with open(rule_file, 'r', encoding='utf-8') as f:
                content = f.read()
                rules = yaml.safe_load(content)
        except yaml.YAMLError as e:
            self.errors.append(f"Invalid YAML in {rule_path}: {e}")
            return False
        except Exception as e:
            self.errors.append(f"Error reading {rule_path}: {e}")
            return False
            
        if not rules:
            self.errors.append(f"Empty rule file: {rule_path}")
            return False
            
        # Handle both single rule and list of rules
        if isinstance(rules, dict):
            rules_list = [rules]
        elif isinstance(rules, list):
            rules_list = rules
        else:
            self.errors.append(f"Invalid rule format in {rule_path}: must be a rule or list of rules")
            return False
            
        # Validate each rule
        for i, rule in enumerate(rules_list):
            if not isinstance(rule, dict):
                self.errors.append(f"Invalid rule format in {rule_path} at index {i}: must be a dictionary")
                continue
                
            self._validate_rule(rule, rule_path, i)
            
        # Validate file-specific requirements
        self._validate_file_requirements(rule_path, content)
        
        return len(self.errors) == 0
        
    def _validate_rule(self, rule: Dict[str, Any], rule_path: str, rule_index: int):
        """Validate individual rule structure and content."""
        # Check required fields
        required_fields = {'id', 'languages', 'message', 'severity'}
        for field in required_fields:
            if field not in rule:
                self.errors.append(f"Missing required field '{field}' in rule {rule_index} of {rule_path}")
                
        # Validate rule ID
        if 'id' in rule:
            self._validate_rule_id(rule['id'], rule_path, rule_index)
            
        # Validate languages
        if 'languages' in rule:
            self._validate_languages(rule['languages'], rule_path, rule_index)
            
        # Validate severity
        if 'severity' in rule:
            self._validate_severity(rule['severity'], rule_path, rule_index)
            
        # Validate message
        if 'message' in rule:
            self._validate_message(rule['message'], rule_path, rule_index)
            
        # Validate patterns
        if 'patterns' in rule:
            self._validate_patterns(rule['patterns'], rule_path, rule_index)
            
        # Validate metadata
        if 'metadata' in rule:
            self._validate_metadata(rule['metadata'], rule_path, rule_index)
            
        # Validate fix
        if 'fix' in rule:
            self._validate_fix(rule['fix'], rule_path, rule_index)
            
    def _validate_rule_id(self, rule_id: str, rule_path: str, rule_index: int):
        """Validate rule ID format and uniqueness."""
        if not isinstance(rule_id, str):
            self.errors.append(f"Rule ID must be a string in rule {rule_index} of {rule_path}")
            return
            
        # Check format: category.rule-name
        if '.' not in rule_id:
            self.warnings.append(f"Rule ID should follow format 'category.rule-name' in {rule_path} rule {rule_index}")
            
        # Check for valid characters
        if not re.match(r'^[a-z0-9._-]+$', rule_id):
            self.warnings.append(f"Rule ID should contain only lowercase letters, numbers, dots, underscores, and hyphens in {rule_path} rule {rule_index}")
            
        # Check length
        if len(rule_id) > 100:
            self.warnings.append(f"Rule ID is very long ({len(rule_id)} chars) in {rule_path} rule {rule_index}")
            
    def _validate_languages(self, languages: List[str], rule_path: str, rule_index: int):
        """Validate supported languages."""
        if not isinstance(languages, list):
            self.errors.append(f"Languages must be a list in rule {rule_index} of {rule_path}")
            return
            
        if not languages:
            self.errors.append(f"Languages list cannot be empty in rule {rule_index} of {rule_path}")
            return
            
        valid_languages = {
            'php', 'javascript', 'python', 'java', 'go', 'ruby', 
            'csharp', 'typescript', 'c', 'cpp', 'c++', 'rust', 'kotlin'
        }
        
        for lang in languages:
            if not isinstance(lang, str):
                self.errors.append(f"Language must be a string in rule {rule_index} of {rule_path}")
            elif lang not in valid_languages:
                self.warnings.append(f"Unknown language '{lang}' in rule {rule_index} of {rule_path}")
                
    def _validate_severity(self, severity: str, rule_path: str, rule_index: int):
        """Validate severity level."""
        if not isinstance(severity, str):
            self.errors.append(f"Severity must be a string in rule {rule_index} of {rule_path}")
            return
            
        valid_severities = {'ERROR', 'WARNING', 'INFO'}
        if severity not in valid_severities:
            self.errors.append(f"Invalid severity '{severity}' in rule {rule_index} of {rule_path}. Must be one of: {valid_severities}")
            
    def _validate_message(self, message: str, rule_path: str, rule_index: int):
        """Validate error message."""
        if not isinstance(message, str):
            self.errors.append(f"Message must be a string in rule {rule_index} of {rule_path}")
            return
            
        if not message.strip():
            self.errors.append(f"Message cannot be empty in rule {rule_index} of {rule_path}")
            return
            
        # Check message length
        if len(message) > 200:
            self.warnings.append(f"Message is very long ({len(message)} chars) in rule {rule_index} of {rule_path}")
            
        # Check for actionable content
        if not any(word in message.lower() for word in ['use', 'should', 'must', 'need', 'require']):
            self.warnings.append(f"Message should be actionable in rule {rule_index} of {rule_path}")
            
    def _validate_patterns(self, patterns: List[str], rule_path: str, rule_index: int):
        """Validate pattern syntax."""
        if not isinstance(patterns, list):
            self.errors.append(f"Patterns must be a list in rule {rule_index} of {rule_path}")
            return
            
        if not patterns:
            self.errors.append(f"Patterns list cannot be empty in rule {rule_index} of {rule_path}")
            return
            
        for i, pattern in enumerate(patterns):
            if not isinstance(pattern, str):
                self.errors.append(f"Pattern must be a string in rule {rule_index} pattern {i} of {rule_path}")
            elif not pattern.strip():
                self.errors.append(f"Pattern cannot be empty in rule {rule_index} pattern {i} of {rule_path}")
                
    def _validate_metadata(self, metadata: Dict[str, Any], rule_path: str, rule_index: int):
        """Validate metadata structure."""
        if not isinstance(metadata, dict):
            self.errors.append(f"Metadata must be a dictionary in rule {rule_index} of {rule_path}")
            return
            
        # Check for required metadata fields
        if 'category' not in metadata:
            self.warnings.append(f"Metadata should include 'category' in rule {rule_index} of {rule_path}")
            
        if 'cwe' not in metadata:
            self.warnings.append(f"Metadata should include 'cwe' in rule {rule_index} of {rule_path}")
            
        # Validate CWE format
        if 'cwe' in metadata:
            cwe = metadata['cwe']
            if isinstance(cwe, str) and not re.match(r'^CWE-\d+$', cwe):
                self.warnings.append(f"CWE should follow format 'CWE-XXX' in rule {rule_index} of {rule_path}")
                
        # Validate category
        if 'category' in metadata:
            category = metadata['category']
            if isinstance(category, str):
                valid_categories = {
                    'nonce-verification', 'capability-checks', 'sanitization-functions',
                    'xss-prevention', 'sql-injection', 'rest-api-security', 'ajax-security',
                    'file-operations', 'authentication', 'authorization', 'input-validation'
                }
                if category not in valid_categories:
                    self.warnings.append(f"Unknown category '{category}' in rule {rule_index} of {rule_path}")
                    
    def _validate_fix(self, fix: str, rule_path: str, rule_index: int):
        """Validate fix suggestion."""
        if not isinstance(fix, str):
            self.errors.append(f"Fix must be a string in rule {rule_index} of {rule_path}")
            return
            
        if not fix.strip():
            self.errors.append(f"Fix cannot be empty in rule {rule_index} of {rule_path}")
            return
            
        # Check fix length
        if len(fix) > 500:
            self.warnings.append(f"Fix is very long ({len(fix)} chars) in rule {rule_index} of {rule_path}")
            
    def _validate_file_requirements(self, rule_path: str, content: str):
        """Validate file-specific requirements."""
        # Check for file header comment
        if not content.strip().startswith('#'):
            self.warnings.append(f"Rule file {rule_path} should start with a comment header")
            
        # Check for proper file organization
        if 'rules:' not in content and not content.strip().startswith('- id:'):
            self.warnings.append(f"Rule file {rule_path} should have proper YAML structure")
            
    def print_results(self):
        """Print validation results."""
        if self.errors:
            print("❌ Rule validation errors:")
            for error in self.errors:
                print(f"  - {error}")
                
        if self.warnings:
            print("⚠️  Rule validation warnings:")
            for warning in self.warnings:
                print(f"  - {warning}")
                
        if not self.errors and not self.warnings:
            print("✅ Rule validation passed")
            
        return len(self.errors) == 0


def main():
    """Main entry point."""
    parser = argparse.ArgumentParser(description="Validate WordPress Semgrep rule files")
    parser.add_argument("rule_file", nargs="?", help="Rule file to validate")
    args = parser.parse_args()
    
    validator = RuleValidator()
    
    if args.rule_file:
        # Validate specific file
        success = validator.validate_rule_file(args.rule_file)
    else:
        # Validate all rule files
        packs_dir = Path(__file__).parent.parent / "packs"
        success = True
        
        if not packs_dir.exists():
            print(f"❌ Packs directory not found: {packs_dir}")
            return 1
            
        for rule_file in packs_dir.rglob("*.yaml"):
            print(f"Validating {rule_file}...")
            if not validator.validate_rule_file(str(rule_file)):
                success = False
                
    validator.print_results()
    
    return 0 if success else 1


if __name__ == "__main__":
    sys.exit(main())
