#!/usr/bin/env python3
"""
WordPress Semgrep Configuration Validator

This script validates WordPress Semgrep configuration files for:
- YAML syntax correctness
- Rule file existence and accessibility
- Configuration structure validation
- Rule ID format validation
- Performance optimization settings
- Cross-references between configurations

Usage:
    python validate-configs.py [config_file]
    python validate-configs.py --all
    python validate-configs.py --validate-rules
"""

import os
import sys
import yaml
import argparse
import glob
from pathlib import Path
from typing import Dict, List, Set, Tuple, Optional
import re
from dataclasses import dataclass
from enum import Enum


class ValidationLevel(Enum):
    """Validation levels for different types of checks."""
    SYNTAX = "syntax"
    STRUCTURE = "structure"
    REFERENCES = "references"
    RULES = "rules"
    PERFORMANCE = "performance"


@dataclass
class ValidationError:
    """Represents a validation error with context."""
    level: ValidationLevel
    message: str
    file: str
    line: Optional[int] = None
    column: Optional[int] = None
    context: Optional[str] = None


@dataclass
class ValidationWarning:
    """Represents a validation warning with context."""
    level: ValidationLevel
    message: str
    file: str
    line: Optional[int] = None
    context: Optional[str] = None


class ConfigValidator:
    """Validates WordPress Semgrep configuration files."""
    
    def __init__(self, project_root: str = "."):
        self.project_root = Path(project_root)
        self.configs_dir = self.project_root / "configs"
        self.packs_dir = self.project_root / "packs"
        self.errors: List[ValidationError] = []
        self.warnings: List[ValidationWarning] = []
        
        # Valid configuration keys
        self.valid_keys = {
            'include', 'exclude', 'rules', 'rule-filters', 
            'severity', 'metadata', 'languages', 'message',
            'patterns', 'pattern-not', 'fix', 'id', 'semgrep'
        }
        
        # Valid rule categories
        self.valid_categories = {
            'nonce-verification', 'capability-checks', 'sanitization-functions',
            'xss-prevention', 'sql-injection', 'ajax-security', 'rest-api-security',
            'file-operations', 'authentication', 'authorization', 'data-validation',
            'output-encoding', 'input-validation', 'session-management',
            'error-handling', 'logging', 'cryptography', 'performance',
            'quality', 'security', 'experimental', 'xss', 'ajax',
            'sql-injection-taint', 'taint-analysis', 'xss-taint-analysis'
        }
        
        # Valid severity levels
        self.valid_severities = {'ERROR', 'WARNING', 'INFO'}
        
        # Valid languages
        self.valid_languages = {'php', 'javascript', 'js', 'typescript', 'ts', 'html', 'css'}
        
        # WordPress-specific rule patterns
        self.wordpress_rule_patterns = {
            'wordpress.nonce.*',
            'wordpress.capability.*',
            'wordpress.sanitization.*',
            'wordpress.xss.*',
            'wordpress.sql.*',
            'wordpress.ajax.*',
            'wordpress.rest.*',
            'wordpress.file.*',
            'wordpress.auth.*',
            'wordpress.quality.*',
            'wordpress.performance.*',
            'wordpress.experimental.*'
        }

    def validate_all_configs(self) -> bool:
        """Validate all configuration files in the configs directory."""
        config_files = list(self.configs_dir.glob("*.yaml"))
        if not config_files:
            self.errors.append(ValidationError(
                ValidationLevel.STRUCTURE,
                "No configuration files found in configs directory",
                str(self.configs_dir)
            ))
            return False
        
        all_valid = True
        for config_file in config_files:
            if not self.validate_config_file(config_file):
                all_valid = False
        
        return all_valid

    def validate_config_file(self, config_path: Path) -> bool:
        """Validate a single configuration file."""
        print(f"Validating {config_path.name}...")
        
        # Reset errors and warnings for this file
        file_errors = []
        file_warnings = []
        
        try:
            # Read and parse YAML
            with open(config_path, 'r', encoding='utf-8') as f:
                content = f.read()
                config = yaml.safe_load(content)
            
            if config is None:
                self.errors.append(ValidationError(
                    ValidationLevel.SYNTAX,
                    "Configuration file is empty or contains only comments",
                    str(config_path)
                ))
                return False
            
            # Validate basic structure
            self._validate_config_structure(config, config_path)
            
            # Validate includes
            if 'include' in config:
                self._validate_includes(config['include'], config_path)
            
            # Validate excludes
            if 'exclude' in config:
                self._validate_excludes(config['exclude'], config_path)
            
            # Validate rule filters
            if 'rule-filters' in config:
                self._validate_rule_filters(config['rule-filters'], config_path)
            
            # Validate inline rules
            if 'rules' in config:
                self._validate_inline_rules(config['rules'], config_path)
            
            # Validate performance settings
            self._validate_performance_settings(config, config_path)
            
            # Validate cross-references
            self._validate_cross_references(config, config_path)
            
        except yaml.YAMLError as e:
            self.errors.append(ValidationError(
                ValidationLevel.SYNTAX,
                f"YAML syntax error: {str(e)}",
                str(config_path),
                getattr(e, 'line', None),
                getattr(e, 'column', None)
            ))
            return False
        except Exception as e:
            self.errors.append(ValidationError(
                ValidationLevel.STRUCTURE,
                f"Unexpected error: {str(e)}",
                str(config_path)
            ))
            return False
        
        return len([e for e in self.errors if e.file == str(config_path)]) == 0

    def _validate_config_structure(self, config: Dict, config_path: Path) -> None:
        """Validate the overall structure of the configuration."""
        # Check for required sections in certain config types
        config_name = config_path.stem
        
        if config_name in ['basic', 'strict', 'plugin-development']:
            if 'include' not in config:
                self.warnings.append(ValidationWarning(
                    ValidationLevel.STRUCTURE,
                    f"Configuration '{config_name}' should include rule packs",
                    str(config_path)
                ))
        
        # Validate top-level keys
        for key in config.keys():
            if key not in self.valid_keys:
                self.warnings.append(ValidationWarning(
                    ValidationLevel.STRUCTURE,
                    f"Unknown configuration key: {key}",
                    str(config_path)
                ))

    def _validate_includes(self, includes: List[str], config_path: Path) -> None:
        """Validate include paths and ensure files exist."""
        if not isinstance(includes, list):
            self.errors.append(ValidationError(
                ValidationLevel.STRUCTURE,
                "Include must be a list of strings",
                str(config_path)
            ))
            return
        
        for include_path in includes:
            if not isinstance(include_path, str):
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    f"Include path must be a string, got {type(include_path)}",
                    str(config_path)
                ))
                continue
            
            # Handle different include patterns
            if include_path.endswith('/'):
                # Directory include
                dir_path = self.project_root / include_path.rstrip('/')
                if not dir_path.exists():
                    self.errors.append(ValidationError(
                        ValidationLevel.REFERENCES,
                        f"Include directory does not exist: {include_path}",
                        str(config_path)
                    ))
                else:
                    # Check if directory contains YAML files
                    yaml_files = list(dir_path.glob("*.yaml"))
                    if not yaml_files:
                        self.warnings.append(ValidationWarning(
                            ValidationLevel.REFERENCES,
                            f"Include directory contains no YAML files: {include_path}",
                            str(config_path)
                        ))
            else:
                # File include
                file_path = self.project_root / include_path
                if not file_path.exists():
                    self.errors.append(ValidationError(
                        ValidationLevel.REFERENCES,
                        f"Include file does not exist: {include_path}",
                        str(config_path)
                    ))
                elif not file_path.suffix == '.yaml':
                    self.warnings.append(ValidationWarning(
                        ValidationLevel.REFERENCES,
                        f"Include file is not a YAML file: {include_path}",
                        str(config_path)
                    ))

    def _validate_excludes(self, excludes: List[str], config_path: Path) -> None:
        """Validate exclude patterns."""
        if not isinstance(excludes, list):
            self.errors.append(ValidationError(
                ValidationLevel.STRUCTURE,
                "Exclude must be a list of strings",
                str(config_path)
            ))
            return
        
        for exclude_pattern in excludes:
            if not isinstance(exclude_pattern, str):
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    f"Exclude pattern must be a string, got {type(exclude_pattern)}",
                    str(config_path)
                ))
                continue
            
            # Validate glob pattern syntax
            try:
                # Test if pattern is valid
                if '**' in exclude_pattern:
                    # Check for proper globstar usage
                    if not exclude_pattern.startswith('**/') and not exclude_pattern.endswith('/**'):
                        self.warnings.append(ValidationWarning(
                            ValidationLevel.PERFORMANCE,
                            f"Globstar pattern should start with '**/' or end with '/**': {exclude_pattern}",
                            str(config_path)
                        ))
            except Exception as e:
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    f"Invalid exclude pattern: {exclude_pattern}",
                    str(config_path)
                ))

    def _validate_rule_filters(self, rule_filters: List[Dict], config_path: Path) -> None:
        """Validate rule filter configurations."""
        if not isinstance(rule_filters, list):
            self.errors.append(ValidationError(
                ValidationLevel.STRUCTURE,
                "Rule filters must be a list",
                str(config_path)
            ))
            return
        
        for filter_config in rule_filters:
            if not isinstance(filter_config, dict):
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    "Rule filter must be a dictionary",
                    str(config_path)
                ))
                continue
            
            # Check for valid filter keys
            valid_filter_keys = {'include', 'exclude'}
            for key in filter_config.keys():
                if key not in valid_filter_keys:
                    self.errors.append(ValidationError(
                        ValidationLevel.STRUCTURE,
                        f"Invalid rule filter key: {key}",
                        str(config_path)
                    ))
            
            # Validate filter patterns
            for filter_type in ['include', 'exclude']:
                if filter_type in filter_config:
                    pattern = filter_config[filter_type]
                    if not isinstance(pattern, str):
                        self.errors.append(ValidationError(
                            ValidationLevel.STRUCTURE,
                            f"Rule filter pattern must be a string, got {type(pattern)}",
                            str(config_path)
                        ))
                    else:
                        # Validate pattern format
                        if not self._is_valid_rule_pattern(pattern):
                            self.warnings.append(ValidationWarning(
                                ValidationLevel.STRUCTURE,
                                f"Rule filter pattern may not match any rules: {pattern}",
                                str(config_path)
                            ))

    def _validate_inline_rules(self, rules: List[Dict], config_path: Path) -> None:
        """Validate inline rule definitions."""
        if not isinstance(rules, list):
            self.errors.append(ValidationError(
                ValidationLevel.STRUCTURE,
                "Rules must be a list",
                str(config_path)
            ))
            return
        
        for i, rule in enumerate(rules):
            if not isinstance(rule, dict):
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    f"Rule {i} must be a dictionary",
                    str(config_path)
                ))
                continue
            
            self._validate_single_rule(rule, config_path, rule_index=i)

    def _validate_single_rule(self, rule: Dict, config_path: Path, rule_index: int = None) -> None:
        """Validate a single rule definition."""
        # Required fields
        required_fields = ['id', 'languages', 'message']
        for field in required_fields:
            if field not in rule:
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    f"Rule missing required field: {field}",
                    str(config_path),
                    context=f"Rule {rule_index or rule.get('id', 'unknown')}"
                ))
        
        # Validate rule ID
        if 'id' in rule:
            rule_id = rule['id']
            if not isinstance(rule_id, str):
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    "Rule ID must be a string",
                    str(config_path),
                    context=f"Rule {rule_index or 'unknown'}"
                ))
            elif not self._is_valid_rule_id(rule_id):
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    f"Invalid rule ID format: {rule_id}",
                    str(config_path),
                    context=f"Rule {rule_index or 'unknown'}"
                ))
        
        # Validate languages
        if 'languages' in rule:
            languages = rule['languages']
            if not isinstance(languages, list):
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    "Languages must be a list",
                    str(config_path),
                    context=f"Rule {rule_index or rule.get('id', 'unknown')}"
                ))
            else:
                for lang in languages:
                    if lang not in self.valid_languages:
                        self.warnings.append(ValidationWarning(
                            ValidationLevel.STRUCTURE,
                            f"Unsupported language: {lang}",
                            str(config_path),
                            context=f"Rule {rule_index or rule.get('id', 'unknown')}"
                        ))
        
        # Validate severity
        if 'severity' in rule:
            severity = rule['severity']
            if severity not in self.valid_severities:
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    f"Invalid severity level: {severity}",
                    str(config_path),
                    context=f"Rule {rule_index or rule.get('id', 'unknown')}"
                ))
        
        # Validate metadata
        if 'metadata' in rule:
            metadata = rule['metadata']
            if not isinstance(metadata, dict):
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    "Metadata must be a dictionary",
                    str(config_path),
                    context=f"Rule {rule_index or rule.get('id', 'unknown')}"
                ))
            else:
                self._validate_rule_metadata(metadata, config_path, rule_index)
        
        # Validate patterns
        if 'patterns' in rule:
            patterns = rule['patterns']
            if not isinstance(patterns, list):
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    "Patterns must be a list",
                    str(config_path),
                    context=f"Rule {rule_index or rule.get('id', 'unknown')}"
                ))
            else:
                for i, pattern_item in enumerate(patterns):
                    if not isinstance(pattern_item, dict):
                        self.errors.append(ValidationError(
                            ValidationLevel.STRUCTURE,
                            f"Pattern {i} must be a dictionary",
                            str(config_path),
                            context=f"Rule {rule_index or rule.get('id', 'unknown')}"
                        ))
                        continue
                    
                    # Check for valid pattern keys
                    valid_pattern_keys = {'pattern', 'pattern-not', 'pattern-either', 'pattern-inside', 'pattern-regex'}
                    for key in pattern_item.keys():
                        if key not in valid_pattern_keys:
                            self.warnings.append(ValidationWarning(
                                ValidationLevel.STRUCTURE,
                                f"Unknown pattern key: {key}",
                                str(config_path),
                                context=f"Rule {rule_index or rule.get('id', 'unknown')}"
                            ))
                    
                    # Validate pattern content
                    for pattern_type in ['pattern', 'pattern-not', 'pattern-either', 'pattern-inside', 'pattern-regex']:
                        if pattern_type in pattern_item:
                            pattern_content = pattern_item[pattern_type]
                            if not isinstance(pattern_content, str):
                                self.errors.append(ValidationError(
                                    ValidationLevel.STRUCTURE,
                                    f"Pattern {i} {pattern_type} must be a string",
                                    str(config_path),
                                    context=f"Rule {rule_index or rule.get('id', 'unknown')}"
                                ))

    def _validate_rule_metadata(self, metadata: Dict, config_path: Path, rule_index: int = None) -> None:
        """Validate rule metadata."""
        # Validate category
        if 'category' in metadata:
            category = metadata['category']
            if category not in self.valid_categories:
                self.warnings.append(ValidationWarning(
                    ValidationLevel.STRUCTURE,
                    f"Unknown category: {category}",
                    str(config_path),
                    context=f"Rule {rule_index or 'unknown'}"
                ))
        
        # Validate CWE
        if 'cwe' in metadata:
            cwe = metadata['cwe']
            if not isinstance(cwe, str) or not cwe.startswith('CWE-'):
                self.warnings.append(ValidationWarning(
                    ValidationLevel.STRUCTURE,
                    f"Invalid CWE format: {cwe}",
                    str(config_path),
                    context=f"Rule {rule_index or 'unknown'}"
                ))
        
        # Validate references
        if 'references' in metadata:
            references = metadata['references']
            if not isinstance(references, list):
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    "References must be a list",
                    str(config_path),
                    context=f"Rule {rule_index or 'unknown'}"
                ))
            else:
                for ref in references:
                    if not isinstance(ref, str):
                        self.errors.append(ValidationError(
                            ValidationLevel.STRUCTURE,
                            "Reference must be a string",
                            str(config_path),
                            context=f"Rule {rule_index or 'unknown'}"
                        ))

    def _validate_performance_settings(self, config: Dict, config_path: Path) -> None:
        """Validate performance-related settings."""
        config_name = config_path.stem
        
        # Check for appropriate exclude patterns
        if 'exclude' not in config:
            if config_name in ['strict', 'plugin-development']:
                self.warnings.append(ValidationWarning(
                    ValidationLevel.PERFORMANCE,
                    f"Configuration '{config_name}' should include exclude patterns for performance",
                    str(config_path)
                ))
        
        # Check for rule filters in performance-critical configs
        if 'rule-filters' not in config:
            if config_name in ['strict', 'plugin-development']:
                self.warnings.append(ValidationWarning(
                    ValidationLevel.PERFORMANCE,
                    f"Configuration '{config_name}' should include rule filters for performance",
                    str(config_path)
                ))

    def _validate_cross_references(self, config: Dict, config_path: Path) -> None:
        """Validate cross-references between configurations."""
        # Check for circular includes (basic check)
        if 'include' in config:
            includes = config['include']
            for include_path in includes:
                if include_path.endswith('.yaml'):
                    included_file = self.project_root / include_path
                    if included_file.exists():
                        try:
                            with open(included_file, 'r', encoding='utf-8') as f:
                                included_config = yaml.safe_load(f)
                                if included_config and 'include' in included_config:
                                    # Check for potential circular references
                                    for sub_include in included_config['include']:
                                        if sub_include == str(config_path.relative_to(self.project_root)):
                                            self.errors.append(ValidationError(
                                                ValidationLevel.REFERENCES,
                                                f"Circular include detected: {config_path.name} -> {include_path}",
                                                str(config_path)
                                            ))
                        except Exception:
                            # Skip if we can't read the included file
                            pass

    def _is_valid_rule_id(self, rule_id: str) -> bool:
        """Check if a rule ID follows the expected format."""
        # WordPress rule ID pattern: wordpress.category.rule-name
        wordpress_pattern = r'^wordpress\.[a-z-]+\.[a-z0-9-]+$'
        
        # Alternative patterns for experimental and framework rules
        experimental_patterns = [
            r'^[a-z-]+\.[a-z-]+\.[a-z0-9-]+$',  # category.type.name
            r'^[a-z-]+-[a-z-]+-[a-z0-9-]+$',    # type-category-name
            r'^[a-z-]+\.[a-z0-9-]+$',           # type.name
            r'^[a-z-]+-[a-z0-9-]+$'             # type-name
        ]
        
        # Check WordPress pattern first
        if re.match(wordpress_pattern, rule_id):
            return True
        
        # Check experimental patterns
        for pattern in experimental_patterns:
            if re.match(pattern, rule_id):
                return True
        
        return False

    def _is_valid_rule_pattern(self, pattern: str) -> bool:
        """Check if a rule pattern is valid."""
        # Check if pattern matches any known WordPress rule patterns
        for wp_pattern in self.wordpress_rule_patterns:
            if self._pattern_matches(pattern, wp_pattern):
                return True
        return False

    def _pattern_matches(self, pattern: str, wp_pattern: str) -> bool:
        """Check if a pattern matches a WordPress rule pattern."""
        # Convert glob pattern to regex
        regex_pattern = wp_pattern.replace('*', '.*')
        return bool(re.match(regex_pattern, pattern))

    def validate_rule_files(self) -> bool:
        """Validate all rule files in the packs directory."""
        print("Validating rule files...")
        
        all_valid = True
        for pack_dir in self.packs_dir.iterdir():
            if pack_dir.is_dir():
                for rule_file in pack_dir.glob("*.yaml"):
                    if not self._validate_rule_file(rule_file):
                        all_valid = False
        
        return all_valid

    def _validate_rule_file(self, rule_file: Path) -> bool:
        """Validate a single rule file."""
        try:
            with open(rule_file, 'r', encoding='utf-8') as f:
                content = f.read()
                rule_data = yaml.safe_load(content)
            
            if rule_data is None:
                self.errors.append(ValidationError(
                    ValidationLevel.SYNTAX,
                    "Rule file is empty or contains only comments",
                    str(rule_file)
                ))
                return False
            
            # Handle both direct rule lists and rules under a 'rules' key
            if isinstance(rule_data, list):
                rules = rule_data
            elif isinstance(rule_data, dict) and 'rules' in rule_data:
                rules = rule_data['rules']
            else:
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    "Rule file must contain a list of rules or a 'rules' section",
                    str(rule_file)
                ))
                return False
            
            if not isinstance(rules, list):
                self.errors.append(ValidationError(
                    ValidationLevel.STRUCTURE,
                    "Rules must be a list",
                    str(rule_file)
                ))
                return False
            
            for i, rule in enumerate(rules):
                if not isinstance(rule, dict):
                    self.errors.append(ValidationError(
                        ValidationLevel.STRUCTURE,
                        f"Rule {i} must be a dictionary",
                        str(rule_file)
                    ))
                    continue
                
                self._validate_single_rule(rule, rule_file, rule_index=i)
            
        except yaml.YAMLError as e:
            self.errors.append(ValidationError(
                ValidationLevel.SYNTAX,
                f"YAML syntax error: {str(e)}",
                str(rule_file),
                getattr(e, 'line', None),
                getattr(e, 'column', None)
            ))
            return False
        except Exception as e:
            self.errors.append(ValidationError(
                ValidationLevel.STRUCTURE,
                f"Unexpected error: {str(e)}",
                str(rule_file)
            ))
            return False
        
        return True

    def print_results(self) -> None:
        """Print validation results in a formatted way."""
        print("\n" + "="*60)
        print("WordPress Semgrep Configuration Validation Results")
        print("="*60)
        
        if not self.errors and not self.warnings:
            print("✅ All configurations are valid!")
            return
        
        # Print errors
        if self.errors:
            print(f"\n❌ {len(self.errors)} Error(s):")
            print("-" * 40)
            for error in self.errors:
                print(f"Error ({error.level.value}): {error.message}")
                print(f"  File: {error.file}")
                if error.line:
                    print(f"  Line: {error.line}")
                if error.column:
                    print(f"  Column: {error.column}")
                if error.context:
                    print(f"  Context: {error.context}")
                print()
        
        # Print warnings
        if self.warnings:
            print(f"\n⚠️  {len(self.warnings)} Warning(s):")
            print("-" * 40)
            for warning in self.warnings:
                print(f"Warning ({warning.level.value}): {warning.message}")
                print(f"  File: {warning.file}")
                if warning.line:
                    print(f"  Line: {warning.line}")
                if warning.context:
                    print(f"  Context: {warning.context}")
                print()
        
        # Summary
        print(f"\nSummary:")
        print(f"  Errors: {len(self.errors)}")
        print(f"  Warnings: {len(self.warnings)}")
        print(f"  Status: {'❌ Failed' if self.errors else '✅ Passed' if not self.warnings else '⚠️  Passed with warnings'}")

    def get_validation_summary(self) -> Dict:
        """Get a summary of validation results."""
        return {
            'total_errors': len(self.errors),
            'total_warnings': len(self.warnings),
            'passed': len(self.errors) == 0,
            'errors_by_level': self._group_by_level(self.errors),
            'warnings_by_level': self._group_by_level(self.warnings),
            'errors_by_file': self._group_by_file(self.errors),
            'warnings_by_file': self._group_by_file(self.warnings)
        }

    def _group_by_level(self, items: List) -> Dict:
        """Group validation items by level."""
        grouped = {}
        for item in items:
            level = item.level.value
            if level not in grouped:
                grouped[level] = []
            grouped[level].append(item)
        return grouped

    def _group_by_file(self, items: List) -> Dict:
        """Group validation items by file."""
        grouped = {}
        for item in items:
            file = item.file
            if file not in grouped:
                grouped[file] = []
            grouped[file].append(item)
        return grouped


