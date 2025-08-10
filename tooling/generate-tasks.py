#!/usr/bin/env python3
"""
Task Generator for WordPress Semgrep Rules Development

This script parses the PRD document and generates a structured task list
for development of the WordPress Semgrep rules project.
"""

import argparse
import json
import sys
from pathlib import Path
from datetime import datetime, timedelta

class TaskGenerator:
    def __init__(self):
        self.tasks = []
        self.task_id = 1
        
    def add_task(self, title, description, phase, priority="medium", dependencies=None, 
                 estimated_hours=8, category="development"):
        """Add a task to the task list"""
        task = {
            "id": self.task_id,
            "title": title,
            "description": description,
            "phase": phase,
            "priority": priority,
            "dependencies": dependencies or [],
            "estimated_hours": estimated_hours,
            "category": category,
            "status": "pending",
            "created": datetime.now().isoformat()
        }
        self.tasks.append(task)
        self.task_id += 1
        return task["id"]
    
    def generate_foundation_tasks(self):
        """Generate Phase 1: Foundation tasks"""
        print("Generating Phase 1: Foundation tasks...")
        
        # Project Setup
        self.add_task(
            "Initialize Project Structure",
            "Set up the complete project directory structure with all necessary folders and files",
            "Phase 1",
            "high",
            estimated_hours=4
        )
        
        # Core WordPress Security Rules
        self.add_task(
            "Create Nonce Verification Rules",
            "Develop comprehensive rules for WordPress nonce creation, verification, and lifecycle management",
            "Phase 1",
            "high",
            estimated_hours=16,
            category="rules"
        )
        
        self.add_task(
            "Create Capability Check Rules",
            "Develop rules for WordPress user capability checks and authorization patterns",
            "Phase 1",
            "high",
            estimated_hours=12,
            category="rules"
        )
        
        self.add_task(
            "Create Sanitization Function Rules",
            "Develop rules for WordPress sanitization function usage and best practices",
            "Phase 1",
            "high",
            estimated_hours=12,
            category="rules"
        )
        
        # Testing Infrastructure
        self.add_task(
            "Create Vulnerable Test Cases",
            "Develop comprehensive test cases for nonce, capability, and sanitization vulnerabilities",
            "Phase 1",
            "high",
            estimated_hours=8,
            category="testing"
        )
        
        self.add_task(
            "Create Safe Test Cases",
            "Develop test cases for proper nonce, capability, and sanitization usage",
            "Phase 1",
            "high",
            estimated_hours=8,
            category="testing"
        )
        
        # Documentation
        self.add_task(
            "Create Rule Documentation",
            "Document all Phase 1 rules with examples, explanations, and remediation guidance",
            "Phase 1",
            "medium",
            estimated_hours=12,
            category="documentation"
        )
        
        # Configuration
        self.add_task(
            "Create Basic Configuration",
            "Develop basic.yaml configuration with essential security rules",
            "Phase 1",
            "high",
            estimated_hours=4,
            category="configuration"
        )
    
    def generate_enhancement_tasks(self):
        """Generate Phase 2: Enhancement tasks"""
        print("Generating Phase 2: Enhancement tasks...")
        
        # Advanced Security Rules
        self.add_task(
            "Create REST API Security Rules",
            "Develop rules for WordPress REST API endpoint security and authentication",
            "Phase 2",
            "high",
            estimated_hours=16,
            category="rules"
        )
        
        self.add_task(
            "Create AJAX Security Rules",
            "Develop rules for WordPress AJAX endpoint security and validation",
            "Phase 2",
            "high",
            estimated_hours=12,
            category="rules"
        )
        
        self.add_task(
            "Create SQL Injection Rules",
            "Develop comprehensive rules for SQL injection prevention in WordPress",
            "Phase 2",
            "high",
            estimated_hours=16,
            category="rules"
        )
        
        self.add_task(
            "Create XSS Prevention Rules",
            "Develop rules for XSS prevention with context-aware escaping",
            "Phase 2",
            "high",
            estimated_hours=16,
            category="rules"
        )
        
        # Taint Analysis
        self.add_task(
            "Implement Taint Analysis Framework",
            "Set up taint analysis infrastructure with sources, sinks, and sanitizers",
            "Phase 2",
            "high",
            estimated_hours=20,
            category="taint-analysis"
        )
        
        self.add_task(
            "Create XSS Taint Rules",
            "Develop taint analysis rules for XSS vulnerability detection",
            "Phase 2",
            "high",
            estimated_hours=12,
            category="taint-analysis"
        )
        
        self.add_task(
            "Create SQL Injection Taint Rules",
            "Develop taint analysis rules for SQL injection detection",
            "Phase 2",
            "high",
            estimated_hours=12,
            category="taint-analysis"
        )
        
        # Performance Optimization
        self.add_task(
            "Optimize Rule Performance",
            "Optimize rule performance to meet <30 second scan time requirement",
            "Phase 2",
            "medium",
            estimated_hours=16,
            category="performance"
        )
        
        # Enhanced Testing
        self.add_task(
            "Create Advanced Test Cases",
            "Develop test cases for REST API, AJAX, SQL injection, and XSS vulnerabilities",
            "Phase 2",
            "high",
            estimated_hours=16,
            category="testing"
        )
        
        self.add_task(
            "Implement Automated Testing",
            "Set up automated test execution and regression testing",
            "Phase 2",
            "high",
            estimated_hours=12,
            category="testing"
        )
    
    def generate_integration_tasks(self):
        """Generate Phase 3: Integration tasks"""
        print("Generating Phase 3: Integration tasks...")
        
        # CI/CD Integration
        self.add_task(
            "Create GitHub Actions Integration",
            "Develop GitHub Actions workflow for automated security scanning",
            "Phase 3",
            "high",
            estimated_hours=12,
            category="integration"
        )
        
        self.add_task(
            "Create Pre-commit Hook",
            "Develop pre-commit hook for automated security checks",
            "Phase 3",
            "high",
            estimated_hours=8,
            category="integration"
        )
        
        # IDE Integration
        self.add_task(
            "Create VS Code Extension",
            "Develop VS Code extension for real-time security scanning",
            "Phase 3",
            "medium",
            estimated_hours=24,
            category="integration"
        )
        
        self.add_task(
            "Create Cursor Integration",
            "Develop integration for Cursor IDE with real-time scanning",
            "Phase 3",
            "medium",
            estimated_hours=16,
            category="integration"
        )
        
        # Advanced Tooling
        self.add_task(
            "Enhance Runner Scripts",
            "Enhance PowerShell and Bash runner scripts with advanced features",
            "Phase 3",
            "high",
            estimated_hours=12,
            category="tooling"
        )
        
        self.add_task(
            "Create Configuration Validator",
            "Develop configuration validation and error handling",
            "Phase 3",
            "medium",
            estimated_hours=8,
            category="tooling"
        )
        
        # Community Features
        self.add_task(
            "Create Contribution Guidelines",
            "Develop comprehensive contribution guidelines and processes",
            "Phase 3",
            "medium",
            estimated_hours=8,
            category="community"
        )
        
        self.add_task(
            "Create Issue Templates",
            "Develop GitHub issue templates for bug reports and feature requests",
            "Phase 3",
            "low",
            estimated_hours=4,
            category="community"
        )
    
    def generate_optimization_tasks(self):
        """Generate Phase 4: Optimization tasks"""
        print("Generating Phase 4: Optimization tasks...")
        
        # Performance Optimization
        self.add_task(
            "Implement Caching System",
            "Implement caching for repeated scans and rule compilation",
            "Phase 4",
            "high",
            estimated_hours=16,
            category="performance"
        )
        
        self.add_task(
            "Implement Incremental Scanning",
            "Implement incremental scanning for changed files only",
            "Phase 4",
            "high",
            estimated_hours=20,
            category="performance"
        )
        
        # Advanced Features
        self.add_task(
            "Create Rule Metrics Dashboard",
            "Develop dashboard for tracking rule performance and false positive rates",
            "Phase 4",
            "medium",
            estimated_hours=16,
            category="features"
        )
        
        self.add_task(
            "Implement Rule Auto-fix",
            "Implement automatic fixing for simple security issues",
            "Phase 4",
            "medium",
            estimated_hours=24,
            category="features"
        )
        
        # Production Readiness
        self.add_task(
            "Security Audit",
            "Conduct comprehensive security audit of all rules and tooling",
            "Phase 4",
            "high",
            estimated_hours=16,
            category="security"
        )
        
        self.add_task(
            "Performance Testing",
            "Conduct comprehensive performance testing and optimization",
            "Phase 4",
            "high",
            estimated_hours=12,
            category="performance"
        )
        
        self.add_task(
            "Create Production Documentation",
            "Create production-ready documentation and deployment guides",
            "Phase 4",
            "high",
            estimated_hours=16,
            category="documentation"
        )
    
    def generate_all_tasks(self):
        """Generate all tasks for all phases"""
        self.generate_foundation_tasks()
        self.generate_enhancement_tasks()
        self.generate_integration_tasks()
        self.generate_optimization_tasks()
        
        # Add dependencies
        self._add_task_dependencies()
    
    def _add_task_dependencies(self):
        """Add logical dependencies between tasks"""
        # Find tasks by title for easier dependency management
        task_map = {task["title"]: task["id"] for task in self.tasks}
        
        # Phase 1 dependencies
        if "Initialize Project Structure" in task_map:
            init_id = task_map["Initialize Project Structure"]
            for task in self.tasks:
                if task["phase"] == "Phase 1" and task["id"] != init_id:
                    task["dependencies"].append(init_id)
        
        # Phase 2 dependencies
        if "Create Advanced Test Cases" in task_map:
            test_id = task_map["Create Advanced Test Cases"]
            for task in self.tasks:
                if task["title"] in ["Create REST API Security Rules", "Create AJAX Security Rules", 
                                   "Create SQL Injection Rules", "Create XSS Prevention Rules"]:
                    task["dependencies"].append(test_id)
        
        # Phase 3 dependencies
        if "Implement Automated Testing" in task_map:
            auto_test_id = task_map["Implement Automated Testing"]
            for task in self.tasks:
                if task["phase"] == "Phase 3" and "integration" in task["category"]:
                    task["dependencies"].append(auto_test_id)
    
    def save_tasks(self, output_file):
        """Save tasks to JSON file"""
        output = {
            "project": "WordPress Semgrep Security Rules",
            "version": "1.0.0",
            "generated": datetime.now().isoformat(),
            "total_tasks": len(self.tasks),
            "phases": {
                "Phase 1": len([t for t in self.tasks if t["phase"] == "Phase 1"]),
                "Phase 2": len([t for t in self.tasks if t["phase"] == "Phase 2"]),
                "Phase 3": len([t for t in self.tasks if t["phase"] == "Phase 3"]),
                "Phase 4": len([t for t in self.tasks if t["phase"] == "Phase 4"])
            },
            "categories": {
                "rules": len([t for t in self.tasks if t["category"] == "rules"]),
                "testing": len([t for t in self.tasks if t["category"] == "testing"]),
                "documentation": len([t for t in self.tasks if t["category"] == "documentation"]),
                "configuration": len([t for t in self.tasks if t["category"] == "configuration"]),
                "integration": len([t for t in self.tasks if t["category"] == "integration"]),
                "tooling": len([t for t in self.tasks if t["category"] == "tooling"]),
                "performance": len([t for t in self.tasks if t["category"] == "performance"]),
                "taint-analysis": len([t for t in self.tasks if t["category"] == "taint-analysis"]),
                "community": len([t for t in self.tasks if t["category"] == "community"]),
                "features": len([t for t in self.tasks if t["category"] == "features"]),
                "security": len([t for t in self.tasks if t["category"] == "security"])
            },
            "tasks": self.tasks
        }
        
        with open(output_file, 'w') as f:
            json.dump(output, f, indent=2)
        
        print(f"Generated {len(self.tasks)} tasks and saved to {output_file}")
        
        # Print summary
        print("\nTask Summary:")
        print(f"Total Tasks: {len(self.tasks)}")
        print(f"Total Estimated Hours: {sum(t['estimated_hours'] for t in self.tasks)}")
        print("\nBy Phase:")
        for phase in ["Phase 1", "Phase 2", "Phase 3", "Phase 4"]:
            phase_tasks = [t for t in self.tasks if t["phase"] == phase]
            hours = sum(t["estimated_hours"] for t in phase_tasks)
            print(f"  {phase}: {len(phase_tasks)} tasks, {hours} hours")
        
        print("\nBy Category:")
        for category in ["rules", "testing", "documentation", "configuration", "integration", 
                        "tooling", "performance", "taint-analysis", "community", "features", "security"]:
            cat_tasks = [t for t in self.tasks if t["category"] == category]
            if cat_tasks:
                hours = sum(t["estimated_hours"] for t in cat_tasks)
                print(f"  {category}: {len(cat_tasks)} tasks, {hours} hours")

def main():
    parser = argparse.ArgumentParser(description="Generate tasks from PRD")
    parser.add_argument("--output", default="tasks.json", help="Output file for tasks")
    parser.add_argument("--phase", choices=["1", "2", "3", "4", "all"], default="all",
                       help="Generate tasks for specific phase or all phases")
    
    args = parser.parse_args()
    
    generator = TaskGenerator()
    
    if args.phase == "all":
        generator.generate_all_tasks()
    elif args.phase == "1":
        generator.generate_foundation_tasks()
    elif args.phase == "2":
        generator.generate_enhancement_tasks()
    elif args.phase == "3":
        generator.generate_integration_tasks()
    elif args.phase == "4":
        generator.generate_optimization_tasks()
    
    generator.save_tasks(args.output)

if __name__ == "__main__":
    main()
