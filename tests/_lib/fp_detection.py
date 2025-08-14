#!/usr/bin/env python3
"""
False positive detection helpers for Semgrep corpus-based testing.

Sources of FP candidates:
- Findings on tests/safe-examples/** (direct FP proxy)
- Findings on corpus where no ground-truth exists for the (rule_id or vuln_class) in that file

Optional allowlist support to suppress known benign matches:
  corpus/labels/allowlist.json
  {
    "path_globs": ["**/vendor/**", "**/*.min.js"],
    "files": ["plugins/example/file.php"],
    "rules": { "rule.id": ["**/some/path/**"] }
  }
"""

from __future__ import annotations

import json
from pathlib import Path
from typing import Dict, List, Optional
import fnmatch

# Support running both as a package (tests._lib) and as a plain module with tests/_lib on sys.path
try:
    from .metrics import normalize_rel_path  # type: ignore
except Exception:  # pragma: no cover
    from metrics import normalize_rel_path  # type: ignore


def load_allowlist(project_root: Path) -> Dict:
    labels_dir = project_root / 'corpus' / 'labels'
    allow_file = labels_dir / 'allowlist.json'
    if not allow_file.exists():
        return {"path_globs": [], "files": [], "rules": {}}
    try:
        with open(allow_file, 'r', encoding='utf-8') as f:
            data = json.load(f)
            return {
                "path_globs": list(data.get('path_globs', [])),
                "files": list(data.get('files', [])),
                "rules": dict(data.get('rules', {}))
            }
    except Exception:
        return {"path_globs": [], "files": [], "rules": {}}


def is_allowed(rule_id: Optional[str], rel_path: str, allowlist: Dict) -> bool:
    if not allowlist:
        return False
    # exact file allow
    if rel_path in allowlist.get('files', []):
        return True
    # glob allow by path
    for pattern in allowlist.get('path_globs', []) or []:
        if fnmatch.fnmatch(rel_path, pattern):
            return True
    # per-rule allow globs
    if rule_id and rule_id in (allowlist.get('rules', {}) or {}):
        for pattern in allowlist['rules'][rule_id]:
            if fnmatch.fnmatch(rel_path, pattern):
                return True
    return False


def extract_position(result_item: Dict) -> Dict:
    extra = result_item.get('extra', {}) or {}
    spans = extra.get('lines', {}) or {}
    start = spans.get('begin') or spans.get('start') or {}
    end = spans.get('end') or {}
    return {
        'start_line': start.get('line') if isinstance(start, dict) else None,
        'end_line': end.get('line') if isinstance(end, dict) else None
    }


def collect_fp_candidates(
    project_root: Path,
    safe_results: Dict,
    corpus_results: Dict,
    rule_id: Optional[str],
    vuln_class: Optional[str],
    ground_truth_index: Optional[Dict],
    allowlist: Optional[Dict]
) -> Dict:
    """Return a dict with false positive candidates and counts.

    Format:
      {
        'safe_fp': [ { path, start_line, message }... ],
        'corpus_fp': [ { path, start_line, message }... ],
        'safe_fp_count': N,
        'corpus_fp_count': M,
        'total_fp_count': N+M
      }
    """
    candidates_safe: List[Dict] = []
    candidates_corpus: List[Dict] = []

    # 1) Anything found in safe examples is an FP candidate (unless allowlisted)
    for item in (safe_results.get('findings') or []):
        rel = normalize_rel_path(project_root, item.get('path') or item.get('extra', {}).get('path', ''))
        if not rel:
            continue
        if is_allowed(rule_id, rel, allowlist or {}):
            continue
        pos = extract_position(item)
        candidates_safe.append({
            'path': rel,
            'start_line': pos['start_line'],
            'message': (item.get('extra', {}) or {}).get('message') or ''
        })

    # 2) In corpus, mark as FP candidate when there is no ground-truth match for (rule or class) in that file
    for item in (corpus_results.get('findings') or []):
        rel = normalize_rel_path(project_root, item.get('path') or item.get('extra', {}).get('path', ''))
        if not rel:
            continue
        if is_allowed(rule_id, rel, allowlist or {}):
            continue
        gt = (ground_truth_index or {}).get(rel)
        has_rule = bool(gt and rule_id and rule_id in (gt.get('by_rule') or {}))
        has_class = bool(gt and vuln_class and vuln_class in (gt.get('by_class') or {}))
        if not has_rule and not has_class:
            pos = extract_position(item)
            candidates_corpus.append({
                'path': rel,
                'start_line': pos['start_line'],
                'message': (item.get('extra', {}) or {}).get('message') or ''
            })

    return {
        'safe_fp': candidates_safe,
        'corpus_fp': candidates_corpus,
        'safe_fp_count': len(candidates_safe),
        'corpus_fp_count': len(candidates_corpus),
        'total_fp_count': len(candidates_safe) + len(candidates_corpus)
    }