def main():
    """Main entry point for the configuration validator."""
    parser = argparse.ArgumentParser(
        description="Validate WordPress Semgrep configuration files",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  python validate-configs.py                           # Validate all configs
  python validate-configs.py configs/basic.yaml        # Validate specific config
  python validate-configs.py --all                     # Validate all configs and rules
  python validate-configs.py --validate-rules          # Validate rule files only
        """
    )
    
    parser.add_argument(
        'config_file',
        nargs='?',
        help='Specific configuration file to validate'
    )
    
    parser.add_argument(
        '--all',
        action='store_true',
        help='Validate all configuration files and rule files'
    )
    
    parser.add_argument(
        '--validate-rules',
        action='store_true',
        help='Validate rule files in addition to configuration files'
    )
    
    parser.add_argument(
        '--project-root',
        default='.',
        help='Project root directory (default: current directory)'
    )
    
    parser.add_argument(
        '--output',
        choices=['text', 'json', 'summary'],
        default='text',
        help='Output format (default: text)'
    )
    
    args = parser.parse_args()
    
    # Initialize validator
    validator = ConfigValidator(args.project_root)
    
    # Determine what to validate
    if args.config_file:
        # Validate specific file
        config_path = Path(args.config_file)
        if not config_path.exists():
            print(f"Error: Configuration file not found: {args.config_file}")
            sys.exit(1)
        
        success = validator.validate_config_file(config_path)
        if args.validate_rules:
            rules_success = validator.validate_rule_files()
            success = success and rules_success
    
    elif args.all:
        # Validate everything
        configs_success = validator.validate_all_configs()
        rules_success = validator.validate_rule_files()
        success = configs_success and rules_success
    
    else:
        # Default: validate all configs
        success = validator.validate_all_configs()
        if args.validate_rules:
            rules_success = validator.validate_rule_files()
            success = success and rules_success
    
    # Output results
    if args.output == 'json':
        import json
        summary = validator.get_validation_summary()
        print(json.dumps(summary, indent=2))
    elif args.output == 'summary':
        summary = validator.get_validation_summary()
        print(f"Errors: {summary['total_errors']}")
        print(f"Warnings: {summary['total_warnings']}")
        print(f"Status: {'PASSED' if summary['passed'] else 'FAILED'}")
    else:
        validator.print_results()
    
    # Exit with appropriate code
    sys.exit(0 if success else 1)


if __name__ == '__main__':
    main()
