#!/usr/bin/env python3
"""
WordPress Semgrep Rules - Incremental Runner

This module provides a unified interface for incremental scanning that integrates:
- Incremental scanner for change detection
- Cache manager for result caching
- Performance monitoring
- Result analysis and reporting

Author: WordPress Semgrep Rules Team
License: MIT
"""

import os
import sys
import json
import time
import subprocess
import logging
from pathlib import Path
from typing import Dict, List, Optional, Tuple, Any
from dataclasses import asdict

# Import our modules
from incremental_scanner import IncrementalScanner, ScanContext, detect_changes_and_prepare_scan
from cache_manager import get_cache_manager

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)


class IncrementalRunner:
    """
    Unified incremental scanning runner that integrates all components.
    
    Features:
    - Change detection and impact analysis
    - Cache management
    - Performance monitoring
    - Result analysis and reporting
    - Cross-platform support
    """
    
    def __init__(self, base_path: str = ".", config_file: str = "configs/plugin-development.yaml"):
        """
        Initialize the incremental runner.
        
        Args:
            base_path: Base path for scanning
            config_file: Configuration file path
        """
        self.base_path = Path(base_path).resolve()
        self.config_file = config_file
        
        # Initialize components
        self.scanner = IncrementalScanner(str(self.base_path))
        self.cache_manager = get_cache_manager()
        
        # Performance tracking
        self.start_time = None
        self.end_time = None
        
        logger.info(f"Incremental runner initialized: {self.base_path}")
    
    def run_scan(self, use_git: bool = True, force_full: bool = False, 
                output_file: str = "semgrep-results.json", 
                html_report: str = "semgrep-report.html") -> Dict[str, Any]:
        """
        Run incremental or full scan based on detected changes.
        
        Args:
            use_git: Whether to use git for change detection
            force_full: Force full scan regardless of changes
            output_file: Output file for results
            html_report: HTML report file
            
        Returns:
            Scan results and metadata
        """
        self.start_time = time.time()
        
        try:
            # Step 1: Detect changes and prepare scan context
            logger.info("🔍 Detecting changes and preparing scan context...")
            context, should_full_scan = detect_changes_and_prepare_scan(
                self.config_file, str(self.base_path), use_git
            )
            
            # Override with force_full if specified
            if force_full:
                should_full_scan = True
                context.scan_type = "full"
            
            # Step 2: Check cache for existing results
            if not force_full:
                cached_results = self._check_cache(context)
                if cached_results:
                    logger.info("✅ Using cached results")
                    self.end_time = time.time()
                    return self._prepare_results(context, cached_results, True)
            
            # Step 3: Determine scan type and paths
            if should_full_scan:
                logger.info("🚀 Performing full scan")
                scan_paths = [str(self.base_path)]
                context.scan_type = "full"
            else:
                logger.info("🚀 Performing incremental scan")
                scan_paths = context.scan_paths if context.scan_paths else [str(self.base_path)]
            
            # Step 4: Run Semgrep
            logger.info(f"🔍 Scanning {len(scan_paths)} path(s)...")
            semgrep_results = self._run_semgrep(scan_paths, output_file)
            
            # Step 5: Analyze results
            findings_count = self._analyze_results(semgrep_results)
            
            # Step 6: Generate HTML report
            if html_report:
                self._generate_html_report(semgrep_results, html_report, findings_count)
            
            # Step 7: Update scan state and cache
            scan_duration = time.time() - self.start_time
            self.scanner.update_scan_state(context, scan_duration, findings_count)
            self._cache_results(context, semgrep_results)
            
            # Step 8: Prepare final results
            self.end_time = time.time()
            return self._prepare_results(context, semgrep_results, False)
            
        except Exception as e:
            logger.error(f"❌ Error during scan: {e}")
            self.end_time = time.time()
            return {
                "success": False,
                "error": str(e),
                "duration": time.time() - self.start_time
            }
    
    def _check_cache(self, context: ScanContext) -> Optional[Dict[str, Any]]:
        """Check cache for existing results."""
        try:
            cached_results = self.cache_manager.get("incremental_scan", context.cache_key)
            if cached_results:
                logger.info(f"📋 Found cached results for key: {context.cache_key}")
                return cached_results
        except Exception as e:
            logger.warning(f"Cache check failed: {e}")
        
        return None
    
    def _cache_results(self, context: ScanContext, results: Dict[str, Any]) -> None:
        """Cache scan results."""
        try:
            self.cache_manager.set("incremental_scan", results, context.cache_key, ttl=3600)  # 1 hour
            logger.info("📋 Results cached successfully")
        except Exception as e:
            logger.warning(f"Failed to cache results: {e}")
    
    def _run_semgrep(self, scan_paths: List[str], output_file: str) -> Dict[str, Any]:
        """Run Semgrep scan."""
        try:
            # Build Semgrep command
            cmd = [
                "semgrep", "scan",
                "--config", self.config_file,
                "--json",
                "--output", output_file
            ]
            
            # Add scan paths
            cmd.extend(scan_paths)
            
            logger.info(f"Running: {' '.join(cmd)}")
            
            # Run Semgrep
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                cwd=self.base_path,
                timeout=300  # 5 minute timeout
            )
            
            if result.returncode != 0 and result.returncode != 1:  # Semgrep returns 1 for findings
                logger.error(f"Semgrep failed: {result.stderr}")
                raise Exception(f"Semgrep scan failed: {result.stderr}")
            
            # Load results
            if os.path.exists(output_file):
                with open(output_file, 'r') as f:
                    return json.load(f)
            else:
                return {"results": [], "errors": []}
                
        except subprocess.TimeoutExpired:
            logger.error("Semgrep scan timed out")
            raise Exception("Semgrep scan timed out")
        except Exception as e:
            logger.error(f"Error running Semgrep: {e}")
            raise
    
    def _analyze_results(self, results: Dict[str, Any]) -> Dict[str, int]:
        """Analyze scan results."""
        findings = results.get("results", [])
        
        # Count by severity
        severity_counts = {
            "ERROR": 0,
            "WARNING": 0,
            "INFO": 0
        }
        
        for finding in findings:
            severity = finding.get("extra", {}).get("severity", "INFO")
            severity_counts[severity] += 1
        
        total_findings = len(findings)
        
        # Log summary
        logger.info(f"📊 Scan Results Summary:")
        logger.info(f"  Total Findings: {total_findings}")
        logger.info(f"  Errors: {severity_counts['ERROR']}")
        logger.info(f"  Warnings: {severity_counts['WARNING']}")
        logger.info(f"  Info: {severity_counts['INFO']}")
        
        # Show critical findings
        if severity_counts["ERROR"] > 0:
            logger.warning("❌ Critical Security Issues Found:")
            error_findings = [f for f in findings if f.get("extra", {}).get("severity") == "ERROR"]
            for finding in error_findings[:5]:  # Show first 5
                message = finding.get("extra", {}).get("message", "Unknown issue")
                file_path = finding.get("path", "Unknown file")
                line = finding.get("start", {}).get("line", "Unknown line")
                logger.warning(f"  • {message} ({file_path}:{line})")
            
            if len(error_findings) > 5:
                logger.warning(f"  ... and {len(error_findings) - 5} more critical issues")
        
        return {
            "total": total_findings,
            "errors": severity_counts["ERROR"],
            "warnings": severity_counts["WARNING"],
            "info": severity_counts["INFO"]
        }
    
    def _generate_html_report(self, results: Dict[str, Any], html_file: str, 
                            findings_count: Dict[str, int]) -> None:
        """Generate HTML report."""
        try:
            findings = results.get("results", [])
            
            # Group by rule
            rule_groups = {}
            for finding in findings:
                rule_id = finding.get("check_id", "unknown")
                if rule_id not in rule_groups:
                    rule_groups[rule_id] = []
                rule_groups[rule_id].append(finding)
            
            # Generate HTML
            html_content = f"""
<!DOCTYPE html>
<html>
<head>
    <title>WordPress Semgrep Security Report</title>
    <style>
        body {{ font-family: Arial, sans-serif; margin: 20px; }}
        .header {{ background: #f5f5f5; padding: 20px; border-radius: 5px; }}
        .summary {{ margin: 20px 0; }}
        .finding {{ margin: 10px 0; padding: 10px; border-left: 4px solid #ddd; }}
        .error {{ border-left-color: #ff4444; background: #fff5f5; }}
        .warning {{ border-left-color: #ffaa00; background: #fffbf0; }}
        .info {{ border-left-color: #0088cc; background: #f0f8ff; }}
        .rule-group {{ margin: 20px 0; }}
        .rule-header {{ background: #eee; padding: 10px; font-weight: bold; }}
        .file-path {{ color: #666; font-family: monospace; }}
        .line-number {{ color: #999; }}
        .message {{ margin: 5px 0; }}
        .fix {{ background: #e8f5e8; padding: 5px; border-radius: 3px; font-family: monospace; }}
    </style>
</head>
<body>
    <div class="header">
        <h1>WordPress Semgrep Security Report</h1>
        <p>Generated: {time.strftime('%Y-%m-%d %H:%M:%S')}</p>
        <p>Scan Type: Incremental</p>
    </div>
    
    <div class="summary">
        <h2>Summary</h2>
        <p><strong>Total Findings:</strong> {findings_count['total']}</p>
        <p><strong>Errors:</strong> {findings_count['errors']}</p>
        <p><strong>Warnings:</strong> {findings_count['warnings']}</p>
        <p><strong>Info:</strong> {findings_count['info']}</p>
    </div>
"""
            
            # Add findings by rule
            for rule_id, rule_findings in rule_groups.items():
                severity = rule_findings[0].get("extra", {}).get("severity", "INFO")
                severity_class = severity.lower()
                
                html_content += f"""
    <div class="rule-group">
        <div class="rule-header">
            {rule_id} ({len(rule_findings)} findings - {severity})
        </div>
"""
                
                for finding in rule_findings:
                    message = finding.get("extra", {}).get("message", "Unknown issue")
                    file_path = finding.get("path", "Unknown file")
                    line = finding.get("start", {}).get("line", "Unknown line")
                    fix = finding.get("extra", {}).get("fix", "")
                    
                    html_content += f"""
        <div class="finding {severity_class}">
            <div class="message">{message}</div>
            <div class="file-path">{file_path}:<span class="line-number">{line}</span></div>
"""
                    
                    if fix:
                        html_content += f'            <div class="fix">Fix: {fix}</div>'
                    
                    html_content += "        </div>"
                
                html_content += "    </div>"
            
            html_content += """
</body>
</html>
"""
            
            # Write HTML file
            with open(html_file, 'w') as f:
                f.write(html_content)
            
            logger.info(f"📄 HTML report generated: {html_file}")
            
        except Exception as e:
            logger.warning(f"Failed to generate HTML report: {e}")
    
    def _prepare_results(self, context: ScanContext, scan_results: Dict[str, Any], 
                        from_cache: bool) -> Dict[str, Any]:
        """Prepare final results."""
        duration = self.end_time - self.start_time if self.end_time else 0
        
        return {
            "success": True,
            "from_cache": from_cache,
            "scan_type": context.scan_type,
            "config_file": context.config_file,
            "changed_files_count": len(context.changed_files),
            "affected_files_count": len(context.affected_files),
            "scan_paths_count": len(context.scan_paths),
            "duration": duration,
            "results": scan_results,
            "cache_key": context.cache_key,
            "timestamp": time.time()
        }
    
    def get_statistics(self) -> Dict[str, Any]:
        """Get scanning statistics."""
        return self.scanner.get_scan_statistics()
    
    def cleanup_old_data(self, max_age_days: int = 30) -> int:
        """Clean up old scan data."""
        return self.scanner.cleanup_old_data(max_age_days)


