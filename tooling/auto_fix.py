#!/usr/bin/env python3
"""
WordPress Semgrep Rules - Auto-fix System

This module provides automatic fixing capabilities for common WordPress security issues
detected by Semgrep rules. It can fix issues like missing nonce verification,
unsanitized input, unsafe output, and other security vulnerabilities.
"""

import json
import re
import os
import sys
import argparse
import yaml
from pathlib import Path
from typing import Dict, List, Tuple, Optional, Any
from dataclasses import dataclass
from datetime import datetime
import logging

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

@dataclass
class FixResult:
    """Result of an auto-fix operation"""
    file_path: str
    line_number: int
    rule_id: str
    original_code: str
    fixed_code: str
    fix_type: str
    confidence: float
    applied: bool
    error_message: Optional[str] = None

@dataclass
class AutoFixRule:
    """Definition of an auto-fix rule"""
    rule_id: str
    pattern: str
    replacement: str
    conditions: List[str]
    confidence: float
    description: str
    category: str

class WordPressAutoFixer:
    """Main auto-fix engine for WordPress security issues"""
    
    def __init__(self, config_path: Optional[str] = None):
        self.config_path = config_path or "tooling/auto-fix-config.yaml"
        self.fix_rules = self._load_fix_rules()
        self.stats = {
            'files_processed': 0,
            'issues_found': 0,
            'fixes_applied': 0,
            'fixes_skipped': 0,
            'errors': 0
        }
    
    def _load_fix_rules(self) -> List[AutoFixRule]:
        """Load auto-fix rules from configuration"""
        default_rules = [
            # Nonce verification fixes
            AutoFixRule(
                rule_id="wordpress.nonce.missing-verification",
                pattern=r'if\s*\(\s*isset\s*\(\s*\$_\w+\[\s*[\'"][^\'"]+[\'"]\s*\]\s*\)\s*\)\s*\{',
                replacement='if (isset($_POST[\'submit\']) && wp_verify_nonce($_POST[\'_wpnonce\'], \'action_name\')) {',
                conditions=["php", "form_processing"],
                confidence=0.8,
                description="Add nonce verification to form processing",
                category="nonce-verification"
            ),
            
            # Sanitization fixes
            AutoFixRule(
                rule_id="wordpress.sanitization.missing-input",
                pattern=r'(\$\w+)\s*=\s*\$_\w+\[\s*[\'"][^\'"]+[\'"]\s*\];',
                replacement=r'\1 = sanitize_text_field($_POST[\'user_input\']);',
                conditions=["php", "user_input"],
                confidence=0.9,
                description="Add sanitization to user input",
                category="sanitization"
            ),
            
            # Output escaping fixes
            AutoFixRule(
                rule_id="wordpress.sanitization.unsafe-output",
                pattern=r'echo\s+(\$\w+);',
                replacement=r'echo esc_html(\1);',
                conditions=["php", "output"],
                confidence=0.85,
                description="Add output escaping",
                category="output-escaping"
            ),
            
            # Database query fixes
            AutoFixRule(
                rule_id="wordpress.sanitization.unsafe-db-query",
                pattern=r'\$wpdb->query\s*\(\s*[\'"][^\'"]*\$[^\'"]*[\'"]\s*\);',
                replacement=r'$wpdb->prepare("SELECT * FROM posts WHERE title LIKE %s", \'%\' . $wpdb->esc_like($user_input) . \'%\');',
                conditions=["php", "database"],
                confidence=0.7,
                description="Use prepared statements for database queries",
                category="database-security"
            ),
            
            # Capability check fixes
            AutoFixRule(
                rule_id="wordpress.capability.missing-check",
                pattern=r'function\s+(\w+)\s*\(\s*\)\s*\{',
                replacement=r'function \1() {\n    if (!current_user_can(\'manage_options\')) {\n        wp_die(__(\'You do not have sufficient permissions to access this page.\'));\n    }',
                conditions=["php", "admin_function"],
                confidence=0.6,
                description="Add capability check to admin functions",
                category="capability-checks"
            ),
            
            # AJAX security fixes
            AutoFixRule(
                rule_id="wordpress.ajax.missing-nonce",
                pattern=r'function\s+(\w+)\s*\(\s*\)\s*\{\s*\$data\s*=\s*\$_\w+\[\s*[\'"][^\'"]+[\'"]\s*\];',
                replacement=r'function \1() {\n    check_ajax_referer(\'my_nonce_action\', \'nonce\');\n    $data = $_POST[\'data\'];',
                conditions=["php", "ajax_handler"],
                confidence=0.75,
                description="Add nonce verification to AJAX handlers",
                category="ajax-security"
            )
        ]
        
        # Try to load from config file
        try:
            if os.path.exists(self.config_path):
                with open(self.config_path, 'r') as f:
                    config = yaml.safe_load(f)
                    custom_rules = config.get('fix_rules', [])
                    for rule_data in custom_rules:
                        default_rules.append(AutoFixRule(**rule_data))
                logger.info(f"Loaded {len(custom_rules)} custom fix rules from {self.config_path}")
        except Exception as e:
            logger.warning(f"Could not load custom fix rules: {e}")
        
        logger.info(f"Loaded {len(default_rules)} total fix rules")
        return default_rules
    
    def analyze_semgrep_results(self, results_file: str) -> List[Dict[str, Any]]:
        """Analyze Semgrep results to identify fixable issues"""
        try:
            with open(results_file, 'r') as f:
                results = json.load(f)
            
            fixable_issues = []
            for result in results.get('results', []):
                rule_id = result.get('check_id', '')
                if any(rule.rule_id in rule_id for rule in self.fix_rules):
                    fixable_issues.append(result)
            
            logger.info(f"Found {len(fixable_issues)} fixable issues out of {len(results.get('results', []))} total issues")
            return fixable_issues
            
        except Exception as e:
            logger.error(f"Error analyzing Semgrep results: {e}")
            return []
    
    def apply_fixes_to_file(self, file_path: str, issues: List[Dict[str, Any]]) -> List[FixResult]:
        """Apply auto-fixes to a specific file with enhanced error handling"""
        fix_results = []
        
        try:
            # Validate file path and existence
            if not os.path.exists(file_path):
                error_msg = f"File does not exist: {file_path}"
                logger.error(error_msg)
                return [FixResult(
                    file_path=file_path,
                    line_number=0,
                    rule_id="",
                    original_code="",
                    fixed_code="",
                    fix_type="error",
                    confidence=0.0,
                    applied=False,
                    error_message=error_msg
                )]
            
            # Check file size
            file_size = os.path.getsize(file_path)
            max_size = 10 * 1024 * 1024  # 10MB limit
            if file_size > max_size:
                error_msg = f"File too large: {file_path} ({file_size} bytes)"
                logger.warning(error_msg)
                return [FixResult(
                    file_path=file_path,
                    line_number=0,
                    rule_id="",
                    original_code="",
                    fixed_code="",
                    fix_type="error",
                    confidence=0.0,
                    applied=False,
                    error_message=error_msg
                )]
            
            # Read file with encoding detection
            try:
                with open(file_path, 'r', encoding='utf-8') as f:
                    content = f.read()
            except UnicodeDecodeError:
                try:
                    with open(file_path, 'r', encoding='latin-1') as f:
                        content = f.read()
                except Exception as e:
                    error_msg = f"Failed to read file {file_path}: {e}"
                    logger.error(error_msg)
                    return [FixResult(
                        file_path=file_path,
                        line_number=0,
                        rule_id="",
                        original_code="",
                        fixed_code="",
                        fix_type="error",
                        confidence=0.0,
                        applied=False,
                        error_message=error_msg
                    )]
            
            original_content = content
            lines = content.split('\n')
            
            # Create backup before making changes
            backup_path = ""
            try:
                backup_path = self.create_backup(file_path)
            except Exception as e:
                logger.warning(f"Failed to create backup for {file_path}: {e}")
            
            # Sort issues by line number (descending) to avoid offset issues
            sorted_issues = sorted(issues, key=lambda x: x.get('start', {}).get('line', 0), reverse=True)
            
            for issue in sorted_issues:
                try:
                    rule_id = issue.get('check_id', '')
                    start_line = issue.get('start', {}).get('line', 0) - 1  # Convert to 0-based index
                    end_line = issue.get('end', {}).get('line', 0) - 1
                    
                    # Validate line numbers
                    if start_line < 0 or end_line >= len(lines) or start_line > end_line:
                        logger.warning(f"Invalid line numbers for {file_path}: {start_line}-{end_line}")
                        continue
                    
                    # Find matching fix rule
                    matching_rule = None
                    for rule in self.fix_rules:
                        if rule.rule_id in rule_id:
                            matching_rule = rule
                            break
                    
                    if not matching_rule:
                        continue
                    
                    # Extract the problematic code
                    if start_line < len(lines):
                        original_code = '\n'.join(lines[start_line:end_line + 1])
                        
                        # Apply the fix
                        fix_result = self._apply_single_fix(
                            file_path, start_line, end_line, original_code, 
                            matching_rule, lines
                        )
                        
                        if fix_result:
                            fix_results.append(fix_result)
                            if fix_result.applied:
                                # Update lines for next iteration
                                lines = content.split('\n')
                                
                except Exception as e:
                    logger.error(f"Error processing issue in {file_path}: {e}")
                    fix_results.append(FixResult(
                        file_path=file_path,
                        line_number=issue.get('start', {}).get('line', 0),
                        rule_id=issue.get('check_id', ''),
                        original_code="",
                        fixed_code="",
                        fix_type="error",
                        confidence=0.0,
                        applied=False,
                        error_message=str(e)
                    ))
            
            # Write back the fixed content with error handling
            if any(result.applied for result in fix_results):
                try:
                    # Create temporary file first
                    temp_file = f"{file_path}.tmp"
                    with open(temp_file, 'w', encoding='utf-8') as f:
                        f.write('\n'.join(lines))
                    
                    # Verify the temporary file
                    with open(temp_file, 'r', encoding='utf-8') as f:
                        temp_content = f.read()
                    
                    # Replace original file
                    os.replace(temp_file, file_path)
                    
                    logger.info(f"Applied {len([r for r in fix_results if r.applied])} fixes to {file_path}")
                    
                except Exception as e:
                    error_msg = f"Failed to write file {file_path}: {e}"
                    logger.error(error_msg)
                    
                    # Restore from backup if available
                    if backup_path and os.path.exists(backup_path):
                        try:
                            os.replace(backup_path, file_path)
                            logger.info(f"Restored {file_path} from backup")
                        except Exception as restore_error:
                            logger.error(f"Failed to restore from backup: {restore_error}")
                    
                    return [FixResult(
                        file_path=file_path,
                        line_number=0,
                        rule_id="",
                        original_code="",
                        fixed_code="",
                        fix_type="error",
                        confidence=0.0,
                        applied=False,
                        error_message=error_msg
                    )]
            
        except Exception as e:
            error_msg = f"Unexpected error processing {file_path}: {e}"
            logger.error(error_msg)
            fix_results.append(FixResult(
                file_path=file_path,
                line_number=0,
                rule_id="",
                original_code="",
                fixed_code="",
                fix_type="error",
                confidence=0.0,
                applied=False,
                error_message=error_msg
            ))
        
        return fix_results
    
    def _apply_single_fix(self, file_path: str, start_line: int, end_line: int, 
                         original_code: str, rule: AutoFixRule, lines: List[str]) -> Optional[FixResult]:
        """Apply a single fix to the code with enhanced validation"""
        try:
            # Validate fix before applying
            if not self._validate_fix_rule(rule, original_code):
                logger.warning(f"Fix validation failed for rule {rule.rule_id}")
                return FixResult(
                    file_path=file_path,
                    line_number=start_line + 1,
                    rule_id=rule.rule_id,
                    original_code=original_code,
                    fixed_code=original_code,
                    fix_type=rule.category,
                    confidence=0.0,
                    applied=False,
                    error_message="Fix validation failed"
                )
            
            # Create the fixed code
            if rule.category == "nonce-verification":
                fixed_code = self._fix_nonce_verification(original_code, rule)
            elif rule.category == "sanitization":
                fixed_code = self._fix_sanitization(original_code, rule)
            elif rule.category == "output-escaping":
                fixed_code = self._fix_output_escaping(original_code, rule)
            elif rule.category == "database-security":
                fixed_code = self._fix_database_query(original_code, rule)
            elif rule.category == "capability-checks":
                fixed_code = self._fix_capability_check(original_code, rule)
            elif rule.category == "ajax-security":
                fixed_code = self._fix_ajax_security(original_code, rule)
            else:
                # Generic pattern replacement
                fixed_code = re.sub(rule.pattern, rule.replacement, original_code)
            
            # Validate the generated fix
            if not self._validate_generated_fix(original_code, fixed_code, rule):
                logger.warning(f"Generated fix validation failed for rule {rule.rule_id}")
                return FixResult(
                    file_path=file_path,
                    line_number=start_line + 1,
                    rule_id=rule.rule_id,
                    original_code=original_code,
                    fixed_code=original_code,
                    fix_type=rule.category,
                    confidence=0.0,
                    applied=False,
                    error_message="Generated fix validation failed"
                )
            
            if fixed_code != original_code:
                # Update the lines
                lines[start_line:end_line + 1] = fixed_code.split('\n')
                
                return FixResult(
                    file_path=file_path,
                    line_number=start_line + 1,
                    rule_id=rule.rule_id,
                    original_code=original_code,
                    fixed_code=fixed_code,
                    fix_type=rule.category,
                    confidence=rule.confidence,
                    applied=True
                )
            
        except Exception as e:
            logger.error(f"Error applying fix {rule.rule_id}: {e}")
            return FixResult(
                file_path=file_path,
                line_number=start_line + 1,
                rule_id=rule.rule_id,
                original_code=original_code,
                fixed_code=original_code,
                fix_type=rule.category,
                confidence=0.0,
                applied=False,
                error_message=str(e)
            )
        
        return None
    
    def _fix_nonce_verification(self, code: str, rule: AutoFixRule) -> str:
        """Fix missing nonce verification"""
        # Extract action name from the code context
        action_match = re.search(r'[\'"]([^\'"]+)[\'"]', code)
        action_name = action_match.group(1) if action_match else 'my_action'
        
        # Create nonce field and verification
        nonce_field = f'<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce(\'{action_name}\'); ?>" />'
        verification_code = f'if (!wp_verify_nonce($_POST[\'_wpnonce\'], \'{action_name}\')) {{\n    wp_die(__(\'Security check failed.\'));\n}}'
        
        # Add verification before the original code
        return f'{verification_code}\n{code}'
    
    def _fix_sanitization(self, code: str, rule: AutoFixRule) -> str:
        """Fix missing input sanitization"""
        # Detect input type and apply appropriate sanitization
        if '$_POST' in code:
            return re.sub(r'(\$\w+)\s*=\s*(\$_\w+\[\s*[\'"][^\'"]+[\'"]\s*\])', 
                         r'\1 = sanitize_text_field(\2)', code)
        elif '$_GET' in code:
            return re.sub(r'(\$\w+)\s*=\s*(\$_\w+\[\s*[\'"][^\'"]+[\'"]\s*\])', 
                         r'\1 = sanitize_text_field(\2)', code)
        elif '$_REQUEST' in code:
            return re.sub(r'(\$\w+)\s*=\s*(\$_\w+\[\s*[\'"][^\'"]+[\'"]\s*\])', 
                         r'\1 = sanitize_text_field(\2)', code)
        
        return code
    
    def _fix_output_escaping(self, code: str, rule: AutoFixRule) -> str:
        """Fix unsafe output"""
        # Add appropriate escaping based on context
        if 'echo' in code:
            return re.sub(r'echo\s+(\$\w+)', r'echo esc_html(\1)', code)
        elif 'print' in code:
            return re.sub(r'print\s+(\$\w+)', r'print esc_html(\1)', code)
        
        return code
    
    def _fix_database_query(self, code: str, rule: AutoFixRule) -> str:
        """Fix unsafe database queries"""
        # Convert direct queries to prepared statements
        if '$wpdb->query' in code:
            # Extract the query and variables
            query_match = re.search(r'\$wpdb->query\s*\(\s*[\'"]([^\'"]*)[\'"]\s*\)', code)
            if query_match:
                query = query_match.group(1)
                # Simple conversion - in practice, this would be more sophisticated
                return code.replace('$wpdb->query', '$wpdb->prepare')
        
        return code
    
    def _fix_capability_check(self, code: str, rule: AutoFixRule) -> str:
        """Fix missing capability checks"""
        capability_check = '''if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}'''
        
        return f'{capability_check}\n{code}'
    
    def _fix_ajax_security(self, code: str, rule: AutoFixRule) -> str:
        """Fix AJAX security issues"""
        nonce_check = '''check_ajax_referer('my_nonce_action', 'nonce');'''
        
        return f'{nonce_check}\n{code}'
    
    def _validate_fix_rule(self, rule: AutoFixRule, original_code: str) -> bool:
        """Validate fix rule before application"""
        try:
            # Check if rule pattern matches the original code
            if not re.search(rule.pattern, original_code, re.MULTILINE | re.DOTALL):
                logger.warning(f"Rule pattern does not match original code for {rule.rule_id}")
                return False
            
            # Validate rule confidence
            if rule.confidence < 0.5:
                logger.warning(f"Rule confidence too low for {rule.rule_id}: {rule.confidence}")
                return False
            
            # Check for dangerous patterns in replacement
            dangerous_patterns = [
                r'eval\s*\(',
                r'exec\s*\(',
                r'system\s*\(',
                r'shell_exec\s*\(',
                r'passthru\s*\(',
                r'`.*`',  # Backticks for command execution
            ]
            
            for pattern in dangerous_patterns:
                if re.search(pattern, rule.replacement, re.IGNORECASE):
                    logger.error(f"Dangerous pattern found in replacement for {rule.rule_id}")
                    return False
            
            # Validate category-specific rules
            if rule.category == "database-security":
                if not self._validate_database_fix(rule, original_code):
                    return False
            elif rule.category == "nonce-verification":
                if not self._validate_nonce_fix(rule, original_code):
                    return False
            
            return True
            
        except Exception as e:
            logger.error(f"Error validating fix rule {rule.rule_id}: {e}")
            return False
    
    def _validate_generated_fix(self, original_code: str, fixed_code: str, rule: AutoFixRule) -> bool:
        """Validate generated fix after creation"""
        try:
            # Check if fix actually changed the code
            if original_code.strip() == fixed_code.strip():
                logger.warning(f"Fix did not change code for {rule.rule_id}")
                return False
            
            # Check for syntax errors (basic PHP syntax check)
            if not self._validate_php_syntax(fixed_code):
                logger.error(f"Generated fix has syntax errors for {rule.rule_id}")
                return False
            
            # Check for security improvements
            if not self._validate_security_improvement(original_code, fixed_code, rule.category):
                logger.warning(f"Fix may not improve security for {rule.rule_id}")
                return False
            
            # Check for dangerous patterns in fixed code
            dangerous_patterns = [
                r'eval\s*\(',
                r'exec\s*\(',
                r'system\s*\(',
                r'shell_exec\s*\(',
                r'passthru\s*\(',
                r'`.*`',
                r'include\s*\$',
                r'require\s*\$',
            ]
            
            for pattern in dangerous_patterns:
                if re.search(pattern, fixed_code, re.IGNORECASE):
                    logger.error(f"Dangerous pattern found in fixed code for {rule.rule_id}")
                    return False
            
            return True
            
        except Exception as e:
            logger.error(f"Error validating generated fix for {rule.rule_id}: {e}")
            return False
    
    def _validate_database_fix(self, rule: AutoFixRule, original_code: str) -> bool:
        """Validate database-related fixes"""
        # Check if the fix properly uses prepared statements
        if '$wpdb->prepare' not in rule.replacement and '$wpdb->query' in original_code:
            logger.warning(f"Database fix should use prepared statements for {rule.rule_id}")
            return False
        return True
    
    def _validate_nonce_fix(self, rule: AutoFixRule, original_code: str) -> bool:
        """Validate nonce-related fixes"""
        # Check if the fix includes proper nonce verification
        if 'wp_verify_nonce' not in rule.replacement and 'check_ajax_referer' not in rule.replacement:
            logger.warning(f"Nonce fix should include verification for {rule.rule_id}")
            return False
        return True
    
    def _validate_php_syntax(self, code: str) -> bool:
        """Basic PHP syntax validation"""
        try:
            # Check for balanced braces and parentheses
            brace_count = code.count('{') - code.count('}')
            paren_count = code.count('(') - code.count(')')
            
            if brace_count != 0 or paren_count != 0:
                logger.warning("Unbalanced braces or parentheses in generated code")
                return False
            
            # Check for basic PHP structure
            if '<?php' in code and not code.strip().endswith('?>') and not code.strip().endswith(';'):
                # This is a basic check - in production, use a proper PHP parser
                pass
            
            return True
            
        except Exception as e:
            logger.error(f"Error validating PHP syntax: {e}")
            return False
    
    def _validate_security_improvement(self, original_code: str, fixed_code: str, category: str) -> bool:
        """Validate that the fix actually improves security"""
        try:
            if category == "sanitization":
                # Check if sanitization functions were added
                sanitization_functions = ['sanitize_text_field', 'sanitize_email', 'sanitize_url', 'intval', 'floatval']
                original_has_sanitization = any(func in original_code for func in sanitization_functions)
                fixed_has_sanitization = any(func in fixed_code for func in sanitization_functions)
                
                if not original_has_sanitization and fixed_has_sanitization:
                    return True
                return False
                
            elif category == "output-escaping":
                # Check if escaping functions were added
                escaping_functions = ['esc_html', 'esc_attr', 'esc_url', 'esc_js', 'wp_kses_post']
                original_has_escaping = any(func in original_code for func in escaping_functions)
                fixed_has_escaping = any(func in fixed_code for func in escaping_functions)
                
                if not original_has_escaping and fixed_has_escaping:
                    return True
                return False
                
            elif category == "nonce-verification":
                # Check if nonce verification was added
                nonce_functions = ['wp_verify_nonce', 'check_ajax_referer']
                original_has_nonce = any(func in original_code for func in nonce_functions)
                fixed_has_nonce = any(func in fixed_code for func in nonce_functions)
                
                if not original_has_nonce and fixed_has_nonce:
                    return True
                return False
                
            elif category == "database-security":
                # Check if prepared statements were added
                if '$wpdb->query' in original_code and '$wpdb->prepare' in fixed_code:
                    return True
                return False
            
            return True  # Default to allowing the fix
            
        except Exception as e:
            logger.error(f"Error validating security improvement: {e}")
            return False
    
    def create_backup(self, file_path: str) -> str:
        """Create a backup of the original file"""
        backup_path = f"{file_path}.backup.{datetime.now().strftime('%Y%m%d_%H%M%S')}"
        try:
            with open(file_path, 'r', encoding='utf-8') as src:
                with open(backup_path, 'w', encoding='utf-8') as dst:
                    dst.write(src.read())
            logger.info(f"Created backup: {backup_path}")
            return backup_path
        except Exception as e:
            logger.error(f"Failed to create backup: {e}")
            return ""
    
    def generate_fix_report(self, fix_results: List[FixResult], output_file: str = "auto-fix-report.json"):
        """Generate a detailed report of applied fixes"""
        report = {
            'timestamp': datetime.now().isoformat(),
            'summary': {
                'total_issues': len(fix_results),
                'fixes_applied': len([r for r in fix_results if r.applied]),
                'fixes_skipped': len([r for r in fix_results if not r.applied]),
                'errors': len([r for r in fix_results if r.error_message])
            },
            'fixes_by_category': {},
            'fixes_by_file': {},
            'detailed_results': []
        }
        
        # Group fixes by category
        for result in fix_results:
            category = result.fix_type
            if category not in report['fixes_by_category']:
                report['fixes_by_category'][category] = 0
            report['fixes_by_category'][category] += 1
            
            # Group fixes by file
            if result.file_path not in report['fixes_by_file']:
                report['fixes_by_file'][result.file_path] = []
            report['fixes_by_file'][result.file_path].append({
                'line': result.line_number,
                'rule_id': result.rule_id,
                'confidence': result.confidence,
                'applied': result.applied
            })
            
            # Detailed results
            report['detailed_results'].append({
                'file_path': result.file_path,
                'line_number': result.line_number,
                'rule_id': result.rule_id,
                'fix_type': result.fix_type,
                'confidence': result.confidence,
                'applied': result.applied,
                'original_code': result.original_code,
                'fixed_code': result.fixed_code,
                'error_message': result.error_message
            })
        
        # Write report
        with open(output_file, 'w') as f:
            json.dump(report, f, indent=2)
        
        logger.info(f"Generated fix report: {output_file}")
        return report

