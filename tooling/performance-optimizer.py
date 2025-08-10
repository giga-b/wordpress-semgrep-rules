#!/usr/bin/env python3
"""
WordPress Semgrep Rules - Advanced Performance Optimizer

This script analyzes performance test results and automatically optimizes configurations
to meet performance targets while maintaining security coverage.
"""

import json
import os
import sys
import time
import subprocess
import yaml
import statistics
from pathlib import Path
from typing import Dict, List, Any, Optional, Tuple
from dataclasses import dataclass, asdict
import argparse

@dataclass
class OptimizationTarget:
    """Performance optimization targets"""
    max_scan_time: float = 30.0
    max_memory_usage: float = 500.0
    max_cpu_percent: float = 80.0
    min_security_coverage: float = 0.95
    min_throughput: float = 0.1

@dataclass
class RulePerformance:
    """Individual rule performance metrics"""
    rule_id: str
    file_path: str
    execution_time: float
    memory_usage: float
    findings_count: int
    complexity_score: int
    priority: str

@dataclass
class OptimizationResult:
    """Result of optimization process"""
    original_config: str
    optimized_config: str
    performance_improvements: Dict[str, float]
    security_coverage_change: float
    rules_removed: List[str]
    rules_optimized: List[str]
    recommendations: List[str]