def main():
    """Main function for command-line usage."""
    import argparse
    
    parser = argparse.ArgumentParser(description="WordPress Semgrep Incremental Scanner")
    parser.add_argument("--config", "-c", default="configs/plugin-development.yaml",
                       help="Configuration file path")
    parser.add_argument("--path", "-p", default=".", help="Base path for scanning")
    parser.add_argument("--output", "-o", default="semgrep-results.json",
                       help="Output file for results")
    parser.add_argument("--report", "-r", default="semgrep-report.html",
                       help="HTML report file")
    parser.add_argument("--force-full", "-f", action="store_true",
                       help="Force full scan")
    parser.add_argument("--no-git", action="store_true",
                       help="Don't use git for change detection")
    parser.add_argument("--stats", action="store_true",
                       help="Show statistics")
    parser.add_argument("--cleanup", type=int, metavar="DAYS",
                       help="Clean up data older than DAYS")
    
    args = parser.parse_args()
    
    # Initialize runner
    runner = IncrementalRunner(args.path, args.config)
    
    # Handle special commands
    if args.stats:
        stats = runner.get_statistics()
        print(json.dumps(stats, indent=2))
        return
    
    if args.cleanup:
        cleaned = runner.cleanup_old_data(args.cleanup)
        print(f"Cleaned up {cleaned} old records")
        return
    
    # Run scan
    print("🚀 Starting WordPress Semgrep Incremental Scan...")
    results = runner.run_scan(
        use_git=not args.no_git,
        force_full=args.force_full,
        output_file=args.output,
        html_report=args.report
    )
    
    if results["success"]:
        print(f"✅ Scan completed successfully in {results['duration']:.2f} seconds")
        print(f"📊 Scan Type: {results['scan_type']}")
        print(f"📁 Changed Files: {results['changed_files_count']}")
        print(f"📁 Affected Files: {results['affected_files_count']}")
        
        if results.get("from_cache"):
            print("📋 Results from cache")
        
        # Exit with error code if critical issues found
        findings = results.get("results", {}).get("results", [])
        error_count = len([f for f in findings if f.get("extra", {}).get("severity") == "ERROR"])
        
        if error_count > 0:
            print(f"❌ Found {error_count} critical security issues")
            sys.exit(1)
        else:
            print("✅ No critical security issues found")
            sys.exit(0)
    else:
        print(f"❌ Scan failed: {results.get('error', 'Unknown error')}")
        sys.exit(1)


if __name__ == "__main__":
    main()
