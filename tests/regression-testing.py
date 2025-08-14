#!/usr/bin/env python3
"""
Regression Testing for WordPress Semgrep Rules

Implements baseline snapshot building and comparison against current results
produced by the advanced testing framework and performance benchmarks.

Outputs JSON diffs and Markdown reports, and returns CI-friendly exit codes.
"""

from __future__ import annotations

import argparse
import json
import os
import re
import shutil
import statistics
import subprocess
import sys
import time
import hashlib
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import Dict, List, Optional, Tuple, Any
import urllib.request
import urllib.error

try:
    import yaml  # type: ignore
except Exception:
    yaml = None


DEFAULT_THRESHOLDS = {
    'precision_drop_max': 0.02,            # 2% absolute drop allowed
    'recall_drop_max': 0.02,               # 2% absolute drop allowed
    'new_fp_max': 1,                       # count across safe+corpus
    'lost_tp_max': 1,                      # count in vulnerable set
    'perf_time_increase_max_pct': 20.0,    # percent
    'perf_rss_increase_max_mb': 50.0       # absolute MB
}


def _read_text(path: Path) -> str:
    with open(path, 'r', encoding='utf-8', errors='replace') as f:
        return f.read()


def _safe_yaml_load(path: Path) -> Dict[str, Any]:
    if yaml is None:
        return {}
    try:
        with open(path, 'r', encoding='utf-8', errors='replace') as f:
            return yaml.safe_load(f) or {}
    except Exception:
        return {}


def _sha256_text(text: str) -> str:
    return hashlib.sha256(text.encode('utf-8', errors='replace')).hexdigest()


def _sha256_file(path: Path) -> str:
    try:
        content = _read_text(path)
        return _sha256_text(content)
    except Exception:
        return ''


def _now_ts_int() -> int:
    return int(time.time())


def _relpath(project_root: Path, abs_path: str) -> str:
    try:
        return str(Path(abs_path).resolve().relative_to(project_root.resolve()))
    except Exception:
        return abs_path


def _finding_key(project_root: Path, finding: Dict[str, Any]) -> str:
    path = finding.get('path') or finding.get('extra', {}).get('path', '') or ''
    start = finding.get('start') or {}
    if isinstance(start, dict):
        line = start.get('line', 0)
    else:
        # Some schemas might encode as int directly
        line = int(start) if str(start).isdigit() else 0
    message = (
        finding.get('extra', {}).get('message')
        or finding.get('extra', {}).get('metavars', {})
        or finding.get('check_id')
        or ''
    )
    if isinstance(message, dict):
        message = json.dumps(message, sort_keys=True)
    message = str(message)
    message_short = message[:160]
    return f"{_relpath(project_root, path)}@{line}:{message_short}"


def _load_quality_config(project_root: Path) -> Dict[str, Any]:
    cfg_path = project_root / '.rule-quality.yml'
    if not cfg_path.exists():
        return {}
    return _safe_yaml_load(cfg_path)


def _load_regression_thresholds(config: Dict[str, Any]) -> Dict[str, Any]:
    user = (config or {}).get('regression_thresholds', {})
    thresholds = DEFAULT_THRESHOLDS.copy()
    thresholds.update({k: v for k, v in user.items() if v is not None})
    return thresholds


def _latest_file_in(dir_path: Path, prefix: str, suffix: str) -> Optional[Path]:
    if not dir_path.exists():
        return None
    candidates = [p for p in dir_path.glob(f"{prefix}*{suffix}") if p.is_file()]
    if not candidates:
        return None
    return max(candidates, key=lambda p: p.stat().st_mtime)


def _find_latest_advanced_results(project_root: Path) -> Optional[Path]:
    results_dir = project_root / 'results' / 'advanced-testing'
    # advanced-testing-results-<ts>.json
    return _latest_file_in(results_dir, 'advanced-testing-results-', '.json')


