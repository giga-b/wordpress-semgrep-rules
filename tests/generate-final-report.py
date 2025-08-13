#!/usr/bin/env python3
"""
Final Report Generator for WordPress Semgrep Rules
Generates comprehensive final reports from all validation results.
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

class FinalReportGenerator:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.results_dir = self.project_root / "results"
        self.output_dir = self.project_root / "results" / "reports"
        self.output_dir.mkdir(parents=True, exist_ok=True)
    
    def find_latest_results(self, result_type: str) -> Optional[Path]:
        """Find the latest results file of a specific type."""
        type_dir = self.results_dir / result_type
        if not type_dir.exists():
            return None
        
        pattern = f"{result_type}-*.json"
        result_files = list(type_dir.glob(pattern))
        if not result_files:
            return None
        
        return max(result_files, key=lambda f: f.stat().st_mtime)
    
    def load_all_results(self) -> Dict:
        """Load all available validation results."""
        results = {
            'timestamp': datetime.now().isoformat(),
            'data_sources': []
        }
        
        # Load quality gates results
        quality_gates_file = self.find_latest_results('quality-gates')
        if quality_gates_file:
            try:
                with open(quality_gates_file, 'r', encoding='utf-8') as f:
                    results['quality_gates'] = json.load(f)
                results['data_sources'].append('quality-gates')
            except Exception as e:
                results['quality_gates_error'] = str(e)
        
        # Load final validation results
        final_validation_file = self.find_latest_results('final-validation')
        if final_validation_file:
            try:
                with open(final_validation_file, 'r', encoding='utf-8') as f:
                    results['final_validation'] = json.load(f)
                results['data_sources'].append('final-validation')
            except Exception as e:
                results['final_validation_error'] = str(e)
        
        # Load security review results
        security_review_file = self.find_latest_results('security-review')
        if security_review_file:
            try:
                with open(security_review_file, 'r', encoding='utf-8') as f:
                    results['security_review'] = json.load(f)
                results['data_sources'].append('security-review')
            except Exception as e:
                results['security_review_error'] = str(e)
        
        # Load corpus validation results
        corpus_validation_file = self.find_latest_results('corpus-validation')
        if corpus_validation_file:
            try:
                with open(corpus_validation_file, 'r', encoding='utf-8') as f:
                    results['corpus_validation'] = json.load(f)
                results['data_sources'].append('corpus-validation')
            except Exception as e:
                results['corpus_validation_error'] = str(e)
        
        # Load performance results
        performance_dir = self.project_root / "tests" / "performance-results"
        if performance_dir.exists():
            performance_files = list(performance_dir.glob('*.json'))
            if performance_files:
                latest_performance = max(performance_files, key=lambda f: f.stat().st_mtime)
                try:
                    with open(latest_performance, 'r', encoding='utf-8') as f:
                        results['performance'] = json.load(f)
                    results['data_sources'].append('performance')
                except Exception as e:
                    results['performance_error'] = str(e)
        
        return results
    
    def generate_markdown_report(self, results: Dict) -> str:
        """Generate a comprehensive markdown final report."""
        report = f"""# WordPress Semgrep Rules - Final Validation Report

**Generated**: {results.get('timestamp', 'Unknown')}
**Data Sources**: {', '.join(results.get('data_sources', ['None']))}

## Executive Summary

This comprehensive final validation report provides a complete assessment of the WordPress Semgrep Rules project, including quality gates, security review, performance benchmarks, and production readiness evaluation.

"""
        
        # Quality Gates Section
        if 'quality_gates' in results:
            qg = results['quality_gates']
            report += f"""## Quality Gates Assessment

### Overall Results

- **Total Files**: {qg.get('total_files', 0)}
- **Passed Files**: {qg.get('passed_files', 0)}
- **Failed Files**: {qg.get('failed_files', 0)}
- **File Success Rate**: {qg.get('summary', {}).get('file_success_rate', 0)*100:.1f}%

### Rule Results

- **Total Rules**: {qg.get('total_rules', 0)}
- **Passed Rules**: {qg.get('passed_rules', 0)}
- **Failed Rules**: {qg.get('failed_rules', 0)}
- **Rule Success Rate**: {qg.get('summary', {}).get('rule_success_rate', 0)*100:.1f}%

### Quality Metrics

- **Average Quality Score**: {qg.get('summary', {}).get('avg_quality_score', 0):.1f}
- **Minimum Quality Score**: {qg.get('summary', {}).get('min_quality_score', 0):.1f}
- **Maximum Quality Score**: {qg.get('summary', {}).get('max_quality_score', 0):.1f}

