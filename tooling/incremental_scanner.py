#!/usr/bin/env python3
"""
WordPress Semgrep Rules - Incremental Scanner

This module provides comprehensive incremental scanning capabilities:
- Git-based change detection
- File modification tracking
- Dependency-aware scanning
- Integration with cache manager
- Performance optimization

Author: WordPress Semgrep Rules Team
License: MIT
"""

import os
import json
import hashlib
import time
import subprocess
import logging
from pathlib import Path
from typing import Dict, List, Set, Optional, Tuple, Any
from dataclasses import dataclass, asdict
from datetime import datetime, timedelta
import yaml
import re

# Import cache manager
from cache_manager import CacheManager, get_cache_manager

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


@dataclass
class FileChange:
    """Represents a file change with metadata."""
    file_path: str
    change_type: str  # 'modified', 'added', 'deleted', 'renamed'
    old_path: Optional[str] = None
    hash_before: Optional[str] = None
    hash_after: Optional[str] = None
    timestamp: float = None
    size_bytes: int = 0
    dependencies: List[str] = None

    def __post_init__(self):
        if self.timestamp is None:
            self.timestamp = time.time()
        if self.dependencies is None:
            self.dependencies = []


@dataclass
class ScanContext:
    """Context for incremental scanning."""
    base_path: str
    config_file: str
    changed_files: List[FileChange]
    affected_files: Set[str]
    scan_paths: List[str]
    cache_key: str
    scan_type: str  # 'incremental', 'full', 'dependency'
    metadata: Dict[str, Any] = None

    def __post_init__(self):
        if self.metadata is None:
            self.metadata = {}