class AdvancedPerformanceOptimizer:
    """Advanced performance optimization framework"""
    
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.configs_dir = self.project_root / "configs"
        self.packs_dir = self.project_root / "packs"
        self.tests_dir = self.project_root / "tests"
        self.optimization_targets = OptimizationTarget()
        
    def analyze_performance_data(self, performance_report_path: str) -> Dict[str, Any]:
        """Analyze performance test results"""
        print("Analyzing performance data...")
        
        with open(performance_report_path, 'r') as f:
            report = json.load(f)
        
        analysis = {
            'config_performance': {},
            'rule_performance': {},
            'bottlenecks': [],
            'optimization_opportunities': []
        }
        
        # Analyze configuration performance
        for summary in report.get('test_summaries', []):
            config_name = summary['config_name']
            analysis['config_performance'][config_name] = {
                'scan_time': summary['mean_scan_time'],
                'memory_usage': summary['mean_memory_peak'],
                'throughput': summary['throughput_files_per_second'],
                'success_rate': summary['success_rate']
            }
            
            # Identify bottlenecks
            if summary['mean_scan_time'] > self.optimization_targets.max_scan_time:
                analysis['bottlenecks'].append({
                    'type': 'scan_time',
                    'config': config_name,
                    'value': summary['mean_scan_time'],
                    'threshold': self.optimization_targets.max_scan_time
                })
            
            if summary['mean_memory_peak'] > self.optimization_targets.max_memory_usage:
                analysis['bottlenecks'].append({
                    'type': 'memory_usage',
                    'config': config_name,
                    'value': summary['mean_memory_peak'],
                    'threshold': self.optimization_targets.max_memory_usage
                })
        
        return analysis
    
    def analyze_rule_complexity(self) -> Dict[str, RulePerformance]:
        """Analyze rule complexity and performance impact"""
        print("Analyzing rule complexity...")
        
        rule_performance = {}
        
        # Scan all rule files
        for rule_file in self.packs_dir.rglob("*.yaml"):
            try:
                with open(rule_file, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                # Parse YAML content
                rules = yaml.safe_load(content)
                if not isinstance(rules, list):
                    continue
                
                for rule in rules:
                    if isinstance(rule, dict) and 'id' in rule:
                        rule_id = rule['id']
                        
                        # Calculate complexity metrics
                        complexity_score = self._calculate_rule_complexity(rule)
                        
                        # Estimate performance impact
                        performance_impact = self._estimate_performance_impact(rule)
                        
                        rule_performance[rule_id] = RulePerformance(
                            rule_id=rule_id,
                            file_path=str(rule_file),
                            execution_time=performance_impact['execution_time'],
                            memory_usage=performance_impact['memory_usage'],
                            findings_count=0,  # Will be updated from test results
                            complexity_score=complexity_score,
                            priority=rule.get('severity', 'WARNING')
                        )
            
            except Exception as e:
                print(f"Error analyzing {rule_file}: {e}")
        
        return rule_performance
    
    def _calculate_rule_complexity(self, rule: Dict[str, Any]) -> int:
        """Calculate complexity score for a rule"""
        complexity = 0
        
        # Base complexity
        complexity += 1
        
        # Pattern complexity
        if 'patterns' in rule:
            complexity += len(rule['patterns']) * 2
        
        if 'pattern-either' in rule:
            complexity += len(rule['pattern-either']) * 3
        
        if 'pattern-not' in rule:
            complexity += len(rule['pattern-not']) * 2
        
        # Taint analysis complexity
        if 'taint-mode' in rule:
            complexity += 10
        
        # Metavariable complexity
        if 'metavariable-pattern' in rule:
            complexity += len(rule['metavariable-pattern']) * 5
        
        # Fix complexity
        if 'fix' in rule:
            complexity += 2
        
        return complexity
    
    def _estimate_performance_impact(self, rule: Dict[str, Any]) -> Dict[str, float]:
        """Estimate performance impact of a rule"""
        # Base execution time (milliseconds)
        base_time = 1.0
        
        # Pattern complexity multiplier
        pattern_count = 0
        if 'patterns' in rule:
            pattern_count += len(rule['patterns'])
        if 'pattern-either' in rule:
            pattern_count += len(rule['pattern-either'])
        
        time_multiplier = 1 + (pattern_count * 0.5)
        
        # Taint analysis multiplier
        if 'taint-mode' in rule:
            time_multiplier *= 3
        
        # Memory usage estimation (MB)
        base_memory = 0.1
        memory_multiplier = 1 + (pattern_count * 0.2)
        
        if 'taint-mode' in rule:
            memory_multiplier *= 2
        
        return {
            'execution_time': base_time * time_multiplier,
            'memory_usage': base_memory * memory_multiplier
        }
    
    def optimize_configuration(self, config_path: str, performance_analysis: Dict[str, Any], 
                             rule_performance: Dict[str, RulePerformance]) -> OptimizationResult:
        """Optimize a configuration based on performance analysis"""
        print(f"Optimizing configuration: {config_path}")
        
        # Load original configuration
        with open(config_path, 'r') as f:
            config_content = f.read()
        
        # Parse configuration
        config_data = yaml.safe_load(config_content)
        
        # Get current performance metrics
        config_name = Path(config_path).stem
        current_performance = performance_analysis['config_performance'].get(config_name, {})
        
        # Calculate optimization targets
        scan_time_target = min(current_performance.get('scan_time', 0) * 0.8, self.optimization_targets.max_scan_time)
        memory_target = min(current_performance.get('memory_usage', 0) * 0.8, self.optimization_targets.max_memory_usage)
        
        # Identify rules to optimize
        optimization_plan = self._create_optimization_plan(
            config_data, rule_performance, scan_time_target, memory_target
        )
        
        # Apply optimizations
        optimized_config = self._apply_optimizations(config_data, optimization_plan)
        
        # Generate optimized configuration file
        optimized_config_path = self._save_optimized_config(config_path, optimized_config)
        
        # Calculate improvements
        improvements = self._calculate_improvements(
            current_performance, optimization_plan, rule_performance
        )
        
        return OptimizationResult(
            original_config=config_path,
            optimized_config=str(optimized_config_path),
            performance_improvements=improvements,
            security_coverage_change=optimization_plan['coverage_change'],
            rules_removed=optimization_plan['rules_removed'],
            rules_optimized=optimization_plan['rules_optimized'],
            recommendations=optimization_plan['recommendations']
        )
    
    def _create_optimization_plan(self, config_data: Any, rule_performance: Dict[str, RulePerformance],
                                scan_time_target: float, memory_target: float) -> Dict[str, Any]:
        """Create optimization plan for configuration"""
        plan = {
            'rules_removed': [],
            'rules_optimized': [],
            'coverage_change': 0.0,
            'recommendations': []
        }
        
        # Sort rules by performance impact (highest first)
        rule_impacts = []
        for rule_id, performance in rule_performance.items():
            if self._rule_in_config(rule_id, config_data):
                impact_score = (performance.execution_time * 0.6 + 
                              performance.memory_usage * 0.4) * performance.complexity_score
                rule_impacts.append((rule_id, impact_score, performance))
        
        # Sort by impact (highest first)
        rule_impacts.sort(key=lambda x: x[1], reverse=True)
        
        # Calculate current resource usage
        current_time = sum(p.execution_time for _, _, p in rule_impacts)
        current_memory = sum(p.memory_usage for _, _, p in rule_impacts)
        
        # Identify rules to remove or optimize
        removed_rules = []
        optimized_rules = []
        
        for rule_id, impact_score, performance in rule_impacts:
            # Check if removing this rule would help meet targets
            if (current_time - performance.execution_time <= scan_time_target and
                current_memory - performance.memory_usage <= memory_target):
                
                # Consider rule priority
                if performance.priority in ['INFO', 'WARNING']:
                    removed_rules.append(rule_id)
                    current_time -= performance.execution_time
                    current_memory -= performance.memory_usage
                    plan['recommendations'].append(f"Remove low-priority rule: {rule_id}")
                else:
                    # Try to optimize instead of remove
                    optimized_rules.append(rule_id)
                    plan['recommendations'].append(f"Optimize high-priority rule: {rule_id}")
        
        plan['rules_removed'] = removed_rules
        plan['rules_optimized'] = optimized_rules
        plan['coverage_change'] = -len(removed_rules) * 0.01  # Estimate 1% coverage loss per rule
        
        return plan
    
    def _rule_in_config(self, rule_id: str, config_data: Any) -> bool:
        """Check if a rule is included in the configuration"""
        if isinstance(config_data, list):
            for item in config_data:
                if isinstance(item, dict) and item.get('id') == rule_id:
                    return True
        elif isinstance(config_data, dict):
            # Handle rule pack references
            if 'rules' in config_data:
                return self._rule_in_config(rule_id, config_data['rules'])
        
        return False
    
    def _apply_optimizations(self, config_data: Any, optimization_plan: Dict[str, Any]) -> Any:
        """Apply optimizations to configuration"""
        optimized_config = config_data.copy() if isinstance(config_data, dict) else config_data
        
        # Remove specified rules
        if isinstance(optimized_config, list):
            optimized_config = [
                rule for rule in optimized_config 
                if not (isinstance(rule, dict) and rule.get('id') in optimization_plan['rules_removed'])
            ]
        
        # Optimize specified rules
        for rule_id in optimization_plan['rules_optimized']:
            optimized_config = self._optimize_rule(optimized_config, rule_id)
        
        return optimized_config
    
    def _optimize_rule(self, config_data: Any, rule_id: str) -> Any:
        """Optimize a specific rule"""
        if isinstance(config_data, list):
            for i, rule in enumerate(config_data):
                if isinstance(rule, dict) and rule.get('id') == rule_id:
                    # Apply rule-specific optimizations
                    optimized_rule = self._apply_rule_optimizations(rule)
                    config_data[i] = optimized_rule
                    break
        
        return config_data
    
    def _apply_rule_optimizations(self, rule: Dict[str, Any]) -> Dict[str, Any]:
        """Apply optimizations to a specific rule"""
        optimized_rule = rule.copy()
        
        # Optimize patterns for better performance
        if 'patterns' in optimized_rule:
            optimized_rule['patterns'] = self._optimize_patterns(optimized_rule['patterns'])
        
        if 'pattern-either' in optimized_rule:
            optimized_rule['pattern-either'] = self._optimize_patterns(optimized_rule['pattern-either'])
        
        # Add performance hints
        optimized_rule['metadata'] = optimized_rule.get('metadata', {})
        optimized_rule['metadata']['optimized'] = True
        optimized_rule['metadata']['optimization_date'] = time.strftime('%Y-%m-%d')
        
        return optimized_rule
    
    def _optimize_patterns(self, patterns: List[str]) -> List[str]:
        """Optimize patterns for better performance"""
        optimized_patterns = []
        
        for pattern in patterns:
            # Simplify complex patterns
            optimized_pattern = self._simplify_pattern(pattern)
            optimized_patterns.append(optimized_pattern)
        
        return optimized_patterns
    
    def _simplify_pattern(self, pattern: str) -> str:
        """Simplify a pattern for better performance"""
        # Basic pattern simplification
        # Remove unnecessary whitespace
        pattern = ' '.join(pattern.split())
        
        # Simplify complex regex patterns
        # This is a basic implementation - could be enhanced with more sophisticated pattern analysis
        
        return pattern
    
    def _save_optimized_config(self, original_path: str, optimized_config: Any) -> Path:
        """Save optimized configuration to file"""
        original_path = Path(original_path)
        optimized_path = original_path.parent / f"{original_path.stem}-optimized{original_path.suffix}"
        
        with open(optimized_path, 'w') as f:
            yaml.dump(optimized_config, f, default_flow_style=False, sort_keys=False)
        
        return optimized_path
    
    def _calculate_improvements(self, current_performance: Dict[str, float], 
                              optimization_plan: Dict[str, Any],
                              rule_performance: Dict[str, RulePerformance]) -> Dict[str, float]:
        """Calculate expected performance improvements"""
        improvements = {}
        
        # Calculate time improvement
        removed_time = sum(
            rule_performance[rule_id].execution_time 
            for rule_id in optimization_plan['rules_removed']
            if rule_id in rule_performance
        )
        
        current_time = current_performance.get('scan_time', 0)
        if current_time > 0:
            improvements['scan_time_improvement'] = (removed_time / current_time) * 100
        
        # Calculate memory improvement
        removed_memory = sum(
            rule_performance[rule_id].memory_usage 
            for rule_id in optimization_plan['rules_removed']
            if rule_id in rule_performance
        )
        
        current_memory = current_performance.get('memory_usage', 0)
        if current_memory > 0:
            improvements['memory_improvement'] = (removed_memory / current_memory) * 100
        
        return improvements
    
    def generate_optimization_report(self, optimization_results: List[OptimizationResult]) -> Dict[str, Any]:
        """Generate comprehensive optimization report"""
        report = {
            'timestamp': time.strftime('%Y-%m-%d %H:%M:%S'),
            'optimization_results': [],
            'summary': {
                'total_configs_optimized': len(optimization_results),
                'average_scan_time_improvement': 0.0,
                'average_memory_improvement': 0.0,
                'total_rules_removed': 0,
                'total_rules_optimized': 0
            }
        }
        
        total_scan_time_improvement = 0.0
        total_memory_improvement = 0.0
        total_rules_removed = 0
        total_rules_optimized = 0
        
        for result in optimization_results:
            report['optimization_results'].append(asdict(result))
            
            total_scan_time_improvement += result.performance_improvements.get('scan_time_improvement', 0)
            total_memory_improvement += result.performance_improvements.get('memory_improvement', 0)
            total_rules_removed += len(result.rules_removed)
            total_rules_optimized += len(result.rules_optimized)
        
        if optimization_results:
            report['summary']['average_scan_time_improvement'] = total_scan_time_improvement / len(optimization_results)
            report['summary']['average_memory_improvement'] = total_memory_improvement / len(optimization_results)
            report['summary']['total_rules_removed'] = total_rules_removed
            report['summary']['total_rules_optimized'] = total_rules_optimized
        
        return report
    
    def run_comprehensive_optimization(self, performance_report_path: str) -> Dict[str, Any]:
        """Run comprehensive optimization process"""
        print("Starting comprehensive performance optimization...")
        
        # Analyze performance data
        performance_analysis = self.analyze_performance_data(performance_report_path)
        
        # Analyze rule complexity
        rule_performance = self.analyze_rule_complexity()
        
        # Optimize each configuration
        optimization_results = []
        
        for config_file in self.configs_dir.glob("*.yaml"):
            if config_file.name not in ['optimized-15s.yaml', 'optimized-30s.yaml']:
                try:
                    result = self.optimize_configuration(
                        str(config_file), performance_analysis, rule_performance
                    )
                    optimization_results.append(result)
                except Exception as e:
                    print(f"Error optimizing {config_file}: {e}")
        
        # Generate optimization report
        report = self.generate_optimization_report(optimization_results)
        
        # Save report
        report_path = self.project_root / "comprehensive-optimization-report.json"
        with open(report_path, 'w') as f:
            json.dump(report, f, indent=2)
        
        print(f"Optimization report saved to: {report_path}")
        
        return report

def main():
    """Main function"""
    parser = argparse.ArgumentParser(description='Advanced Performance Optimizer for WordPress Semgrep Rules')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--performance-report', required=True, help='Path to performance report JSON file')
    parser.add_argument('--target-scan-time', type=float, default=30.0, help='Target scan time in seconds')
    parser.add_argument('--target-memory', type=float, default=500.0, help='Target memory usage in MB')
    parser.add_argument('--output', help='Output file for optimization report')
    
    args = parser.parse_args()
    
    # Initialize optimizer
    optimizer = AdvancedPerformanceOptimizer(args.project_root)
    
    # Set optimization targets
    optimizer.optimization_targets.max_scan_time = args.target_scan_time
    optimizer.optimization_targets.max_memory_usage = args.target_memory
    
    # Run comprehensive optimization
    report = optimizer.run_comprehensive_optimization(args.performance_report)
    
    # Print summary
    print(f"\n{'='*60}")
    print("OPTIMIZATION SUMMARY")
    print(f"{'='*60}")
    print(f"Configurations optimized: {report['summary']['total_configs_optimized']}")
    print(f"Average scan time improvement: {report['summary']['average_scan_time_improvement']:.1f}%")
    print(f"Average memory improvement: {report['summary']['average_memory_improvement']:.1f}%")
    print(f"Total rules removed: {report['summary']['total_rules_removed']}")
    print(f"Total rules optimized: {report['summary']['total_rules_optimized']}")
    
    if args.output:
        with open(args.output, 'w') as f:
            json.dump(report, f, indent=2)
        print(f"\nDetailed report saved to: {args.output}")

if __name__ == '__main__':
    main()
