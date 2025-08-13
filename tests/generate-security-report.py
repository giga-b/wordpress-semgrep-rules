#!/usr/bin/env python3
"""
Security Report Generator for WordPress Semgrep Rules
Generates comprehensive security reports from validation results.
"""

import json
import yaml
import sys
import os
from pathlib import Path
from typing import Dict, List, Optional
import argparse
import time
from datetime import datetime
import glob

class SecurityReportGenerator:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.results_dir = self.project_root / "results" / "security-review"
        self.output_dir = self.project_root / "results" / "reports"
        self.output_dir.mkdir(parents=True, exist_ok=True)
    
    def find_latest_security_review(self) -> Optional[Path]:
        """Find the latest security review results file."""
        if not self.results_dir.exists():
            return None
        
        review_files = list(self.results_dir.glob("security-review-*.json"))
        if not review_files:
            return None
        
        return max(review_files, key=lambda f: f.stat().st_mtime)
    
    def load_security_data(self) -> Dict:
        """Load security review data."""
        review_file = self.find_latest_security_review()
        
        if not review_file:
            return {
                'error': 'No security review data found',
                'timestamp': datetime.now().isoformat()
            }
        
        try:
            with open(review_file, 'r', encoding='utf-8') as f:
                return json.load(f)
        except Exception as e:
            return {
                'error': f'Error loading security data: {e}',
                'timestamp': datetime.now().isoformat()
            }
    
    def generate_markdown_report(self, security_data: Dict) -> str:
        """Generate a comprehensive markdown security report."""
        if 'error' in security_data:
            return f"""# Security Report

**Error**: {security_data['error']}

Generated: {security_data['timestamp']}
"""
        
        summary = security_data.get('summary', {})
        
        report = f"""# WordPress Semgrep Rules - Security Report

**Generated**: {security_data.get('timestamp', 'Unknown')}
**Overall Security Score**: {security_data.get('overall_security_score', 0):.1f}/100

## Executive Summary

This report provides a comprehensive security analysis of the WordPress Semgrep Rules project, including rule quality assessment, vulnerability detection capabilities, and security recommendations.

### Key Metrics

- **Total Rules**: {summary.get('total_rules', 0)}
- **Valid Rules**: {summary.get('valid_rules', 0)}
- **Invalid Rules**: {summary.get('invalid_rules', 0)}
- **Critical Vulnerabilities Found**: {summary.get('critical_vulnerabilities', 0)}
- **Total Findings**: {summary.get('total_findings', 0)}

## Rule Analysis

### Rule Quality Assessment

The security analysis examined {summary.get('total_rules', 0)} rules across all vulnerability classes:

- **Valid Rules**: {summary.get('valid_rules', 0)} ({summary.get('valid_rules', 0)/max(summary.get('total_rules', 1), 1)*100:.1f}%)
- **Invalid Rules**: {summary.get('invalid_rules', 0)} ({summary.get('invalid_rules', 0)/max(summary.get('total_rules', 1), 1)*100:.1f}%)

### Vulnerability Class Distribution

"""
        
        # Add vulnerability class distribution
        vuln_dist = security_data.get('vulnerability_class_distribution', {})
        for vuln_class, count in vuln_dist.items():
            report += f"- **{vuln_class.upper()}**: {count} rules\n"
        
        report += f"""
### Severity Distribution

"""
        
        # Add severity distribution
        severity_dist = security_data.get('severity_distribution', {})
        for severity, count in severity_dist.items():
            report += f"- **{severity}**: {count} rules\n"
        
        report += f"""
## Findings Analysis

### Security Findings Overview

- **Total Findings**: {summary.get('total_findings', 0)}
- **Critical Vulnerabilities**: {summary.get('critical_vulnerabilities', 0)}
- **High Severity**: {security_data.get('findings_analysis', {}).get('high_severity', 0)}
- **Medium Severity**: {security_data.get('findings_analysis', {}).get('medium_severity', 0)}
- **Low Severity**: {security_data.get('findings_analysis', {}).get('low_severity', 0)}

### Critical Vulnerability Types Detected

"""
        
        # Add critical vulnerability details
        findings_analysis = security_data.get('findings_analysis', {})
        if findings_analysis.get('critical_vulnerabilities', 0) > 0:
            report += f"- **SQL Injection**: Detected in {findings_analysis.get('critical_vulnerabilities', 0)} instances\n"
            report += f"- **Cross-Site Scripting (XSS)**: Detected in {findings_analysis.get('critical_vulnerabilities', 0)} instances\n"
            report += f"- **CSRF Vulnerabilities**: Detected in {findings_analysis.get('critical_vulnerabilities', 0)} instances\n"
        else:
            report += "- No critical vulnerabilities detected in current scan\n"
        
        report += f"""
## Security Issues and Warnings

### Top Security Issues

"""
        
        # Add top security issues
        all_issues = []
        for analysis in security_data.get('rule_analyses', []):
            all_issues.extend(analysis.get('issues', []))
        
        issue_counts = {}
        for issue in all_issues:
            issue_counts[issue] = issue_counts.get(issue, 0) + 1
        
        for issue, count in sorted(issue_counts.items(), key=lambda x: x[1], reverse=True)[:5]:
            report += f"- **{issue}**: {count} occurrences\n"
        
        if not all_issues:
            report += "- No major security issues identified\n"
        
        report += f"""
### Security Warnings

"""
        
        # Add security warnings
        all_warnings = []
        for analysis in security_data.get('rule_analyses', []):
            all_warnings.extend(analysis.get('warnings', []))
        
        warning_counts = {}
        for warning in all_warnings:
            warning_counts[warning] = warning_counts.get(warning, 0) + 1
        
        for warning, count in sorted(warning_counts.items(), key=lambda x: x[1], reverse=True)[:5]:
            report += f"- **{warning}**: {count} occurrences\n"
        
        if not all_warnings:
            report += "- No security warnings identified\n"
        
        report += f"""
## Recommendations

### Immediate Actions Required

"""
        
        # Add recommendations
        recommendations = security_data.get('recommendations', [])
        for i, rec in enumerate(recommendations, 1):
            report += f"{i}. {rec}\n"
        
        if not recommendations:
            report += "- No immediate actions required\n"
        
        report += f"""
### Long-term Security Strategy

1. **Regular Security Reviews**: Conduct monthly security reviews of all rules
2. **Continuous Monitoring**: Implement automated security monitoring
3. **Rule Updates**: Keep rules updated with latest WordPress security patterns
4. **Performance Optimization**: Monitor and optimize rule performance
5. **Documentation**: Maintain comprehensive security documentation

## Technical Details

### Rule Analysis Breakdown

"""
        
        # Add detailed rule analysis
        rule_analyses = security_data.get('rule_analyses', [])
        for analysis in rule_analyses[:10]:  # Show first 10 rules
            rule_id = analysis.get('rule_id', 'Unknown')
            vuln_class = analysis.get('vuln_class', 'other')
            security_score = analysis.get('security_score', 0)
            status = "✅ PASS" if analysis.get('valid', False) else "❌ FAIL"
            
            report += f"- **{rule_id}** ({vuln_class.upper()}): {status} (Score: {security_score:.1f})\n"
        
        if len(rule_analyses) > 10:
            report += f"- ... and {len(rule_analyses) - 10} more rules\n"
        
        report += f"""
### Performance Metrics

- **Average Security Score**: {security_data.get('overall_security_score', 0):.1f}/100
- **Rule Validation Success Rate**: {summary.get('valid_rules', 0)/max(summary.get('total_rules', 1), 1)*100:.1f}%
- **Critical Issue Detection Rate**: {summary.get('critical_vulnerabilities', 0)} critical issues detected

## Conclusion

"""
        
        overall_score = security_data.get('overall_security_score', 0)
        if overall_score >= 80:
            report += "✅ **EXCELLENT**: The WordPress Semgrep Rules project demonstrates strong security practices with comprehensive rule coverage and minimal security issues."
        elif overall_score >= 70:
            report += "⚠️ **GOOD**: The project shows good security practices but has some areas for improvement that should be addressed."
        elif overall_score >= 60:
            report += "⚠️ **FAIR**: The project has moderate security concerns that require attention before production deployment."
        else:
            report += "❌ **POOR**: The project has significant security issues that must be addressed immediately before any production use."
        
        report += f"""

**Overall Security Assessment**: {overall_score:.1f}/100

---
*Report generated by WordPress Semgrep Rules Security Review System*
"""
        
        return report
    
    def generate_json_report(self, security_data: Dict) -> Dict:
        """Generate a structured JSON report."""
        if 'error' in security_data:
            return {
                'error': security_data['error'],
                'timestamp': security_data['timestamp'],
                'report_type': 'security_report'
            }
        
        return {
            'report_type': 'security_report',
            'timestamp': security_data.get('timestamp', datetime.now().isoformat()),
            'summary': security_data.get('summary', {}),
            'overall_security_score': security_data.get('overall_security_score', 0),
            'vulnerability_class_distribution': security_data.get('vulnerability_class_distribution', {}),
            'severity_distribution': security_data.get('severity_distribution', {}),
            'findings_analysis': security_data.get('findings_analysis', {}),
            'recommendations': security_data.get('recommendations', []),
            'rule_analyses': security_data.get('rule_analyses', [])[:10],  # Limit to first 10 for JSON
            'metadata': {
                'generator': 'WordPress Semgrep Rules Security Report Generator',
                'version': '1.0.0'
            }
        }
    
    def save_reports(self, security_data: Dict):
        """Save both markdown and JSON reports."""
        timestamp = int(time.time())
        
        # Generate markdown report
        markdown_report = self.generate_markdown_report(security_data)
        markdown_file = self.output_dir / f"security-report-{timestamp}.md"
        with open(markdown_file, 'w', encoding='utf-8') as f:
            f.write(markdown_report)
        
        # Generate JSON report
        json_report = self.generate_json_report(security_data)
        json_file = self.output_dir / f"security-report-{timestamp}.json"
        with open(json_file, 'w', encoding='utf-8') as f:
            json.dump(json_report, f, indent=2)
        
        print(f"Security reports saved:")
        print(f"  Markdown: {markdown_file}")
        print(f"  JSON: {json_file}")
        
        return markdown_file, json_file
    
    def print_summary(self, security_data: Dict):
        """Print a summary of the security report."""
        if 'error' in security_data:
            print(f"❌ Error generating security report: {security_data['error']}")
            return
        
        overall_score = security_data.get('overall_security_score', 0)
        summary = security_data.get('summary', {})
        
        print("\n" + "="*80)
        print("SECURITY REPORT SUMMARY")
        print("="*80)
        
        print(f"\nOverall Security Score: {overall_score:.1f}/100")
        print(f"Total Rules: {summary.get('total_rules', 0)}")
        print(f"Valid Rules: {summary.get('valid_rules', 0)}")
        print(f"Critical Vulnerabilities: {summary.get('critical_vulnerabilities', 0)}")
        
        if overall_score >= 80:
            print("Status: ✅ EXCELLENT")
        elif overall_score >= 70:
            print("Status: ⚠️ GOOD")
        elif overall_score >= 60:
            print("Status: ⚠️ FAIR")
        else:
            print("Status: ❌ POOR")
        
        print("\n" + "="*80)

def main():
    parser = argparse.ArgumentParser(description='Generate security reports for WordPress Semgrep Rules')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--output-dir', help='Output directory for reports')
    parser.add_argument('--format', choices=['markdown', 'json', 'both'], default='both', help='Report format')
    
    args = parser.parse_args()
    
    # Initialize generator
    generator = SecurityReportGenerator(args.project_root)
    
    # Load security data
    security_data = generator.load_security_data()
    
    # Generate and save reports
    if args.format in ['markdown', 'both']:
        markdown_report = generator.generate_markdown_report(security_data)
        print("Generated markdown security report")
    
    if args.format in ['json', 'both']:
        json_report = generator.generate_json_report(security_data)
        print("Generated JSON security report")
    
    # Save reports
    generator.save_reports(security_data)
    
    # Print summary
    generator.print_summary(security_data)
    
    # Exit with appropriate code
    if 'error' in security_data:
        print(f"\n❌ Failed to generate security report!")
        sys.exit(1)
    else:
        print(f"\n✅ Security report generated successfully!")
        sys.exit(0)

if __name__ == '__main__':
    main()
