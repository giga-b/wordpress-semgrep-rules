#!/usr/bin/env python3
"""
Shared metrics utilities for Semgrep corpus-based testing.

Capabilities:
- Load optional ground-truth labels for corpus-based evaluation
- Compute TP/FP/FN from semgrep results and optional labels
- Calculate precision/recall/F1/FPR/FNR from counts

This module is importable by test scripts via:
    sys.path.append(str((Path(__file__).parent).resolve()))
then: from _lib.metrics import ...
"""

from __future__ import annotations

import json
from pathlib import Path
from typing import Dict, List, Optional, Tuple


def load_ground_truth(project_root: Path) -> Dict:
    """Load optional ground-truth labels.

    Expected path: corpus/labels/ground-truth.json
    Flexible schema supported:
    - { "labels": [ {"file": "relative/path.php", "rule_id": "...", "vuln_class": "xss", "count": 1 } ] }
    - or a dict keyed by file path with nested counts

    Returns an index of form:
      { (rel_path): { 'by_rule': {rule_id: count}, 'by_class': {vuln_class: count} } }
    Missing file gracefully returns {}.
    """
    labels_dir = project_root / 'corpus' / 'labels'
    labels_file = labels_dir / 'ground-truth.json'
    if not labels_file.exists():
        return {}

    try:
        with open(labels_file, 'r', encoding='utf-8') as f:
            data = json.load(f)
    except Exception:
        return {}

    index: Dict[str, Dict[str, Dict[str, int]]] = {}

    def ensure_entry(rel_path: str):
        if rel_path not in index:
            index[rel_path] = {'by_rule': {}, 'by_class': {}}
        return index[rel_path]

    if isinstance(data, dict) and 'labels' in data and isinstance(data['labels'], list):
        for entry in data['labels']:
            rel_path = str(entry.get('file', '')).replace('\\', '/')
            if not rel_path:
                continue
            rule_id = entry.get('rule_id')
            vclass = entry.get('vuln_class')
            count = int(entry.get('count', 1))
            slot = ensure_entry(rel_path)
            if rule_id:
                slot['by_rule'][rule_id] = slot['by_rule'].get(rule_id, 0) + count
            if vclass:
                slot['by_class'][vclass] = slot['by_class'].get(vclass, 0) + count
        return index

    # Fallback: dict keyed by file path
    if isinstance(data, dict):
        for rel_path, detail in data.items():
            slot = ensure_entry(str(rel_path).replace('\\', '/'))
            if isinstance(detail, dict):
                for rule_id, cnt in detail.get('by_rule', {}).items():
                    slot['by_rule'][rule_id] = slot['by_rule'].get(rule_id, 0) + int(cnt)
                for vclass, cnt in detail.get('by_class', {}).items():
                    slot['by_class'][vclass] = slot['by_class'].get(vclass, 0) + int(cnt)
    return index


def extract_check_id(result_item: Dict) -> Optional[str]:
    """Extract semgrep check_id from a result item, when present."""
    return result_item.get('check_id') or result_item.get('extra', {}).get('check_id')


def normalize_rel_path(project_root: Path, abs_or_rel: str) -> str:
    try:
        p = Path(abs_or_rel)
        if p.is_absolute():
            try:
                rel = p.resolve().relative_to(project_root.resolve())
            except Exception:
                return p.as_posix()
            return rel.as_posix()
        return p.as_posix()
    except Exception:
        return str(abs_or_rel).replace('\\', '/')


def count_expected_for_rule(
    ground_truth_index: Dict,
    project_root: Path,
    findings: List[Dict],
    rule_id: Optional[str],
    vuln_class: Optional[str]
) -> int:
    """Estimate the expected count using ground-truth for the files that actually appeared in findings context.

    If rule_id is provided, prefer rule-based counts; else fallback to vuln_class-based counts.
    If neither is provided or no labels exist, returns 0.
    """
    if not ground_truth_index:
        return 0

    expected = 0
    for item in findings:
        path = item.get('path') or item.get('extra', {}).get('path')
        if not path:
            continue
        rel = normalize_rel_path(project_root, path)
        slot = ground_truth_index.get(rel)
        if not slot:
            continue
        if rule_id and rule_id in slot['by_rule']:
            expected += int(slot['by_rule'][rule_id])
        elif vuln_class and vuln_class in slot['by_class']:
            expected += int(slot['by_class'][vuln_class])
    return expected


def compute_counts(
    project_root: Path,
    vulnerable_results: Dict,
    safe_results: Dict,
    rule_id: Optional[str],
    vuln_class: Optional[str],
    ground_truth_index: Optional[Dict] = None
) -> Tuple[int, int, int]:
    """Compute (tp, fp, fn) from semgrep results and optional ground-truth labels.

    - tp: findings in vulnerable corpus
    - fp: findings in safe corpus
    - fn: if labels available, expected - tp for the relevant files; else 0
    """
    tp = int(vulnerable_results.get('findings_count', 0) or 0)
    fp = int(safe_results.get('findings_count', 0) or 0)

    fn = 0
    if ground_truth_index:
        # Estimate expected counts for vulnerable corpus based on labels
        expected = count_expected_for_rule(
            ground_truth_index,
            project_root,
            vulnerable_results.get('findings', []) or [],
            rule_id,
            vuln_class
        )
        if expected > tp:
            fn = expected - tp

    return tp, fp, fn


def calculate_metrics(tp: int, fp: int, fn: int) -> Dict[str, float]:
    """Calculate precision, recall, F1, FPR, FNR."""
    precision = tp / (tp + fp) if (tp + fp) > 0 else 1.0
    recall = tp / (tp + fn) if (tp + fn) > 0 else 0.0
    f1_score = 2 * (precision * recall) / (precision + recall) if (precision + recall) > 0 else 0.0
    fpr = fp / (tp + fp) if (tp + fp) > 0 else 0.0
    fnr = fn / (tp + fn) if (tp + fn) > 0 else 0.0
    return {
        'precision': precision,
        'recall': recall,
        'f1_score': f1_score,
        'fpr': fpr,
        'fnr': fnr
    }


