#!/usr/bin/env python3
"""
Security Review Script for WordPress Semgrep Rules
Performs comprehensive security analysis of rules and findings.
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
import re

class SecurityReviewer:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.results_dir = self.project_root / "results" / "security-review"
        self.results_dir.mkdir(parents=True, exist_ok=True)
        
        # Load quality configuration
        self.quality_config = self.project_root / ".rule-quality.yml"
        if self.quality_config.exists():
            with open(self.quality_config, 'r', encoding='utf-8') as f:
                self.config = yaml.safe_load(f)
        else:
            self.config = {}
    
    def get_rule_files(self) -> List[Path]:
        """Get all rule files for security review."""
        rule_files = []
        
        # Scan all rule packs
        rule_packs = ['packs/wp-core-security', 'packs/wp-core-quality', 'packs/experimental']
        
        for pack in rule_packs:
            pack_path = self.project_root / pack
            if pack_path.exists():
                rule_files.extend(pack_path.glob('*.yaml'))
        
        return rule_files
    
    def analyze_rule_security(self, rule_file: Path) -> Dict:
        """Analyze security aspects of a single rule."""
        try:
            with open(rule_file, 'r', encoding='utf-8') as f:
                content = yaml.safe_load(f)
            
            if not content or 'rules' not in content:
                return {
                    'rule_file': str(rule_file),
                    'valid': False,
                    'error': 'No rules found in file',
                    'security_score': 0
                }
            
            rule = content['rules'][0]
            metadata = rule.get('metadata', {})
            
            # Security analysis
            security_analysis = {
                'rule_file': str(rule_file),
                'rule_id': rule.get('id', ''),
                'severity': rule.get('severity', ''),
                'confidence': metadata.get('confidence', 'medium'),
                'cwe': metadata.get('cwe', ''),
                'vuln_class': metadata.get('vuln_class', 'other'),
                'security_score': 0,
                'issues': [],
                'warnings': [],
                'recommendations': []
            }
            
            # Check severity appropriateness
            vuln_class = metadata.get('vuln_class', 'other')
            severity = rule.get('severity', '')
            
            if vuln_class in ['sqli', 'xss', 'csrf'] and severity != 'ERROR':
                security_analysis['warnings'].append(
                    f"High-risk vulnerability class '{vuln_class}' should have ERROR severity"
                )
                security_analysis['security_score'] -= 10
            
            # Check confidence level
            confidence = metadata.get('confidence', 'medium')
            if confidence != 'high':
                security_analysis['warnings'].append(
                    f"Confidence should be 'high' for production rules, got '{confidence}'"
                )
                security_analysis['security_score'] -= 5
            
            # Check CWE mapping
            cwe = metadata.get('cwe', '')
            if not cwe.startswith('CWE-'):
                security_analysis['issues'].append(
                    f"Invalid CWE format: {cwe}"
                )
                security_analysis['security_score'] -= 15
            
            # Check pattern security
            patterns = rule.get('patterns', [])
            if not patterns:
                security_analysis['issues'].append("No patterns defined")
                security_analysis['security_score'] -= 20
            else:
                # Analyze pattern complexity and security
                pattern_analysis = self.analyze_patterns(patterns)
                security_analysis['pattern_analysis'] = pattern_analysis
                security_analysis['security_score'] += pattern_analysis.get('score', 0)
            
            # Check message quality
            message = rule.get('message', '')
            if not message or len(message) < 10:
                security_analysis['warnings'].append("Message too short or missing")
                security_analysis['security_score'] -= 5
            
            # Generate recommendations
            if security_analysis['security_score'] < 70:
                security_analysis['recommendations'].append(
                    "Consider improving rule quality before production use"
                )
            
            if confidence != 'high':
                security_analysis['recommendations'].append(
                    "Increase confidence to 'high' for production rules"
                )
            
            if vuln_class in ['sqli', 'xss'] and severity != 'ERROR':
                security_analysis['recommendations'].append(
                    f"Consider ERROR severity for {vuln_class} vulnerabilities"
                )
            
            # Ensure score doesn't go below 0
            security_analysis['security_score'] = max(0, security_analysis['security_score'])
            security_analysis['valid'] = len(security_analysis['issues']) == 0
            
            return security_analysis
            
        except Exception as e:
            return {
                'rule_file': str(rule_file),
                'valid': False,
                'error': str(e),
                'security_score': 0
            }
    
    def analyze_patterns(self, patterns: List) -> Dict:
        """Analyze pattern security and complexity."""
        analysis = {
            'total_patterns': len(patterns),
            'complex_patterns': 0,
            'simple_patterns': 0,
            'score': 0,
            'issues': []
        }
        
        for pattern in patterns:
            pattern_str = str(pattern)
            
            # Check for overly complex patterns
            if len(pattern_str) > 500:
                analysis['complex_patterns'] += 1
                analysis['issues'].append("Pattern is very complex (>500 chars)")
                analysis['score'] -= 5
            
            # Check for potential regex issues
            if 'regex:' in pattern_str:
                analysis['complex_patterns'] += 1
                # Check for potential regex DoS patterns
                if re.search(r'\(.*\+.*\)', pattern_str):
                    analysis['issues'].append("Potential regex DoS pattern detected")
                    analysis['score'] -= 10
            
            # Check for simple patterns (good)
            if len(pattern_str) < 100:
                analysis['simple_patterns'] += 1
                analysis['score'] += 5
        
        return analysis
    
    def analyze_findings_security(self, findings_file: Optional[Path] = None) -> Dict:
        """Analyze security implications of findings."""
        analysis = {
            'total_findings': 0,
            'high_severity': 0,
            'medium_severity': 0,
            'low_severity': 0,
            'critical_vulnerabilities': 0,
            'security_score': 0,
            'issues': [],
            'recommendations': []
        }
        
        # Try to find recent findings
        if findings_file is None:
            # Look for recent findings files
            results_dir = self.project_root / "results"
            if results_dir.exists():
                findings_files = list(results_dir.rglob("*findings*.json"))
                if findings_files:
                    findings_file = max(findings_files, key=lambda f: f.stat().st_mtime)
        
        if findings_file and findings_file.exists():
            try:
                with open(findings_file, 'r', encoding='utf-8') as f:
                    findings_data = json.load(f)
                
                findings = findings_data.get('results', [])
                analysis['total_findings'] = len(findings)
                
                for finding in findings:
                    severity = finding.get('extra', {}).get('severity', '')
                    
                    if severity == 'ERROR':
                        analysis['high_severity'] += 1
                        analysis['security_score'] -= 10
                    elif severity == 'WARNING':
                        analysis['medium_severity'] += 1
                        analysis['security_score'] -= 5
                    elif severity == 'INFO':
                        analysis['low_severity'] += 1
                        analysis['security_score'] -= 1
                    
                    # Check for critical vulnerability types
                    message = finding.get('extra', {}).get('message', '').lower()
                    if any(vuln in message for vuln in ['sql injection', 'xss', 'csrf']):
                        analysis['critical_vulnerabilities'] += 1
                        analysis['security_score'] -= 15
                
                # Generate recommendations based on findings
                if analysis['critical_vulnerabilities'] > 0:
                    analysis['recommendations'].append(
                        f"Found {analysis['critical_vulnerabilities']} critical vulnerabilities - immediate attention required"
                    )
                
                if analysis['high_severity'] > 10:
                    analysis['recommendations'].append(
                        f"High number of high-severity findings ({analysis['high_severity']}) - review security posture"
                    )
                
            except Exception as e:
                analysis['issues'].append(f"Error analyzing findings: {e}")
        
        return analysis
    
    def run_security_review(self) -> Dict:
        """Run comprehensive security review."""
        print("Starting security review...")
        
        # Analyze rules
        print("Analyzing rule security...")
        rule_files = self.get_rule_files()
        rule_analyses = []
        
        for rule_file in rule_files:
            analysis = self.analyze_rule_security(rule_file)
            rule_analyses.append(analysis)
        
        # Analyze findings
        print("Analyzing findings security...")
        findings_analysis = self.analyze_findings_security()
        
        # Generate overall report
        print("Generating security report...")
        report = self.generate_security_report(rule_analyses, findings_analysis)
        
        # Save report
        timestamp = int(time.time())
        report_file = self.results_dir / f"security-review-{timestamp}.json"
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2)
        
        return report
    
    def generate_security_report(self, rule_analyses: List[Dict], findings_analysis: Dict) -> Dict:
        """Generate comprehensive security report."""
        valid_rules = [r for r in rule_analyses if r.get('valid', False)]
        invalid_rules = [r for r in rule_analyses if not r.get('valid', False)]
        
        # Calculate security scores
        rule_scores = [r.get('security_score', 0) for r in valid_rules]
        avg_rule_score = sum(rule_scores) / len(rule_scores) if rule_scores else 0
        
        # Vulnerability class distribution
        vuln_class_dist = {}
        for analysis in valid_rules:
            vuln_class = analysis.get('vuln_class', 'other')
            vuln_class_dist[vuln_class] = vuln_class_dist.get(vuln_class, 0) + 1
        
        # Severity distribution
        severity_dist = {}
        for analysis in valid_rules:
            severity = analysis.get('severity', '')
            severity_dist[severity] = severity_dist.get(severity, 0) + 1
        
        report = {
            'timestamp': datetime.now().isoformat(),
            'summary': {
                'total_rules': len(rule_analyses),
                'valid_rules': len(valid_rules),
                'invalid_rules': len(invalid_rules),
                'avg_security_score': avg_rule_score,
                'critical_vulnerabilities': findings_analysis.get('critical_vulnerabilities', 0),
                'total_findings': findings_analysis.get('total_findings', 0)
            },
            'rule_analyses': rule_analyses,
            'findings_analysis': findings_analysis,
            'vulnerability_class_distribution': vuln_class_dist,
            'severity_distribution': severity_dist,
            'overall_security_score': (avg_rule_score + findings_analysis.get('security_score', 0)) / 2,
            'recommendations': self.generate_overall_recommendations(rule_analyses, findings_analysis)
        }
        
        return report
    
    def generate_overall_recommendations(self, rule_analyses: List[Dict], findings_analysis: Dict) -> List[str]:
        """Generate overall security recommendations."""
        recommendations = []
        
        # Rule quality recommendations
        invalid_rules = [r for r in rule_analyses if not r.get('valid', False)]
        if invalid_rules:
            recommendations.append(f"Fix {len(invalid_rules)} invalid rules before production use")
        
        low_confidence_rules = [r for r in rule_analyses if r.get('confidence') != 'high']
        if low_confidence_rules:
            recommendations.append(f"Improve confidence for {len(low_confidence_rules)} rules")
        
        # Findings recommendations
        if findings_analysis.get('critical_vulnerabilities', 0) > 0:
            recommendations.append("Address critical vulnerabilities immediately")
        
        if findings_analysis.get('high_severity', 0) > 10:
            recommendations.append("Review high-severity findings for false positives")
        
        # General recommendations
        recommendations.append("Regularly review and update security rules")
        recommendations.append("Monitor for new WordPress security patterns")
        recommendations.append("Consider implementing automated security testing")
        
        return recommendations
    
    def print_report(self, report: Dict):
        """Print a formatted security report."""
        summary = report['summary']
        
        print("\n" + "="*80)
        print("SECURITY REVIEW REPORT")
        print("="*80)
        
        print(f"\nOverall Security Score: {report['overall_security_score']:.1f}/100")
        print(f"Timestamp: {report['timestamp']}")
        
        print(f"\nRule Analysis:")
        print(f"  Total Rules: {summary['total_rules']}")
        print(f"  Valid Rules: {summary['valid_rules']}")
        print(f"  Invalid Rules: {summary['invalid_rules']}")
        print(f"  Average Security Score: {summary['avg_security_score']:.1f}")
        
        print(f"\nFindings Analysis:")
        print(f"  Total Findings: {summary['total_findings']}")
        print(f"  Critical Vulnerabilities: {summary['critical_vulnerabilities']}")
        
        print(f"\nVulnerability Class Distribution:")
        for vuln_class, count in report['vulnerability_class_distribution'].items():
            print(f"  {vuln_class.upper()}: {count} rules")
        
        print(f"\nSeverity Distribution:")
        for severity, count in report['severity_distribution'].items():
            print(f"  {severity}: {count} rules")
        
        print(f"\nTop Security Issues:")
        all_issues = []
        for analysis in report['rule_analyses']:
            all_issues.extend(analysis.get('issues', []))
        
        issue_counts = {}
        for issue in all_issues:
            issue_counts[issue] = issue_counts.get(issue, 0) + 1
        
        for issue, count in sorted(issue_counts.items(), key=lambda x: x[1], reverse=True)[:5]:
            print(f"  {issue}: {count} occurrences")
        
        print(f"\nRecommendations:")
        for i, rec in enumerate(report['recommendations'], 1):
            print(f"  {i}. {rec}")
        
        print("\n" + "="*80)

def main():
    parser = argparse.ArgumentParser(description='Run security review for WordPress Semgrep Rules')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--findings', help='Path to findings file for analysis')
    parser.add_argument('--output', help='Output file for results')
    
    args = parser.parse_args()
    
    # Initialize reviewer
    reviewer = SecurityReviewer(args.project_root)
    
    # Run security review
    report = reviewer.run_security_review()
    
    # Print report
    reviewer.print_report(report)
    
    # Exit with appropriate code
    if report['overall_security_score'] >= 70:
        print(f"\n✅ Security review passed!")
        sys.exit(0)
    else:
        print(f"\n❌ Security review needs attention!")
        sys.exit(1)

if __name__ == '__main__':
    main()