"""
        else:
            report += """## Quality Gates Assessment

❌ **No quality gates data available**

"""
        
        # Final Validation Section
        if 'final_validation' in results:
            fv = results['final_validation']
            report += f"""## Final Validation Assessment

### Overall Status

**Status**: {'✅ PASSED' if fv.get('overall_valid', False) else '❌ FAILED'}

### Validation Summary

- **Total Rules**: {fv.get('summary', {}).get('total_rules', 0)}
- **Valid Rules**: {fv.get('summary', {}).get('valid_rules', 0)}
- **Complete Metadata**: {fv.get('summary', {}).get('complete_metadata', 0)}
- **Tested Rules**: {fv.get('summary', {}).get('tested_rules', 0)}
- **Corpus Components**: {fv.get('summary', {}).get('corpus_components', 0)}
- **Documentation Complete**: {'✅ YES' if fv.get('summary', {}).get('documentation_complete', False) else '❌ NO'}
- **Performance Benchmarks**: {'✅ YES' if fv.get('summary', {}).get('performance_benchmarks', False) else '❌ NO'}

### Validation Details

"""
            
            validations = fv.get('validations', {})
            for name, validation in validations.items():
                status = "✅ PASS" if validation.get('valid', False) else "❌ FAIL"
                report += f"- **{name.replace('_', ' ').title()}**: {status}\n"
            
            report += "\n"
        else:
            report += """## Final Validation Assessment

❌ **No final validation data available**

"""
        
        # Security Review Section
        if 'security_review' in results:
            sr = results['security_review']
            report += f"""## Security Review Assessment

### Overall Security Score

**Score**: {sr.get('overall_security_score', 0):.1f}/100

### Security Metrics

- **Total Rules**: {sr.get('summary', {}).get('total_rules', 0)}
- **Valid Rules**: {sr.get('summary', {}).get('valid_rules', 0)}
- **Invalid Rules**: {sr.get('summary', {}).get('invalid_rules', 0)}
- **Average Security Score**: {sr.get('summary', {}).get('avg_security_score', 0):.1f}
- **Critical Vulnerabilities**: {sr.get('summary', {}).get('critical_vulnerabilities', 0)}
- **Total Findings**: {sr.get('summary', {}).get('total_findings', 0)}

### Vulnerability Class Distribution

"""
            
            vuln_dist = sr.get('vulnerability_class_distribution', {})
            for vuln_class, count in vuln_dist.items():
                report += f"- **{vuln_class.upper()}**: {count} rules\n"
            
            report += "\n"
        else:
            report += """## Security Review Assessment

❌ **No security review data available**

"""
        
        # Corpus Validation Section
        if 'corpus_validation' in results:
            cv = results['corpus_validation']
            report += f"""## Corpus Validation Assessment

### Overall Status

**Status**: {'✅ VALID' if cv.get('overall_valid', False) else '❌ INVALID'}

### Corpus Metrics

- **Total Components**: {cv.get('summary', {}).get('total_components', 0)}
- **Valid Components**: {cv.get('summary', {}).get('valid_components', 0)}
- **Total Size**: {cv.get('summary', {}).get('total_size_mb', 0):.2f} MB
- **Scan Findings**: {cv.get('summary', {}).get('scan_findings', 0)}
- **Scan Time**: {cv.get('summary', {}).get('scan_time_seconds', 0):.2f} seconds

"""
        else:
            report += """## Corpus Validation Assessment

❌ **No corpus validation data available**

"""
        
        # Performance Section
        if 'performance' in results:
            perf = results['performance']
            report += f"""## Performance Assessment

### Performance Metrics

- **Total Scans**: {perf.get('total_scans', 0)}
- **Average Scan Time**: {perf.get('avg_scan_time', 0):.2f} seconds
- **Total Memory Usage**: {perf.get('total_memory_mb', 0):.2f} MB
- **Average Memory per Scan**: {perf.get('avg_memory_per_scan', 0):.2f} MB

"""
        else:
            report += """## Performance Assessment

❌ **No performance data available**

"""
        
        # Production Readiness Assessment
        report += """## Production Readiness Assessment

### Readiness Criteria