def _ensure_dirs(path: Path) -> None:
    path.mkdir(parents=True, exist_ok=True)


@dataclass
class RuleSnapshot:
    rule_id: str
    rule_file: str
    rule_hash: str
    vuln_class: str
    metadata: Dict[str, Any]
    metrics: Dict[str, float]
    counts: Dict[str, int]
    performance: Dict[str, float]
    findings: Dict[str, List[str]]


class RegressionTester:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root).resolve()
        self.regression_dir = self.project_root / 'results' / 'regression'
        self.baselines_dir = self.regression_dir / 'baselines'
        self.diffs_dir = self.regression_dir / 'diffs'
        self.reports_dir = self.regression_dir / 'reports'
        for d in (self.baselines_dir, self.diffs_dir, self.reports_dir):
            _ensure_dirs(d)

        self.quality_config = _load_quality_config(self.project_root)
        self.thresholds = _load_regression_thresholds(self.quality_config)

    # -------------------- Data collection --------------------
    def load_advanced_results(self, path: Optional[Path]) -> Dict[str, Any]:
        if path is None:
            path = _find_latest_advanced_results(self.project_root)
        if path is None or not path.exists():
            raise FileNotFoundError(
                'Advanced testing results not found. Run tests/advanced-testing-framework.py first.'
            )
        with open(path, 'r', encoding='utf-8', errors='replace') as f:
            return json.load(f)

    def _extract_findings_keys(self, project_root: Path, results: Dict[str, Any]) -> List[str]:
        items = results.get('findings', []) if isinstance(results, dict) else []
        keys: List[str] = []
        for it in items:
            try:
                keys.append(_finding_key(project_root, it))
            except Exception:
                # Skip malformed finding
                continue
        # Sort for determinism
        return sorted(set(keys))

    def build_snapshot(self, advanced_results_json: Dict[str, Any], rule_filter: Optional[List[str]] = None) -> Dict[str, Any]:
        results = advanced_results_json.get('results', [])
        snapshots: List[RuleSnapshot] = []

        for entry in results:
            try:
                rule_file = entry.get('rule_file', '')
                rule_file_path = Path(rule_file)
                if not rule_file_path.is_absolute():
                    rule_file_path = (self.project_root / rule_file).resolve()

                if rule_filter is not None and rule_file_path.name not in rule_filter and str(rule_file_path) not in rule_filter:
                    continue

                rule_id = entry.get('rule_id', '')
                vuln_class = entry.get('vuln_class', 'other')
                metadata = {
                    'severity': entry.get('confidence', ''),
                    'confidence': entry.get('confidence', 'medium')
                }

                # Metrics
                metrics = entry.get('metrics', {}) or {}
                counts = {
                    'tp': int(metrics.get('true_positives', 0)),
                    'fp': int(metrics.get('false_positives', 0)),
                    'fn': int(metrics.get('false_negatives', 0)),
                }

                # Performance: prefer corpus_test where available
                corpus_test = entry.get('corpus_test', {}) or {}
                performance = {
                    'scan_time': float(corpus_test.get('scan_time', 0.0)),
                    'rss_bytes': float(corpus_test.get('memory_usage', 0.0)),
                    'cpu_time': float(corpus_test.get('cpu_time', 0.0)),
                }

                # Findings keys
                vulnerable_test = entry.get('vulnerable_test', {}) or {}
                safe_test = entry.get('safe_test', {}) or {}
                findings = {
                    'safe': self._extract_findings_keys(self.project_root, safe_test),
                    'vulnerable': self._extract_findings_keys(self.project_root, vulnerable_test),
                    'corpus': self._extract_findings_keys(self.project_root, corpus_test),
                }

                snapshots.append(
                    RuleSnapshot(
                        rule_id=rule_id,
                        rule_file=str(rule_file_path),
                        rule_hash=_sha256_file(rule_file_path) if rule_file_path.exists() else '',
                        vuln_class=vuln_class,
                        metadata=metadata,
                        metrics={
                            'precision': float(metrics.get('precision', 0.0)),
                            'recall': float(metrics.get('recall', 0.0)),
                            'fpr': float(metrics.get('fpr', 0.0)),
                            'fnr': float(metrics.get('fnr', 0.0)),
                            'f1': float(metrics.get('f1_score', 0.0)),
                        },
                        counts=counts,
                        performance=performance,
                        findings=findings,
                    )
                )
            except Exception:
                # Skip malformed entries
                continue

        snapshot = {
            'generated_at': datetime.now().isoformat(),
            'project_root': str(self.project_root),
            'rules': [s.__dict__ for s in snapshots]
        }
        return snapshot

    # -------------------- Baseline I/O --------------------
    def save_baseline(self, snapshot: Dict[str, Any]) -> Path:
        ts = _now_ts_int()
        out_path = self.baselines_dir / f"baseline-{ts}.json"
        with open(out_path, 'w', encoding='utf-8') as f:
            json.dump(snapshot, f, indent=2)

        # Update canonical pointer (copy the file for simplicity)
        pointer = self.regression_dir / 'baseline-current.json'
        shutil.copyfile(out_path, pointer)
        return out_path

    def load_baseline(self, path: Optional[Path]) -> Dict[str, Any]:
        if path is None:
            path = self.regression_dir / 'baseline-current.json'
        if not path.exists():
            raise FileNotFoundError('Baseline file not found. Run with --save-baseline first.')
        with open(path, 'r', encoding='utf-8', errors='replace') as f:
            return json.load(f)

    # -------------------- Comparison --------------------
    def _rule_map(self, snapshot: Dict[str, Any]) -> Dict[str, Dict[str, Any]]:
        m: Dict[str, Dict[str, Any]] = {}
        for r in snapshot.get('rules', []):
            rid = r.get('rule_id', '')
            if rid:
                m[rid] = r
        return m

    def _perf_increase_pct(self, base: float, cur: float) -> float:
        if base <= 0:
            return 0.0
        return max(0.0, ((cur - base) / base) * 100.0)

    def compare_snapshots(self, baseline: Dict[str, Any], current: Dict[str, Any]) -> Dict[str, Any]:
        bmap = self._rule_map(baseline)
        cmap = self._rule_map(current)
        all_ids = sorted(set(bmap.keys()) | set(cmap.keys()))

        report_rules: Dict[str, Any] = {}
        counts = {'pass': 0, 'warn': 0, 'fail': 0, 'added': 0, 'removed': 0}

        t = self.thresholds

        for rid in all_ids:
            b = bmap.get(rid)
            c = cmap.get(rid)
            if b is None:
                counts['added'] += 1
                report_rules[rid] = {'status': 'added', 'details': {'rule_file': c.get('rule_file') if c else ''}}
                continue
            if c is None:
                counts['removed'] += 1
                report_rules[rid] = {'status': 'removed', 'details': {'rule_file': b.get('rule_file') if b else ''}}
                continue

            # Metric deltas
            md = {}
            for k in ['precision', 'recall', 'fpr', 'fnr', 'f1']:
                md[k] = float(c['metrics'].get(k, 0.0)) - float(b['metrics'].get(k, 0.0))

            # Performance deltas
            perf = {
                'scan_time_delta': float(c['performance'].get('scan_time', 0.0)) - float(b['performance'].get('scan_time', 0.0)),
                'rss_bytes_delta': float(c['performance'].get('rss_bytes', 0.0)) - float(b['performance'].get('rss_bytes', 0.0)),
                'cpu_time_delta': float(c['performance'].get('cpu_time', 0.0)) - float(b['performance'].get('cpu_time', 0.0)),
                'scan_time_increase_pct': self._perf_increase_pct(float(b['performance'].get('scan_time', 0.0)), float(c['performance'].get('scan_time', 0.0)))
            }

            # Findings diffs
            def setdiff(a: List[str], b: List[str]) -> List[str]:
                return sorted(list(set(a) - set(b)))

            f_b = b.get('findings', {})
            f_c = c.get('findings', {})
            findings_diff = {
                'safe': {
                    'added': setdiff(f_c.get('safe', []), f_b.get('safe', [])),
                    'removed': setdiff(f_b.get('safe', []), f_c.get('safe', [])),
                },
                'vulnerable': {
                    'added': setdiff(f_c.get('vulnerable', []), f_b.get('vulnerable', [])),
                    'removed': setdiff(f_b.get('vulnerable', []), f_c.get('vulnerable', [])),
                },
                'corpus': {
                    'added': setdiff(f_c.get('corpus', []), f_b.get('corpus', [])),
                    'removed': setdiff(f_b.get('corpus', []), f_c.get('corpus', [])),
                }
            }

            new_fp = len(findings_diff['safe']['added']) + len(findings_diff['corpus']['added'])
            lost_tp = len(findings_diff['vulnerable']['removed'])

            rss_mb_increase = perf['rss_bytes_delta'] / (1024 * 1024)

            # Severity evaluation
            fail_reasons = []
            warn_reasons = []

            if md['precision'] < -t['precision_drop_max']:
                fail_reasons.append({'precision_drop': md['precision']})
            elif md['precision'] < 0:
                warn_reasons.append({'precision_drop': md['precision']})

            if md['recall'] < -t['recall_drop_max']:
                fail_reasons.append({'recall_drop': md['recall']})
            elif md['recall'] < 0:
                warn_reasons.append({'recall_drop': md['recall']})

            if new_fp > t['new_fp_max']:
                fail_reasons.append({'new_fp': new_fp})
            elif new_fp > 0:
                warn_reasons.append({'new_fp': new_fp})

            if lost_tp > t['lost_tp_max']:
                fail_reasons.append({'lost_tp': lost_tp})
            elif lost_tp > 0:
                warn_reasons.append({'lost_tp': lost_tp})

            if perf['scan_time_increase_pct'] > t['perf_time_increase_max_pct']:
                fail_reasons.append({'scan_time_increase_pct': perf['scan_time_increase_pct']})
            elif perf['scan_time_increase_pct'] > 0:
                warn_reasons.append({'scan_time_increase_pct': perf['scan_time_increase_pct']})

            if rss_mb_increase > t['perf_rss_increase_max_mb']:
                fail_reasons.append({'rss_mb_increase': rss_mb_increase})
            elif rss_mb_increase > 0:
                warn_reasons.append({'rss_mb_increase': rss_mb_increase})

            # Rule change indicator
            if b.get('rule_hash') and c.get('rule_hash') and b['rule_hash'] != c['rule_hash']:
                warn_reasons.append({'rule_changed': True})

            status = 'pass'
            if fail_reasons:
                status = 'fail'
                counts['fail'] += 1
            elif warn_reasons:
                status = 'warn'
                counts['warn'] += 1
            else:
                counts['pass'] += 1

            report_rules[rid] = {
                'status': status,
                'rule_file': c.get('rule_file', ''),
                'metrics_delta': md,
                'performance_delta': perf,
                'findings_diff': findings_diff,
                'reasons_fail': fail_reasons,
                'reasons_warn': warn_reasons,
            }

        summary = {
            'counts': counts,
            'thresholds': self.thresholds,
            'generated_at': datetime.now().isoformat(),
        }

        return {'rules': report_rules, 'summary': summary}

    # -------------------- Reporting --------------------
    def save_diff(self, diff: Dict[str, Any]) -> Path:
        ts = datetime.now().strftime('%Y%m%d_%H%M%S')
        out = self.diffs_dir / f'diff-{ts}.json'
        with open(out, 'w', encoding='utf-8') as f:
            json.dump(diff, f, indent=2)
        return out

    def save_markdown(self, diff: Dict[str, Any]) -> Path:
        ts = datetime.now().strftime('%Y%m%d_%H%M%S')
        out = self.reports_dir / f'regression-report-{ts}.md'

        markdown = self.generate_markdown(diff)
        with open(out, 'w', encoding='utf-8') as f:
            f.write(markdown)
        return out

    def generate_markdown(self, diff: Dict[str, Any]) -> str:
        counts = diff['summary']['counts']
        lines: List[str] = []
        lines.append('## Regression Test Report')
        lines.append('')
        lines.append(f"Generated: {diff['summary']['generated_at']}")
        lines.append('')
        lines.append(f"- Pass: {counts['pass']}")
        lines.append(f"- Warn: {counts['warn']}")
        lines.append(f"- Fail: {counts['fail']}")
        lines.append(f"- Added rules: {counts['added']}")
        lines.append(f"- Removed rules: {counts['removed']}")
        lines.append('')

        # Show top offenders by precision drop and recall drop
        offenders: List[Tuple[str, float, float]] = []
        for rid, r in diff['rules'].items():
            if r['status'] in ('warn', 'fail'):
                md = r.get('metrics_delta', {})
                offenders.append((rid, float(md.get('precision', 0.0)), float(md.get('recall', 0.0))))
        offenders.sort(key=lambda x: (x[1], x[2]))  # most negative first

        if offenders:
            lines.append('### Top metric drops')
            for rid, pdrop, rdrop in offenders[:20]:
                lines.append(f"- {rid}: precision {pdrop:+.3f}, recall {rdrop:+.3f}")
            lines.append('')

        return '\n'.join(lines)

    def alert_step_summary(self, markdown: str) -> bool:
        try:
            step_summary_path = os.environ.get('GITHUB_STEP_SUMMARY')
            if not step_summary_path:
                return False
            with open(step_summary_path, 'a', encoding='utf-8') as f:
                f.write(markdown)
                f.write('\n')
            return True
        except Exception:
            return False

    def alert_slack(self, markdown: str, webhook_url: str) -> bool:
        try:
            data = json.dumps({'text': markdown}).encode('utf-8')
            req = urllib.request.Request(webhook_url, data=data, headers={'Content-Type': 'application/json'})
            with urllib.request.urlopen(req, timeout=10) as resp:
                return 200 <= resp.getcode() < 300
        except Exception:
            return False

    # -------------------- Change detection (optional) --------------------
    def detect_changed_rules(self) -> List[str]:
        try:
            # Ensure we are in a git repository
            probe = subprocess.run('git rev-parse --git-dir', shell=True, cwd=self.project_root,
                                   capture_output=True, text=True)
            if probe.returncode != 0:
                return []

            # Determine base branch from environment or sensible defaults
            base_ref = (
                os.environ.get('GITHUB_BASE_REF') or
                os.environ.get('CI_MERGE_REQUEST_TARGET_BRANCH_NAME') or
                os.environ.get('CI_DEFAULT_BRANCH') or
                'origin/main'
            )

            candidate_cmds = [
                f'git diff --name-only {base_ref}...HEAD',
                'git diff --name-only main...HEAD',
                'git diff --name-only master...HEAD',
                'git diff --name-only HEAD~1'
            ]

            changed: List[str] = []
            for cmd in candidate_cmds:
                result = subprocess.run(cmd, shell=True, cwd=self.project_root, capture_output=True, text=True)
                if result.returncode == 0 and result.stdout.strip():
                    changed = [s.strip() for s in result.stdout.splitlines() if s.strip()]
                    break

            # Filter to rule files only (under packs/ and .yaml extension)
            filtered = []
            for p in changed:
                # Normalize path separators
                p_norm = p.replace('\\', '/')
                if p_norm.startswith('packs/') and p_norm.endswith('.yaml'):
                    filtered.append(p)

            return filtered
        except Exception:
            return []


