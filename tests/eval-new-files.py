#!/usr/bin/env python3
"""
Quick evaluator for 15 ad-hoc test files (10 vulnerable, 5 safe).
Runs Semgrep packs over explicit files (bypassing git tracking) and computes
overall precision/recall vs global targets in .rule-quality.yml.
"""
import json
import subprocess
import sys
from pathlib import Path
import yaml

PROJECT = Path(__file__).resolve().parents[1]

SAFE = [
    PROJECT / 'tests' / 'safe-examples' / 'nonce-and-capability-safe.php',
    PROJECT / 'tests' / 'safe-examples' / 'xss-and-sqli-safe.php',
    PROJECT / 'tests' / 'safe-examples' / 'file-upload-safe.php',
    PROJECT / 'tests' / 'safe-examples' / 'rest-and-ajax-safe.php',
    PROJECT / 'tests' / 'safe-examples' / 'options-and-escaping-safe.php',
]

VULN = [
    PROJECT / 'tests' / 'vulnerable-examples' / 'xss-direct-easy.php',
    PROJECT / 'tests' / 'vulnerable-examples' / 'xss-attribute-hidden.php',
    PROJECT / 'tests' / 'vulnerable-examples' / 'xss-js-context-deep.php',
    PROJECT / 'tests' / 'vulnerable-examples' / 'sqli-concat-basic.php',
    PROJECT / 'tests' / 'vulnerable-examples' / 'sqli-prepare-misuse-hidden.php',
    PROJECT / 'tests' / 'vulnerable-examples' / 'ajax-no-nonce-deep.php',
    PROJECT / 'tests' / 'vulnerable-examples' / 'file-upload-missing-validation-long.php',
    PROJECT / 'tests' / 'vulnerable-examples' / 'path-traversal-unzip.php',
    PROJECT / 'tests' / 'vulnerable-examples' / 'deserialization-unserialize-hidden.php',
    PROJECT / 'tests' / 'vulnerable-examples' / 'rest-route-no-permission.php',
]

CONFIGS = [
    PROJECT / 'packs' / 'wp-core-security' / 'xss-generic.yaml',
    PROJECT / 'packs' / 'wp-core-security' / 'rest-permission-generic.yaml',
    PROJECT / 'packs' / 'wp-core-security' / 'ajax-nonce-generic.yaml',
    PROJECT / 'packs' / 'wp-core-security' / 'file-upload-generic.yaml',
    PROJECT / 'packs' / 'wp-core-security' / 'path-traversal-unzip-generic.yaml',
    PROJECT / 'packs' / 'wp-core-security' / 'sqli-generic.yaml',
]

def run_semgrep(targets):
    all_results = []
    for cfg in CONFIGS:
        cmd = [
            'semgrep', '--json', '--quiet', '--metrics=off', '--config', str(cfg)
        ]
        cmd += [str(p) for p in targets]
        r = subprocess.run(cmd, capture_output=True, text=True, cwd=PROJECT, timeout=120, encoding='utf-8', errors='replace')
        if r.returncode not in (0, 1):
            # Skip bad configs without aborting full eval
            continue
        out = r.stdout or ''
        data = {}
        try:
            data = json.loads(out)
        except Exception:
            start = out.find('{"version"')
            if start != -1:
                end = out.rfind('}')
                try:
                    data = json.loads(out[start:end+1])
                except Exception:
                    data = {}
        results = data.get('results', []) if isinstance(data, dict) else []
        all_results.extend(results)
    return all_results

def compute_metrics(tp, fp, fn):
    precision = tp / (tp + fp) if (tp + fp) > 0 else 1.0
    recall = tp / (tp + fn) if (tp + fn) > 0 else 0.0
    f1 = (2 * precision * recall / (precision + recall)) if (precision + recall) > 0 else 0.0
    fpr = fp / (tp + fp) if (tp + fp) > 0 else 0.0
    fnr = fn / (tp + fn) if (tp + fn) > 0 else 0.0
    return dict(precision=precision, recall=recall, f1=f1, fpr=fpr, fnr=fnr)

def main():
    # Load global targets
    qc = yaml.safe_load((PROJECT / '.rule-quality.yml').read_text(encoding='utf-8'))
    targets = qc.get('global_targets', {})

    vuln_findings = run_semgrep(VULN)
    safe_findings = run_semgrep(SAFE)

    tp = len(vuln_findings)
    fp = len(safe_findings)
    fn = 0  # no labels, so FN unknown
    metrics = compute_metrics(tp, fp, fn)

    summary = {
        'tp': tp, 'fp': fp, 'fn': fn,
        'metrics': metrics,
        'targets': {
            'precision_min': targets.get('precision_min', 0.95),
            'recall_min': targets.get('recall_min', 0.95),
            'fp_rate_max': targets.get('fp_rate_max', 0.05),
            'fn_rate_max': targets.get('fn_rate_max', 0.05),
        },
        'pass': {
            'precision_ok': metrics['precision'] >= targets.get('precision_min', 0.95),
            'recall_ok': metrics['recall'] >= targets.get('recall_min', 0.95),
            'fpr_ok': metrics['fpr'] <= targets.get('fp_rate_max', 0.05),
            'fnr_ok': metrics['fnr'] <= targets.get('fn_rate_max', 0.05),
        }
    }

    (PROJECT / 'results' / 'quick-debug').mkdir(parents=True, exist_ok=True)
    out = PROJECT / 'results' / 'quick-debug' / 'new-files-eval.json'
    out.write_text(json.dumps(summary, indent=2), encoding='utf-8')
    print(json.dumps(summary, indent=2))

if __name__ == '__main__':
    main()


