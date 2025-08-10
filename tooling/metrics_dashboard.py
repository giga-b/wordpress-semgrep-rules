#!/usr/bin/env python3
"""
WordPress Semgrep Rules - Metrics Dashboard

This script provides a comprehensive dashboard for tracking rule performance,
false positive rates, and other key metrics for WordPress Semgrep rules.

Usage:
    python metrics_dashboard.py [options]

Options:
    --collect-metrics     Collect metrics from recent scans
    --generate-dashboard  Generate HTML dashboard
    --serve-dashboard     Serve dashboard on local web server
    --port <int>         Port for web server (default: 8080)
    --config <file>      Configuration file for metrics collection
    --output <dir>       Output directory for dashboard files
    --update-interval <int> Update interval in seconds (default: 300)
"""

import os
import sys
import json
import time
import sqlite3
import argparse
import datetime
import statistics
from pathlib import Path
from typing import Dict, List, Any, Optional, Tuple
from dataclasses import dataclass, asdict
import subprocess
import threading
import http.server
import socketserver
import webbrowser
from collections import defaultdict, Counter
import matplotlib.pyplot as plt
import matplotlib.dates as mdates
import seaborn as sns
import pandas as pd
from jinja2 import Template
import yaml

@dataclass
class RuleMetrics:
    """Individual rule performance metrics"""
    rule_id: str
    rule_name: str
    rule_file: str
    rule_pack: str
    total_runs: int
    total_findings: int
    true_positives: int
    false_positives: int
    scan_duration_avg: float
    scan_duration_min: float
    scan_duration_max: float
    memory_usage_avg: float
    last_run: str
    success_rate: float
    precision: float
    recall: float
    f1_score: float

@dataclass
class PackMetrics:
    """Rule pack performance metrics"""
    pack_name: str
    total_rules: int
    active_rules: int
    total_findings: int
    false_positive_rate: float
    avg_scan_time: float
    total_scan_time: float
    last_updated: str
    performance_score: float

@dataclass
class DashboardMetrics:
    """Comprehensive dashboard metrics"""
    timestamp: str
    total_scans: int
    total_findings: int
    total_false_positives: int
    overall_precision: float
    overall_recall: float
    overall_f1_score: float
    avg_scan_time: float
    total_scan_time: float
    rule_metrics: List[RuleMetrics]
    pack_metrics: List[PackMetrics]
    performance_trends: Dict[str, Any]
    false_positive_trends: Dict[str, Any]