def main() -> int:
    parser = argparse.ArgumentParser(description='Regression testing: baselines and comparison')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--save-baseline', action='store_true', help='Build and save a new baseline snapshot')
    parser.add_argument('--compare', action='store_true', help='Compare current snapshot to baseline')
    parser.add_argument('--baseline-file', help='Path to baseline file to compare against')
    parser.add_argument('--update-baseline-on-pass', action='store_true', help='Update baseline if no failures (and no warnings when --strict)')
    parser.add_argument('--changed-only', action='store_true', help='Limit to rules changed compared to main')
    parser.add_argument('--rules', nargs='+', help='Specific rule files or names to include')
    parser.add_argument('--advanced-results', help='Path to advanced testing results JSON to use for snapshot')
    parser.add_argument('--strict', action='store_true', help='Treat warnings as failures')
    parser.add_argument('--alert-step-summary', action='store_true', help='Write Markdown report to GitHub Step Summary when available')
    parser.add_argument('--alert-slack', action='store_true', help='Send a Slack notification when regressions occur')
    parser.add_argument('--slack-webhook-url', help='Slack Incoming Webhook URL (or set SLACK_WEBHOOK_URL)')

    args = parser.parse_args()

    tester = RegressionTester(args.project_root)

    # Determine rule filter
    rule_filter: Optional[List[str]] = None
    if args.changed_only:
        changed = tester.detect_changed_rules()
        if changed:
            rule_filter = [Path(c).name for c in changed] + changed
    if args.rules:
        if rule_filter is None:
            rule_filter = []
        rule_filter.extend(args.rules)

    # Load advanced results
    adv_path = Path(args.advanced_results) if args.advanced_results else None
    advanced_json = tester.load_advanced_results(adv_path)

    exit_code = 0

    # Save baseline if requested
    if args.save_baseline:
        snapshot = tester.build_snapshot(advanced_json, rule_filter=rule_filter)
        out = tester.save_baseline(snapshot)
        print(f"Saved baseline: {out}")

    # Compare if requested (default behavior when not saving explicitly)
    if args.compare or not args.save_baseline:
        try:
            baseline_file = Path(args.baseline_file) if args.baseline_file else None
            baseline = tester.load_baseline(baseline_file)
        except FileNotFoundError as e:
            print(f"Error: {e}")
            return 2

        current_snapshot = tester.build_snapshot(advanced_json, rule_filter=rule_filter)
        diff = tester.compare_snapshots(baseline, current_snapshot)
        diff_path = tester.save_diff(diff)
        md_path = tester.save_markdown(diff)
        markdown = tester.generate_markdown(diff)
        print(f"Diff saved: {diff_path}")
        print(f"Report saved: {md_path}")

        counts = diff['summary']['counts']
        fails = counts.get('fail', 0)
        warns = counts.get('warn', 0)

        if fails > 0 or (args.strict and warns > 0):
            exit_code = 1
        else:
            exit_code = 0

        # Automated alerting
        if args.alert_step_summary or os.environ.get('GITHUB_STEP_SUMMARY'):
            if tester.alert_step_summary(markdown):
                print('Wrote GitHub Step Summary')
        if args.alert_slack or os.environ.get('SLACK_WEBHOOK_URL'):
            webhook = args.slack_webhook_url or os.environ.get('SLACK_WEBHOOK_URL', '')
            # Only alert on problems to reduce noise
            if webhook and (fails > 0 or warns > 0):
                ok = tester.alert_slack(markdown, webhook)
                print('Slack notification sent' if ok else 'Slack notification failed')

        if args.update_baseline_on_pass and exit_code == 0:
            out = tester.save_baseline(current_snapshot)
            print(f"Updated baseline: {out}")

    return exit_code


if __name__ == '__main__':
    sys.exit(main())