class IncrementalScanner:
    """
    Comprehensive incremental scanning system for WordPress Semgrep rules.
    
    Features:
    - Git-based change detection
    - File modification tracking
    - Dependency-aware scanning
    - Cache integration
    - Performance optimization
    - Cross-platform support
    """
    
    def __init__(self, base_path: str = ".", cache_manager: Optional[CacheManager] = None):
        """
        Initialize the incremental scanner.
        
        Args:
            base_path: Base path for scanning
            cache_manager: Cache manager instance
        """
        self.base_path = Path(base_path).resolve()
        self.cache_manager = cache_manager or get_cache_manager()
        
        # File tracking
        self.file_tracker_file = self.base_path / ".semgrep-file-tracker.json"
        self.scan_state_file = self.base_path / ".semgrep-scan-state.json"
        
        # Load existing state
        self.file_tracker = self._load_file_tracker()
        self.scan_state = self._load_scan_state()
        
        # WordPress-specific file patterns
        self.php_patterns = [
            "*.php",
            "*.inc",
            "*.module",
            "*.install",
            "*.test"
        ]
        
        # Configuration patterns
        self.config_patterns = [
            "*.yaml",
            "*.yml",
            "*.json",
            "*.xml",
            "*.ini"
        ]
        
        # Dependency patterns
        self.dependency_patterns = [
            "composer.json",
            "package.json",
            "wp-config.php",
            "functions.php",
            "*.deps"
        ]
        
        logger.info(f"Incremental scanner initialized: {self.base_path}")
    
    def _load_file_tracker(self) -> Dict[str, Any]:
        """Load file tracking data."""
        if self.file_tracker_file.exists():
            try:
                with open(self.file_tracker_file, 'r') as f:
                    return json.load(f)
            except Exception as e:
                logger.warning(f"Error loading file tracker: {e}")
        return {
            "files": {},
            "last_scan": None,
            "version": "1.0"
        }
    
    def _save_file_tracker(self) -> None:
        """Save file tracking data."""
        try:
            with open(self.file_tracker_file, 'w') as f:
                json.dump(self.file_tracker, f, indent=2)
        except Exception as e:
            logger.error(f"Error saving file tracker: {e}")
    
    def _load_scan_state(self) -> Dict[str, Any]:
        """Load scan state data."""
        if self.scan_state_file.exists():
            try:
                with open(self.scan_state_file, 'r') as f:
                    return json.load(f)
            except Exception as e:
                logger.warning(f"Error loading scan state: {e}")
        return {
            "last_full_scan": None,
            "last_incremental_scan": None,
            "scan_history": [],
            "performance_metrics": {}
        }
    
    def _save_scan_state(self) -> None:
        """Save scan state data."""
        try:
            with open(self.scan_state_file, 'w') as f:
                json.dump(self.scan_state, f, indent=2)
        except Exception as e:
            logger.error(f"Error saving scan state: {e}")
    
    def _calculate_file_hash(self, file_path: Path) -> str:
        """Calculate SHA256 hash of file content."""
        try:
            with open(file_path, 'rb') as f:
                return hashlib.sha256(f.read()).hexdigest()
        except Exception as e:
            logger.warning(f"Error calculating hash for {file_path}: {e}")
            return ""
    
    def _get_file_metadata(self, file_path: Path) -> Dict[str, Any]:
        """Get file metadata."""
        try:
            stat = file_path.stat()
            return {
                "size_bytes": stat.st_size,
                "modified_time": stat.st_mtime,
                "hash": self._calculate_file_hash(file_path)
            }
        except Exception as e:
            logger.warning(f"Error getting metadata for {file_path}: {e}")
            return {}
    
    def _is_git_repository(self) -> bool:
        """Check if current directory is a git repository."""
        return (self.base_path / ".git").exists()
    
    def _get_git_changes(self, since: Optional[str] = None) -> List[FileChange]:
        """Get changed files from git."""
        if not self._is_git_repository():
            return []
        
        try:
            # Get git status for untracked and modified files
            cmd = ["git", "status", "--porcelain"]
            result = subprocess.run(cmd, capture_output=True, text=True, cwd=self.base_path)
            
            if result.returncode != 0:
                logger.warning("Git status command failed")
                return []
            
            changes = []
            for line in result.stdout.strip().split('\n'):
                if not line:
                    continue
                
                status = line[:2].strip()
                file_path = line[3:]
                
                if not file_path.endswith('.php'):
                    continue
                
                change_type = self._map_git_status(status)
                if change_type:
                    changes.append(FileChange(
                        file_path=file_path,
                        change_type=change_type
                    ))
            
            # Get changes since last commit if specified
            if since:
                cmd = ["git", "diff", "--name-only", since]
                result = subprocess.run(cmd, capture_output=True, text=True, cwd=self.base_path)
                
                if result.returncode == 0:
                    for file_path in result.stdout.strip().split('\n'):
                        if file_path and file_path.endswith('.php'):
                            changes.append(FileChange(
                                file_path=file_path,
                                change_type="modified"
                            ))
            
            return changes
            
        except Exception as e:
            logger.error(f"Error getting git changes: {e}")
            return []
    
    def _map_git_status(self, status: str) -> Optional[str]:
        """Map git status to change type."""
        status_map = {
            "M": "modified",
            "A": "added",
            "D": "deleted",
            "R": "renamed",
            "C": "modified",
            "U": "modified"
        }
        return status_map.get(status, None)
    
    def _detect_file_changes(self) -> List[FileChange]:
        """Detect file changes using file system monitoring."""
        changes = []
        current_time = time.time()
        
        # Check tracked files for changes
        for file_path_str, metadata in self.file_tracker.get("files", {}).items():
            file_path = self.base_path / file_path_str
            
            if not file_path.exists():
                # File was deleted
                changes.append(FileChange(
                    file_path=file_path_str,
                    change_type="deleted",
                    hash_before=metadata.get("hash"),
                    timestamp=current_time
                ))
                continue
            
            # Check if file was modified
            current_metadata = self._get_file_metadata(file_path)
            if not current_metadata:
                continue
            
            old_hash = metadata.get("hash")
            new_hash = current_metadata.get("hash")
            
            if old_hash and new_hash and old_hash != new_hash:
                changes.append(FileChange(
                    file_path=file_path_str,
                    change_type="modified",
                    hash_before=old_hash,
                    hash_after=new_hash,
                    timestamp=current_time,
                    size_bytes=current_metadata.get("size_bytes", 0)
                ))
        
        # Check for new files
        for pattern in self.php_patterns:
            for file_path in self.base_path.rglob(pattern):
                file_path_str = str(file_path.relative_to(self.base_path))
                
                if file_path_str not in self.file_tracker.get("files", {}):
                    metadata = self._get_file_metadata(file_path)
                    if metadata:
                        changes.append(FileChange(
                            file_path=file_path_str,
                            change_type="added",
                            hash_after=metadata.get("hash"),
                            timestamp=current_time,
                            size_bytes=metadata.get("size_bytes", 0)
                        ))
        
        return changes
    
    def _analyze_dependencies(self, changed_files: List[FileChange]) -> Set[str]:
        """Analyze dependencies of changed files."""
        affected_files = set()
        
        for change in changed_files:
            if change.change_type == "deleted":
                continue
            
            file_path = self.base_path / change.file_path
            
            # Add the changed file itself
            affected_files.add(change.file_path)
            
            # Analyze PHP dependencies
            if file_path.suffix == '.php':
                dependencies = self._analyze_php_dependencies(file_path)
                affected_files.update(dependencies)
            
            # Analyze configuration dependencies
            if file_path.suffix in ['.yaml', '.yml', '.json']:
                dependencies = self._analyze_config_dependencies(file_path)
                affected_files.update(dependencies)
        
        return affected_files
    
    def _analyze_php_dependencies(self, file_path: Path) -> Set[str]:
        """Analyze PHP file dependencies."""
        dependencies = set()
        
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for include/require statements
            include_patterns = [
                r'include\s*[\'"]([^\'"]+\.php)[\'"]',
                r'require\s*[\'"]([^\'"]+\.php)[\'"]',
                r'include_once\s*[\'"]([^\'"]+\.php)[\'"]',
                r'require_once\s*[\'"]([^\'"]+\.php)[\'"]'
            ]
            
            for pattern in include_patterns:
                matches = re.findall(pattern, content)
                for match in matches:
                    # Resolve relative paths
                    if match.startswith('./'):
                        dep_path = file_path.parent / match[2:]
                    elif match.startswith('../'):
                        dep_path = file_path.parent / match
                    else:
                        dep_path = file_path.parent / match
                    
                    if dep_path.exists():
                        dependencies.add(str(dep_path.relative_to(self.base_path)))
            
            # Look for WordPress-specific dependencies
            wp_patterns = [
                r'get_template_part\s*\(\s*[\'"]([^\'"]+)[\'"]',
                r'locate_template\s*\(\s*[\'"]([^\'"]+)[\'"]',
                r'load_template\s*\(\s*[\'"]([^\'"]+)[\'"]'
            ]
            
            for pattern in wp_patterns:
                matches = re.findall(pattern, content)
                for match in matches:
                    if match.endswith('.php'):
                        dep_path = self.base_path / match
                        if dep_path.exists():
                            dependencies.add(str(dep_path.relative_to(self.base_path)))
        
        except Exception as e:
            logger.warning(f"Error analyzing PHP dependencies for {file_path}: {e}")
        
        return dependencies
    
    def _analyze_config_dependencies(self, file_path: Path) -> Set[str]:
        """Analyze configuration file dependencies."""
        dependencies = set()
        
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Parse YAML/JSON configuration
            if file_path.suffix in ['.yaml', '.yml']:
                try:
                    config = yaml.safe_load(content)
                    dependencies.update(self._extract_config_dependencies(config))
                except yaml.YAMLError:
                    pass
            elif file_path.suffix == '.json':
                try:
                    config = json.loads(content)
                    dependencies.update(self._extract_config_dependencies(config))
                except json.JSONDecodeError:
                    pass
        
        except Exception as e:
            logger.warning(f"Error analyzing config dependencies for {file_path}: {e}")
        
        return dependencies
    
    def _extract_config_dependencies(self, config: Any) -> Set[str]:
        """Extract dependencies from configuration object."""
        dependencies = set()
        
        if isinstance(config, dict):
            for key, value in config.items():
                if key in ['rules', 'include', 'exclude'] and isinstance(value, list):
                    for item in value:
                        if isinstance(item, str) and item.endswith('.php'):
                            dependencies.add(item)
                elif isinstance(value, (dict, list)):
                    dependencies.update(self._extract_config_dependencies(value))
        elif isinstance(config, list):
            for item in config:
                dependencies.update(self._extract_config_dependencies(item))
        
        return dependencies
    
    def _update_file_tracker(self, changed_files: List[FileChange]) -> None:
        """Update file tracker with current state."""
        current_time = time.time()
        
        # Remove deleted files
        for change in changed_files:
            if change.change_type == "deleted":
                self.file_tracker["files"].pop(change.file_path, None)
        
        # Update/add modified/added files
        for change in changed_files:
            if change.change_type in ["modified", "added"]:
                file_path = self.base_path / change.file_path
                if file_path.exists():
                    metadata = self._get_file_metadata(file_path)
                    if metadata:
                        self.file_tracker["files"][change.file_path] = metadata
        
        # Update last scan time
        self.file_tracker["last_scan"] = current_time
        
        # Save tracker
        self._save_file_tracker()
    
    def _generate_scan_paths(self, affected_files: Set[str]) -> List[str]:
        """Generate scan paths for affected files."""
        scan_paths = []
        
        for file_path in affected_files:
            full_path = self.base_path / file_path
            if full_path.exists():
                scan_paths.append(str(full_path))
        
        # Add directories containing affected files
        directories = set()
        for file_path in affected_files:
            full_path = self.base_path / file_path
            if full_path.exists():
                directories.add(str(full_path.parent))
        
        scan_paths.extend(list(directories))
        
        return list(set(scan_paths))  # Remove duplicates
    
    def _generate_cache_key(self, config_file: str, scan_paths: List[str]) -> str:
        """Generate cache key for incremental scan."""
        # Create a hash of config file and scan paths
        content = f"{config_file}:{':'.join(sorted(scan_paths))}"
        return hashlib.sha256(content.encode()).hexdigest()
    
    def detect_changes(self, use_git: bool = True) -> List[FileChange]:
        """
        Detect changes in the codebase.
        
        Args:
            use_git: Whether to use git for change detection
            
        Returns:
            List of file changes
        """
        changes = []
        
        if use_git and self._is_git_repository():
            logger.info("Using git for change detection")
            changes = self._get_git_changes()
        else:
            logger.info("Using file system monitoring for change detection")
            changes = self._detect_file_changes()
        
        logger.info(f"Detected {len(changes)} file changes")
        return changes
    
    def analyze_impact(self, changed_files: List[FileChange]) -> Set[str]:
        """
        Analyze the impact of changes on the codebase.
        
        Args:
            changed_files: List of changed files
            
        Returns:
            Set of affected files (including dependencies)
        """
        logger.info("Analyzing change impact...")
        affected_files = self._analyze_dependencies(changed_files)
        logger.info(f"Impact analysis: {len(affected_files)} files affected")
        return affected_files
    
    def prepare_scan_context(self, config_file: str, changed_files: List[FileChange], 
                           affected_files: Set[str]) -> ScanContext:
        """
        Prepare scan context for incremental scanning.
        
        Args:
            config_file: Configuration file path
            changed_files: List of changed files
            affected_files: Set of affected files
            
        Returns:
            Scan context
        """
        scan_paths = self._generate_scan_paths(affected_files)
        cache_key = self._generate_cache_key(config_file, scan_paths)
        
        context = ScanContext(
            base_path=str(self.base_path),
            config_file=config_file,
            changed_files=changed_files,
            affected_files=affected_files,
            scan_paths=scan_paths,
            cache_key=cache_key,
            scan_type="incremental" if changed_files else "full",
            metadata={
                "changed_files_count": len(changed_files),
                "affected_files_count": len(affected_files),
                "scan_paths_count": len(scan_paths),
                "timestamp": time.time()
            }
        )
        
        return context
    
    def should_perform_full_scan(self, changed_files: List[FileChange], 
                                last_full_scan: Optional[float] = None) -> bool:
        """
        Determine if a full scan should be performed.
        
        Args:
            changed_files: List of changed files
            last_full_scan: Timestamp of last full scan
            
        Returns:
            True if full scan should be performed
        """
        # If no changes, no need for scan
        if not changed_files:
            return False
        
        # If too many files changed, do full scan
        if len(changed_files) > 50:
            logger.info("Too many changes detected, performing full scan")
            return True
        
        # If last full scan was more than 24 hours ago, do full scan
        if last_full_scan:
            hours_since_full_scan = (time.time() - last_full_scan) / 3600
            if hours_since_full_scan > 24:
                logger.info("Last full scan was more than 24 hours ago, performing full scan")
                return True
        
        # If critical files changed, do full scan
        critical_patterns = [
            "wp-config.php",
            "functions.php",
            "composer.json",
            "package.json",
            "*.deps"
        ]
        
        for change in changed_files:
            for pattern in critical_patterns:
                if change.file_path.endswith(pattern.replace('*', '')) or pattern in change.file_path:
                    logger.info(f"Critical file changed: {change.file_path}, performing full scan")
                    return True
        
        return False
    
    def update_scan_state(self, context: ScanContext, scan_duration: float, 
                         findings_count: int) -> None:
        """
        Update scan state with results.
        
        Args:
            context: Scan context
            scan_duration: Scan duration in seconds
            findings_count: Number of findings
        """
        current_time = time.time()
        
        # Update scan history
        scan_record = {
            "timestamp": current_time,
            "scan_type": context.scan_type,
            "config_file": context.config_file,
            "changed_files_count": len(context.changed_files),
            "affected_files_count": len(context.affected_files),
            "scan_paths_count": len(context.scan_paths),
            "scan_duration": scan_duration,
            "findings_count": findings_count,
            "cache_key": context.cache_key
        }
        
        self.scan_state["scan_history"].append(scan_record)
        
        # Keep only last 100 scans
        if len(self.scan_state["scan_history"]) > 100:
            self.scan_state["scan_history"] = self.scan_state["scan_history"][-100:]
        
        # Update last scan times
        if context.scan_type == "full":
            self.scan_state["last_full_scan"] = current_time
        else:
            self.scan_state["last_incremental_scan"] = current_time
        
        # Update performance metrics
        self.scan_state["performance_metrics"][context.scan_type] = {
            "last_duration": scan_duration,
            "avg_duration": self._calculate_avg_duration(context.scan_type),
            "total_scans": len([s for s in self.scan_state["scan_history"] 
                              if s["scan_type"] == context.scan_type])
        }
        
        # Update file tracker
        self._update_file_tracker(context.changed_files)
        
        # Save state
        self._save_scan_state()
    
    def _calculate_avg_duration(self, scan_type: str) -> float:
        """Calculate average duration for scan type."""
        durations = [s["scan_duration"] for s in self.scan_state["scan_history"] 
                    if s["scan_type"] == scan_type]
        return sum(durations) / len(durations) if durations else 0.0
    
    def get_scan_statistics(self) -> Dict[str, Any]:
        """Get scan statistics."""
        return {
            "file_tracker": {
                "total_files": len(self.file_tracker.get("files", {})),
                "last_scan": self.file_tracker.get("last_scan")
            },
            "scan_state": {
                "last_full_scan": self.scan_state.get("last_full_scan"),
                "last_incremental_scan": self.scan_state.get("last_incremental_scan"),
                "total_scans": len(self.scan_state.get("scan_history", [])),
                "performance_metrics": self.scan_state.get("performance_metrics", {})
            }
        }
    
    def cleanup_old_data(self, max_age_days: int = 30) -> int:
        """
        Clean up old scan data.
        
        Args:
            max_age_days: Maximum age of data to keep
            
        Returns:
            Number of records cleaned up
        """
        cutoff_time = time.time() - (max_age_days * 24 * 3600)
        
        # Clean up scan history
        original_count = len(self.scan_state.get("scan_history", []))
        self.scan_state["scan_history"] = [
            record for record in self.scan_state.get("scan_history", [])
            if record.get("timestamp", 0) > cutoff_time
        ]
        cleaned_count = original_count - len(self.scan_state["scan_history"])
        
        # Save state
        self._save_scan_state()
        
        logger.info(f"Cleaned up {cleaned_count} old scan records")
        return cleaned_count