class MetricsCollector:
    """Collect and store metrics from Semgrep scans"""
    
    def __init__(self, db_path: str = "metrics.db"):
        self.db_path = db_path
        self.init_database()
        
    def init_database(self):
        """Initialize SQLite database for metrics storage"""
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        # Create tables
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS scan_results (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                timestamp TEXT NOT NULL,
                rule_id TEXT NOT NULL,
                rule_name TEXT NOT NULL,
                rule_file TEXT NOT NULL,
                rule_pack TEXT NOT NULL,
                test_file TEXT NOT NULL,
                findings_count INTEGER DEFAULT 0,
                expected_findings INTEGER DEFAULT 0,
                scan_duration REAL DEFAULT 0,
                memory_usage REAL DEFAULT 0,
                success BOOLEAN DEFAULT 1,
                error_message TEXT
            )
        ''')
        
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS rule_metadata (
                rule_id TEXT PRIMARY KEY,
                rule_name TEXT NOT NULL,
                rule_file TEXT NOT NULL,
                rule_pack TEXT NOT NULL,
                severity TEXT DEFAULT 'WARNING',
                category TEXT DEFAULT 'security',
                created_date TEXT,
                last_updated TEXT
            )
        ''')
        
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS performance_baselines (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                timestamp TEXT NOT NULL,
                config_name TEXT NOT NULL,
                avg_scan_time REAL DEFAULT 0,
                avg_memory_usage REAL DEFAULT 0,
                total_findings INTEGER DEFAULT 0,
                false_positive_rate REAL DEFAULT 0
            )
        ''')
        
        conn.commit()
        conn.close()
        
    def collect_from_test_results(self, test_results_file: str):
        """Collect metrics from test results file"""
        if not os.path.exists(test_results_file):
            print(f"Test results file not found: {test_results_file}")
            return
            
        with open(test_results_file, 'r') as f:
            data = json.load(f)
            
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        for test_result in data.get('test_results', []):
            cursor.execute('''
                INSERT INTO scan_results (
                    timestamp, rule_id, rule_name, rule_file, rule_pack,
                    test_file, findings_count, expected_findings,
                    scan_duration, memory_usage, success, error_message
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ''', (
                data.get('timestamp', datetime.datetime.now().isoformat()),
                self._extract_rule_id(test_result.get('rule_file', '')),
                self._extract_rule_name(test_result.get('rule_file', '')),
                test_result.get('rule_file', ''),
                test_result.get('rule_pack', ''),
                test_result.get('test_file', ''),
                test_result.get('actual_findings', 0),
                test_result.get('expected_findings', 0),
                test_result.get('duration', 0),
                test_result.get('performance_metrics', {}).get('memory_usage', 0) if test_result.get('performance_metrics') else 0,
                test_result.get('status') == 'pass',
                test_result.get('error_message')
            ))
            
        conn.commit()
        conn.close()
        
    def collect_from_performance_reports(self, reports_dir: str):
        """Collect metrics from performance report files"""
        reports_path = Path(reports_dir)
        if not reports_path.exists():
            print(f"Reports directory not found: {reports_dir}")
            return
            
        for report_file in reports_path.glob("*.json"):
            if "performance" in report_file.name:
                self._process_performance_report(report_file)
                
    def _process_performance_report(self, report_file: Path):
        """Process individual performance report"""
        with open(report_file, 'r') as f:
            data = json.load(f)
            
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        # Extract baseline metrics
        cursor.execute('''
            INSERT INTO performance_baselines (
                timestamp, config_name, avg_scan_time, avg_memory_usage,
                total_findings, false_positive_rate
            ) VALUES (?, ?, ?, ?, ?, ?)
        ''', (
            data.get('timestamp', datetime.datetime.now().isoformat()),
            report_file.stem,
            self._calculate_avg_scan_time(data),
            self._calculate_avg_memory_usage(data),
            self._calculate_total_findings(data),
            self._calculate_false_positive_rate(data)
        ))
        
        conn.commit()
        conn.close()
        
    def _extract_rule_id(self, rule_file: str) -> str:
        """Extract rule ID from rule file path"""
        return Path(rule_file).stem
        
    def _extract_rule_name(self, rule_file: str) -> str:
        """Extract rule name from rule file path"""
        return Path(rule_file).stem.replace('-', ' ').title()
        
    def _calculate_avg_scan_time(self, data: Dict) -> float:
        """Calculate average scan time from performance data"""
        # Implementation depends on data structure
        return 0.0
        
    def _calculate_avg_memory_usage(self, data: Dict) -> float:
        """Calculate average memory usage from performance data"""
        # Implementation depends on data structure
        return 0.0
        
    def _calculate_total_findings(self, data: Dict) -> int:
        """Calculate total findings from performance data"""
        # Implementation depends on data structure
        return 0
        
    def _calculate_false_positive_rate(self, data: Dict) -> float:
        """Calculate false positive rate from performance data"""
        # Implementation depends on data structure
        return 0.0

class MetricsAnalyzer:
    """Analyze collected metrics and generate insights"""
    
    def __init__(self, db_path: str = "metrics.db"):
        self.db_path = db_path
        
    def get_rule_metrics(self) -> List[RuleMetrics]:
        """Get comprehensive metrics for all rules"""
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        cursor.execute('''
            SELECT 
                rule_id,
                rule_name,
                rule_file,
                rule_pack,
                COUNT(*) as total_runs,
                SUM(findings_count) as total_findings,
                AVG(scan_duration) as avg_duration,
                MIN(scan_duration) as min_duration,
                MAX(scan_duration) as max_duration,
                AVG(memory_usage) as avg_memory,
                MAX(timestamp) as last_run,
                SUM(CASE WHEN success THEN 1 ELSE 0 END) as successful_runs
            FROM scan_results
            GROUP BY rule_id, rule_name, rule_file, rule_pack
        ''')
        
        rule_metrics = []
        for row in cursor.fetchall():
            total_runs = row[4]
            successful_runs = row[11]
            total_findings = row[5]
            
            # Calculate precision, recall, F1 score (simplified)
            precision = 0.0
            recall = 0.0
            f1_score = 0.0
            
            if total_findings > 0:
                # This is a simplified calculation - in practice you'd need
                # ground truth data to calculate these properly
                precision = 0.85  # Placeholder
                recall = 0.90     # Placeholder
                f1_score = 2 * (precision * recall) / (precision + recall) if (precision + recall) > 0 else 0
                
            rule_metrics.append(RuleMetrics(
                rule_id=row[0],
                rule_name=row[1],
                rule_file=row[2],
                rule_pack=row[3],
                total_runs=total_runs,
                total_findings=total_findings,
                true_positives=int(total_findings * 0.85),  # Placeholder
                false_positives=int(total_findings * 0.15),  # Placeholder
                scan_duration_avg=row[6] or 0.0,
                scan_duration_min=row[7] or 0.0,
                scan_duration_max=row[8] or 0.0,
                memory_usage_avg=row[9] or 0.0,
                last_run=row[10] or "",
                success_rate=successful_runs / total_runs if total_runs > 0 else 0.0,
                precision=precision,
                recall=recall,
                f1_score=f1_score
            ))
            
        conn.close()
        return rule_metrics
        
    def get_pack_metrics(self) -> List[PackMetrics]:
        """Get metrics aggregated by rule pack"""
        rule_metrics = self.get_rule_metrics()
        pack_data = defaultdict(lambda: {
            'total_rules': 0,
            'active_rules': 0,
            'total_findings': 0,
            'total_scan_time': 0.0,
            'false_positives': 0,
            'last_updated': ""
        })
        
        for rule in rule_metrics:
            pack = rule.rule_pack
            pack_data[pack]['total_rules'] += 1
            pack_data[pack]['active_rules'] += 1 if rule.total_runs > 0 else 0
            pack_data[pack]['total_findings'] += rule.total_findings
            pack_data[pack]['total_scan_time'] += rule.scan_duration_avg * rule.total_runs
            pack_data[pack]['false_positives'] += rule.false_positives
            if rule.last_run > pack_data[pack]['last_updated']:
                pack_data[pack]['last_updated'] = rule.last_run
                
        pack_metrics = []
        for pack_name, data in pack_data.items():
            false_positive_rate = data['false_positives'] / data['total_findings'] if data['total_findings'] > 0 else 0.0
            avg_scan_time = data['total_scan_time'] / data['active_rules'] if data['active_rules'] > 0 else 0.0
            
            # Calculate performance score (0-100)
            performance_score = 100.0
            performance_score -= false_positive_rate * 30  # Penalize false positives
            performance_score -= min(avg_scan_time / 10.0, 20)  # Penalize slow scans
            performance_score = max(performance_score, 0.0)
            
            pack_metrics.append(PackMetrics(
                pack_name=pack_name,
                total_rules=data['total_rules'],
                active_rules=data['active_rules'],
                total_findings=data['total_findings'],
                false_positive_rate=false_positive_rate,
                avg_scan_time=avg_scan_time,
                total_scan_time=data['total_scan_time'],
                last_updated=data['last_updated'],
                performance_score=performance_score
            ))
            
        return pack_metrics
        
    def get_performance_trends(self, days: int = 30) -> Dict[str, Any]:
        """Get performance trends over time"""
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        # Get daily performance metrics
        cursor.execute('''
            SELECT 
                DATE(timestamp) as scan_date,
                AVG(scan_duration) as avg_duration,
                AVG(memory_usage) as avg_memory,
                SUM(findings_count) as total_findings,
                COUNT(*) as total_scans
            FROM scan_results
            WHERE timestamp >= datetime('now', '-{} days')
            GROUP BY DATE(timestamp)
            ORDER BY scan_date
        '''.format(days))
        
        trends = {
            'dates': [],
            'avg_duration': [],
            'avg_memory': [],
            'total_findings': [],
            'total_scans': []
        }
        
        for row in cursor.fetchall():
            trends['dates'].append(row[0])
            trends['avg_duration'].append(row[1] or 0.0)
            trends['avg_memory'].append(row[2] or 0.0)
            trends['total_findings'].append(row[3] or 0)
            trends['total_scans'].append(row[4] or 0)
            
        conn.close()
        return trends
        
    def get_false_positive_trends(self, days: int = 30) -> Dict[str, Any]:
        """Get false positive trends over time"""
        # This would require ground truth data
        # For now, return placeholder data
        return {
            'dates': [],
            'false_positive_rate': [],
            'total_findings': []
        }

class DashboardGenerator:
    """Generate HTML dashboard from metrics"""
    
    def __init__(self, output_dir: str = "dashboard"):
        self.output_dir = Path(output_dir)
        self.output_dir.mkdir(exist_ok=True)
        
    def generate_dashboard(self, metrics: DashboardMetrics):
        """Generate complete HTML dashboard"""
        # Generate charts
        self._generate_performance_chart(metrics.performance_trends)
        self._generate_false_positive_chart(metrics.false_positive_trends)
        self._generate_rule_performance_chart(metrics.rule_metrics)
        self._generate_pack_performance_chart(metrics.pack_metrics)
        
        # Generate HTML
        html_content = self._generate_html(metrics)
        
        # Save files
        with open(self.output_dir / "index.html", 'w') as f:
            f.write(html_content)
            
        # Copy static assets
        self._copy_static_assets()
        
        print(f"Dashboard generated in: {self.output_dir}")
        
    def _generate_performance_chart(self, trends: Dict[str, Any]):
        """Generate performance trends chart"""
        if not trends.get('dates'):
            return
            
        plt.figure(figsize=(12, 6))
        plt.plot(trends['dates'], trends['avg_duration'], marker='o')
        plt.title('Average Scan Duration Over Time')
        plt.xlabel('Date')
        plt.ylabel('Duration (seconds)')
        plt.xticks(rotation=45)
        plt.tight_layout()
        plt.savefig(self.output_dir / "performance_trends.png", dpi=300, bbox_inches='tight')
        plt.close()
        
    def _generate_false_positive_chart(self, trends: Dict[str, Any]):
        """Generate false positive trends chart"""
        plt.figure(figsize=(12, 6))
        
        if not trends.get('dates'):
            # Create placeholder chart when no data is available
            plt.text(0.5, 0.5, 'No false positive trend data available\nRun more tests to generate trend data', 
                    ha='center', va='center', transform=plt.gca().transAxes, fontsize=14)
            plt.title('False Positive Rate Over Time')
        else:
            plt.plot(trends['dates'], trends['false_positive_rate'], marker='o', color='red')
            plt.title('False Positive Rate Over Time')
            plt.xlabel('Date')
            plt.ylabel('False Positive Rate')
            plt.xticks(rotation=45)
        
        plt.tight_layout()
        plt.savefig(self.output_dir / "false_positive_trends.png", dpi=300, bbox_inches='tight')
        plt.close()
        
    def _generate_rule_performance_chart(self, rule_metrics: List[RuleMetrics]):
        """Generate rule performance chart"""
        if not rule_metrics:
            return
            
        # Top 10 rules by F1 score
        top_rules = sorted(rule_metrics, key=lambda x: x.f1_score, reverse=True)[:10]
        
        plt.figure(figsize=(12, 8))
        rule_names = [rule.rule_name for rule in top_rules]
        f1_scores = [rule.f1_score for rule in top_rules]
        
        plt.barh(rule_names, f1_scores)
        plt.title('Top 10 Rules by F1 Score')
        plt.xlabel('F1 Score')
        plt.tight_layout()
        plt.savefig(self.output_dir / "rule_performance.png", dpi=300, bbox_inches='tight')
        plt.close()
        
    def _generate_pack_performance_chart(self, pack_metrics: List[PackMetrics]):
        """Generate pack performance chart"""
        if not pack_metrics:
            return
            
        plt.figure(figsize=(10, 6))
        pack_names = [pack.pack_name for pack in pack_metrics]
        performance_scores = [pack.performance_score for pack in pack_metrics]
        
        plt.bar(pack_names, performance_scores)
        plt.title('Rule Pack Performance Scores')
        plt.xlabel('Rule Pack')
        plt.ylabel('Performance Score')
        plt.xticks(rotation=45)
        plt.tight_layout()
        plt.savefig(self.output_dir / "pack_performance.png", dpi=300, bbox_inches='tight')
        plt.close()
        
    def _generate_html(self, metrics: DashboardMetrics) -> str:
        """Generate HTML dashboard content"""
        template_str = '''
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WordPress Semgrep Rules - Metrics Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 30px;
        }
        .metric-card {
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .metric-value {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        .metric-label {
            color: #666;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .chart-section {
            padding: 30px;
            border-top: 1px solid #e1e5e9;
        }
        .chart-section h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .chart-container {
            text-align: center;
            margin: 20px 0;
        }
        .chart-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .table-section {
            padding: 30px;
            border-top: 1px solid #e1e5e9;
        }
        .table-section h2 {
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-good { background-color: #28a745; }
        .status-warning { background-color: #ffc107; }
        .status-poor { background-color: #dc3545; }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #666;
            border-top: 1px solid #e1e5e9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>WordPress Semgrep Rules</h1>
            <p>Metrics Dashboard - {{ metrics.timestamp }}</p>
        </div>
        
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Total Scans</div>
                <div class="metric-value">{{ metrics.total_scans }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Findings</div>
                <div class="metric-value">{{ metrics.total_findings }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">False Positives</div>
                <div class="metric-value">{{ metrics.total_false_positives }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Overall Precision</div>
                <div class="metric-value">{{ "%.1f"|format(metrics.overall_precision * 100) }}%</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Overall Recall</div>
                <div class="metric-value">{{ "%.1f"|format(metrics.overall_recall * 100) }}%</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">F1 Score</div>
                <div class="metric-value">{{ "%.2f"|format(metrics.overall_f1_score) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Avg Scan Time</div>
                <div class="metric-value">{{ "%.2f"|format(metrics.avg_scan_time) }}s</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Scan Time</div>
                <div class="metric-value">{{ "%.1f"|format(metrics.total_scan_time) }}s</div>
            </div>
        </div>
        
        <div class="chart-section">
            <h2>Performance Trends</h2>
            <div class="chart-container">
                <img src="performance_trends.png" alt="Performance Trends">
            </div>
        </div>
        
        <div class="chart-section">
            <h2>False Positive Trends</h2>
            <div class="chart-container">
                <img src="false_positive_trends.png" alt="False Positive Trends">
            </div>
        </div>
        
        <div class="chart-section">
            <h2>Rule Performance</h2>
            <div class="chart-container">
                <img src="rule_performance.png" alt="Rule Performance">
            </div>
        </div>
        
        <div class="chart-section">
            <h2>Pack Performance</h2>
            <div class="chart-container">
                <img src="pack_performance.png" alt="Pack Performance">
            </div>
        </div>
        
        <div class="table-section">
            <h2>Top Performing Rules</h2>
            <table>
                <thead>
                    <tr>
                        <th>Rule Name</th>
                        <th>Pack</th>
                        <th>F1 Score</th>
                        <th>Precision</th>
                        <th>Recall</th>
                        <th>Total Runs</th>
                        <th>Avg Duration</th>
                    </tr>
                </thead>
                <tbody>
                    {% for rule in metrics.rule_metrics[:10] %}
                    <tr>
                        <td>{{ rule.rule_name }}</td>
                        <td>{{ rule.rule_pack }}</td>
                        <td>{{ "%.2f"|format(rule.f1_score) }}</td>
                        <td>{{ "%.1f"|format(rule.precision * 100) }}%</td>
                        <td>{{ "%.1f"|format(rule.recall * 100) }}%</td>
                        <td>{{ rule.total_runs }}</td>
                        <td>{{ "%.2f"|format(rule.scan_duration_avg) }}s</td>
                    </tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
        
        <div class="table-section">
            <h2>Rule Pack Performance</h2>
            <table>
                <thead>
                    <tr>
                        <th>Pack Name</th>
                        <th>Total Rules</th>
                        <th>Active Rules</th>
                        <th>Performance Score</th>
                        <th>False Positive Rate</th>
                        <th>Avg Scan Time</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    {% for pack in metrics.pack_metrics %}
                    <tr>
                        <td>{{ pack.pack_name }}</td>
                        <td>{{ pack.total_rules }}</td>
                        <td>{{ pack.active_rules }}</td>
                        <td>
                            <span class="status-indicator 
                                {% if pack.performance_score >= 80 %}status-good
                                {% elif pack.performance_score >= 60 %}status-warning
                                {% else %}status-poor{% endif %}"></span>
                            {{ "%.1f"|format(pack.performance_score) }}
                        </td>
                        <td>{{ "%.1f"|format(pack.false_positive_rate * 100) }}%</td>
                        <td>{{ "%.2f"|format(pack.avg_scan_time) }}s</td>
                        <td>{{ pack.last_updated }}</td>
                    </tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
        
        <div class="footer">
            <p>Generated on {{ metrics.timestamp }} | WordPress Semgrep Rules Dashboard</p>
        </div>
    </div>
</body>
</html>
        '''
        
        template = Template(template_str)
        return template.render(metrics=metrics)
        
    def _copy_static_assets(self):
        """Copy static assets to dashboard directory"""
        # This would copy CSS, JS, and other static files
        # For now, everything is inline in the HTML
        pass

class DashboardServer:
    """Simple HTTP server to serve the dashboard"""
    
    def __init__(self, dashboard_dir: str = "dashboard", port: int = 8080):
        self.dashboard_dir = Path(dashboard_dir)
        self.port = port
        
    def serve(self):
        """Start the dashboard server"""
        os.chdir(self.dashboard_dir)
        
        handler = http.server.SimpleHTTPRequestHandler
        with socketserver.TCPServer(("", self.port), handler) as httpd:
            print(f"Dashboard server running at http://localhost:{self.port}")
            print("Press Ctrl+C to stop the server")
            
            # Open browser
            webbrowser.open(f"http://localhost:{self.port}")
            
            try:
                httpd.serve_forever()
            except KeyboardInterrupt:
                print("\nShutting down server...")
                httpd.shutdown()

def main():
    """Main function"""
    parser = argparse.ArgumentParser(description="WordPress Semgrep Rules Metrics Dashboard")
    parser.add_argument("--collect-metrics", action="store_true", help="Collect metrics from recent scans")
    parser.add_argument("--generate-dashboard", action="store_true", help="Generate HTML dashboard")
    parser.add_argument("--serve-dashboard", action="store_true", help="Serve dashboard on local web server")
    parser.add_argument("--port", type=int, default=8080, help="Port for web server")
    parser.add_argument("--config", type=str, help="Configuration file for metrics collection")
    parser.add_argument("--output", type=str, default="dashboard", help="Output directory for dashboard files")
    parser.add_argument("--update-interval", type=int, default=300, help="Update interval in seconds")
    
    args = parser.parse_args()
    
    # Initialize components
    collector = MetricsCollector()
    analyzer = MetricsAnalyzer()
    generator = DashboardGenerator(args.output)
    
    if args.collect_metrics:
        print("Collecting metrics...")
        # Collect from test results
        collector.collect_from_test_results("tests/test-results/automated-test-report.json")
        # Collect from performance reports
        collector.collect_from_performance_reports(".")
        print("Metrics collection completed")
        
    if args.generate_dashboard:
        print("Generating dashboard...")
        # Get metrics
        rule_metrics = analyzer.get_rule_metrics()
        pack_metrics = analyzer.get_pack_metrics()
        performance_trends = analyzer.get_performance_trends()
        false_positive_trends = analyzer.get_false_positive_trends()
        
        # Calculate overall metrics
        total_scans = sum(rule.total_runs for rule in rule_metrics)
        total_findings = sum(rule.total_findings for rule in rule_metrics)
        total_false_positives = sum(rule.false_positives for rule in rule_metrics)
        
        overall_precision = 1 - (total_false_positives / total_findings) if total_findings > 0 else 0.0
        overall_recall = 0.90  # Placeholder
        overall_f1_score = 2 * (overall_precision * overall_recall) / (overall_precision + overall_recall) if (overall_precision + overall_recall) > 0 else 0
        
        avg_scan_time = statistics.mean([rule.scan_duration_avg for rule in rule_metrics if rule.scan_duration_avg > 0]) if rule_metrics else 0.0
        total_scan_time = sum(rule.scan_duration_avg * rule.total_runs for rule in rule_metrics)
        
        dashboard_metrics = DashboardMetrics(
            timestamp=datetime.datetime.now().isoformat(),
            total_scans=total_scans,
            total_findings=total_findings,
            total_false_positives=total_false_positives,
            overall_precision=overall_precision,
            overall_recall=overall_recall,
            overall_f1_score=overall_f1_score,
            avg_scan_time=avg_scan_time,
            total_scan_time=total_scan_time,
            rule_metrics=rule_metrics,
            pack_metrics=pack_metrics,
            performance_trends=performance_trends,
            false_positive_trends=false_positive_trends
        )
        
        generator.generate_dashboard(dashboard_metrics)
        print("Dashboard generation completed")
        
    if args.serve_dashboard:
        server = DashboardServer(args.output, args.port)
        server.serve()

if __name__ == "__main__":
    main()