"""
        
        readiness_score = 0
        total_criteria = 0
        
        # Check quality gates
        if 'quality_gates' in results:
            qg = results['quality_gates']
            qg_success_rate = qg.get('summary', {}).get('file_success_rate', 0)
            if qg_success_rate >= 0.9:
                report += "- ✅ **Quality Gates**: Passed (90%+ success rate)\n"
                readiness_score += 25
            else:
                report += f"- ❌ **Quality Gates**: Failed ({qg_success_rate*100:.1f}% success rate)\n"
            total_criteria += 25
        
        # Check final validation
        if 'final_validation' in results:
            fv = results['final_validation']
            if fv.get('overall_valid', False):
                report += "- ✅ **Final Validation**: Passed\n"
                readiness_score += 25
            else:
                report += "- ❌ **Final Validation**: Failed\n"
            total_criteria += 25
        
        # Check security review
        if 'security_review' in results:
            sr = results['security_review']
            security_score = sr.get('overall_security_score', 0)
            if security_score >= 70:
                report += f"- ✅ **Security Review**: Passed (Score: {security_score:.1f}/100)\n"
                readiness_score += 25
            else:
                report += f"- ❌ **Security Review**: Failed (Score: {security_score:.1f}/100)\n"
            total_criteria += 25
        
        # Check corpus validation
        if 'corpus_validation' in results:
            cv = results['corpus_validation']
            if cv.get('overall_valid', False):
                report += "- ✅ **Corpus Validation**: Passed\n"
                readiness_score += 25
            else:
                report += "- ❌ **Corpus Validation**: Failed\n"
            total_criteria += 25
        
        if total_criteria == 0:
            readiness_score = 0
            total_criteria = 1
        
        readiness_percentage = (readiness_score / total_criteria) * 100
        
        report += f"""
### Overall Readiness Score

**Score**: {readiness_percentage:.1f}%

"""
        
        if readiness_percentage >= 90:
            report += "**Status**: ✅ **READY FOR PRODUCTION**\n\n"
            report += "The WordPress Semgrep Rules project meets all production readiness criteria and is ready for deployment.\n"
        elif readiness_percentage >= 75:
            report += "**Status**: ⚠️ **NEARLY READY**\n\n"
            report += "The project is close to production readiness but has some minor issues that should be addressed.\n"
        elif readiness_percentage >= 50:
            report += "**Status**: ⚠️ **NEEDS IMPROVEMENT**\n\n"
            report += "The project has significant issues that must be addressed before production deployment.\n"
        else:
            report += "**Status**: ❌ **NOT READY**\n\n"
            report += "The project has critical issues that prevent production deployment.\n"
        
        # Recommendations
        report += """## Recommendations

### Immediate Actions

"""
        
        recommendations = []
        
        if 'quality_gates' in results:
            qg = results['quality_gates']
            if qg.get('failed_files', 0) > 0:
                recommendations.append(f"Fix {qg.get('failed_files', 0)} failed quality gate files")
        
        if 'final_validation' in results:
            fv = results['final_validation']
            if not fv.get('overall_valid', False):
                recommendations.append("Address final validation failures")
        
        if 'security_review' in results:
            sr = results['security_review']
            if sr.get('overall_security_score', 0) < 70:
                recommendations.append("Improve security score to 70+ before production")
        
        if 'corpus_validation' in results:
            cv = results['corpus_validation']
            if not cv.get('overall_valid', False):
                recommendations.append("Fix corpus validation issues")
        
        if not recommendations:
            recommendations.append("No immediate actions required - project is ready for production")
        
        for i, rec in enumerate(recommendations, 1):
            report += f"{i}. {rec}\n"
        
        # Long-term recommendations
        report += """
### Long-term Strategy

1. **Continuous Monitoring**: Implement automated monitoring of rule quality and performance
2. **Regular Reviews**: Conduct monthly security and quality reviews
3. **Performance Optimization**: Continuously monitor and optimize rule performance
4. **Documentation Updates**: Keep documentation current with rule changes
5. **Community Feedback**: Gather and incorporate community feedback

## Technical Details

### Data Sources Used

"""
        
        for source in results.get('data_sources', []):
            report += f"- {source.replace('-', ' ').title()}\n"
        
        # Error reporting
        errors = []
        for key, value in results.items():
            if key.endswith('_error'):
                errors.append(f"{key.replace('_error', '')}: {value}")
        
        if errors:
            report += "\n### Data Source Errors\n\n"
            for error in errors:
                report += f"- {error}\n"
        
        report += f"""