def get_incremental_scanner(base_path: str = ".", cache_manager: Optional[CacheManager] = None) -> IncrementalScanner:
    """Get incremental scanner instance."""
    return IncrementalScanner(base_path, cache_manager)


def detect_changes_and_prepare_scan(config_file: str, base_path: str = ".", 
                                  use_git: bool = True) -> Tuple[ScanContext, bool]:
    """
    Detect changes and prepare scan context.
    
    Args:
        config_file: Configuration file path
        base_path: Base path for scanning
        use_git: Whether to use git for change detection
        
    Returns:
        Tuple of (scan_context, should_perform_full_scan)
    """
    scanner = get_incremental_scanner(base_path)
    
    # Detect changes
    changed_files = scanner.detect_changes(use_git)
    
    # Determine if full scan is needed
    last_full_scan = scanner.scan_state.get("last_full_scan")
    should_full_scan = scanner.should_perform_full_scan(changed_files, last_full_scan)
    
    if should_full_scan:
        # For full scan, include all PHP files
        all_php_files = set()
        for pattern in scanner.php_patterns:
            for file_path in Path(base_path).rglob(pattern):
                all_php_files.add(str(file_path.relative_to(Path(base_path))))
        
        context = scanner.prepare_scan_context(config_file, changed_files, all_php_files)
        context.scan_type = "full"
    else:
        # For incremental scan, analyze impact
        affected_files = scanner.analyze_impact(changed_files)
        context = scanner.prepare_scan_context(config_file, changed_files, affected_files)
    
    return context, should_full_scan


if __name__ == "__main__":
    # Example usage
    import sys
    
    if len(sys.argv) < 2:
        print("Usage: python incremental_scanner.py <config_file> [base_path]")
        sys.exit(1)
    
    config_file = sys.argv[1]
    base_path = sys.argv[2] if len(sys.argv) > 2 else "."
    
    context, should_full_scan = detect_changes_and_prepare_scan(config_file, base_path)
    
    print(f"Scan Type: {context.scan_type}")
    print(f"Changed Files: {len(context.changed_files)}")
    print(f"Affected Files: {len(context.affected_files)}")
    print(f"Scan Paths: {len(context.scan_paths)}")
    print(f"Cache Key: {context.cache_key}")
    
    if should_full_scan:
        print("Recommendation: Perform full scan")
    else:
        print("Recommendation: Perform incremental scan")