def main():
    """Main entry point for the auto-fix system"""
    parser = argparse.ArgumentParser(description='WordPress Semgrep Rules Auto-fix System')
    parser.add_argument('--results', required=True, help='Path to Semgrep results JSON file')
    parser.add_argument('--backup', action='store_true', help='Create backups before applying fixes')
    parser.add_argument('--dry-run', action='store_true', help='Show what would be fixed without applying changes')
    parser.add_argument('--output', default='auto-fix-report.json', help='Output report file')
    parser.add_argument('--config', help='Path to auto-fix configuration file')
    parser.add_argument('--verbose', action='store_true', help='Enable verbose output')
    
    args = parser.parse_args()
    
    if args.verbose:
        logging.getLogger().setLevel(logging.DEBUG)
    
    # Initialize auto-fixer
    fixer = WordPressAutoFixer(config_path=args.config)
    
    # Analyze Semgrep results
    fixable_issues = fixer.analyze_semgrep_results(args.results)
    
    if not fixable_issues:
        logger.info("No fixable issues found")
        return
    
    # Group issues by file
    issues_by_file = {}
    for issue in fixable_issues:
        file_path = issue.get('path', {}).get('value', '')
        if file_path not in issues_by_file:
            issues_by_file[file_path] = []
        issues_by_file[file_path].append(issue)
    
    # Apply fixes
    all_fix_results = []
    for file_path, issues in issues_by_file.items():
        if not os.path.exists(file_path):
            logger.warning(f"File not found: {file_path}")
            continue
        
        if args.backup and not args.dry_run:
            fixer.create_backup(file_path)
        
        if args.dry_run:
            logger.info(f"Would apply {len(issues)} fixes to {file_path}")
            # Create mock results for dry run
            for issue in issues:
                all_fix_results.append(FixResult(
                    file_path=file_path,
                    line_number=issue.get('start', {}).get('line', 0),
                    rule_id=issue.get('check_id', ''),
                    original_code="",
                    fixed_code="",
                    fix_type="dry-run",
                    confidence=0.8,
                    applied=False
                ))
        else:
            fix_results = fixer.apply_fixes_to_file(file_path, issues)
            all_fix_results.extend(fix_results)
    
    # Generate report
    report = fixer.generate_fix_report(all_fix_results, args.output)
    
    # Print summary
    print(f"\n{'='*60}")
    print("AUTO-FIX SUMMARY")
    print(f"{'='*60}")
    print(f"Total issues analyzed: {len(fixable_issues)}")
    print(f"Fixes applied: {report['summary']['fixes_applied']}")
    print(f"Fixes skipped: {report['summary']['fixes_skipped']}")
    print(f"Errors: {report['summary']['errors']}")
    
    if report['fixes_by_category']:
        print(f"\nFixes by category:")
        for category, count in report['fixes_by_category'].items():
            print(f"  {category}: {count}")
    
    print(f"\nDetailed report saved to: {args.output}")

if __name__ == '__main__':
    main()