---
*Report generated by WordPress Semgrep Rules Final Report Generator*
*Generated: {results.get('timestamp', 'Unknown')}*
"""
        
        return report
    
    def generate_json_report(self, results: Dict) -> Dict:
        """Generate a structured JSON final report."""
        # Calculate readiness score
        readiness_score = 0
        total_criteria = 0
        
        if 'quality_gates' in results:
            qg = results['quality_gates']
            qg_success_rate = qg.get('summary', {}).get('file_success_rate', 0)
            if qg_success_rate >= 0.9:
                readiness_score += 25
            total_criteria += 25
        
        if 'final_validation' in results:
            fv = results['final_validation']
            if fv.get('overall_valid', False):
                readiness_score += 25
            total_criteria += 25
        
        if 'security_review' in results:
            sr = results['security_review']
            security_score = sr.get('overall_security_score', 0)
            if security_score >= 70:
                readiness_score += 25
            total_criteria += 25
        
        if 'corpus_validation' in results:
            cv = results['corpus_validation']
            if cv.get('overall_valid', False):
                readiness_score += 25
            total_criteria += 25
        
        if total_criteria == 0:
            readiness_score = 0
            total_criteria = 1
        
        readiness_percentage = (readiness_score / total_criteria) * 100
        
        return {
            'report_type': 'final_validation_report',
            'timestamp': results.get('timestamp', datetime.now().isoformat()),
            'data_sources': results.get('data_sources', []),
            'readiness_score': readiness_percentage,
            'readiness_status': 'ready' if readiness_percentage >= 90 else 'nearly_ready' if readiness_percentage >= 75 else 'needs_improvement' if readiness_percentage >= 50 else 'not_ready',
            'quality_gates': results.get('quality_gates', {}),
            'final_validation': results.get('final_validation', {}),
            'security_review': results.get('security_review', {}),
            'corpus_validation': results.get('corpus_validation', {}),
            'performance': results.get('performance', {}),
            'errors': {k: v for k, v in results.items() if k.endswith('_error')},
            'metadata': {
                'generator': 'WordPress Semgrep Rules Final Report Generator',
                'version': '1.0.0'
            }
        }
    
    def save_reports(self, results: Dict):
        """Save both markdown and JSON reports."""
        timestamp = int(time.time())
        
        # Generate markdown report
        markdown_report = self.generate_markdown_report(results)
        markdown_file = self.output_dir / f"final-report-{timestamp}.md"
        with open(markdown_file, 'w', encoding='utf-8') as f:
            f.write(markdown_report)
        
        # Generate JSON report
        json_report = self.generate_json_report(results)
        json_file = self.output_dir / f"final-report-{timestamp}.json"
        with open(json_file, 'w', encoding='utf-8') as f:
            json.dump(json_report, f, indent=2)
        
        print(f"Final reports saved:")
        print(f"  Markdown: {markdown_file}")
        print(f"  JSON: {json_file}")
        
        return markdown_file, json_file
    
    def print_summary(self, results: Dict):
        """Print a summary of the final report."""
        json_report = self.generate_json_report(results)
        
        print("\n" + "="*80)
        print("FINAL REPORT SUMMARY")
        print("="*80)
        
        print(f"\nReadiness Score: {json_report['readiness_score']:.1f}%")
        print(f"Readiness Status: {json_report['readiness_status'].replace('_', ' ').title()}")
        print(f"Data Sources: {', '.join(results.get('data_sources', ['None']))}")
        
        if json_report['readiness_score'] >= 90:
            print("Status: ✅ READY FOR PRODUCTION")
        elif json_report['readiness_score'] >= 75:
            print("Status: ⚠️ NEARLY READY")
        elif json_report['readiness_score'] >= 50:
            print("Status: ⚠️ NEEDS IMPROVEMENT")
        else:
            print("Status: ❌ NOT READY")
        
        print("\n" + "="*80)

def main():
    parser = argparse.ArgumentParser(description='Generate final reports for WordPress Semgrep Rules')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--output-dir', help='Output directory for reports')
    parser.add_argument('--format', choices=['markdown', 'json', 'both'], default='both', help='Report format')
    
    args = parser.parse_args()
    
    # Initialize generator
    generator = FinalReportGenerator(args.project_root)
    
    # Load all results
    results = generator.load_all_results()
    
    # Generate and save reports
    if args.format in ['markdown', 'both']:
        markdown_report = generator.generate_markdown_report(results)
        print("Generated markdown final report")
    
    if args.format in ['json', 'both']:
        json_report = generator.generate_json_report(results)
        print("Generated JSON final report")
    
    # Save reports
    generator.save_reports(results)
    
    # Print summary
    generator.print_summary(results)
    
    # Exit with appropriate code
    json_report = generator.generate_json_report(results)
    if json_report['readiness_score'] >= 75:
        print(f"\n✅ Final report generated successfully!")
        sys.exit(0)
    else:
        print(f"\n⚠️ Final report generated with readiness concerns!")
        sys.exit(1)

if __name__ == '__main__':
    main()
