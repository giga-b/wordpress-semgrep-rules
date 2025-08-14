## Regression Testing Architecture

### Goal
Automate detection of behavioral regressions in Semgrep rules across precision/recall, findings drift, and performance, using corpus-based testing (no embedded tests) and existing advanced/performance frameworks.

### Guiding Principles
- Baseline-driven comparisons with explicit thresholds from `.rule-quality.yml`
- Deterministic, reproducible snapshots stored in `results/regression/`
- Fast paths for changed rules; full mode for nightly/CI
- Windows-friendly (UTF-8, no interactive prompts)

### Components
- Baseline registry
  - JSON snapshot of per-rule metrics and corpus findings
  - Stored under `results/regression/baselines/`
  - One canonical pointer: `results/regression/baseline-current.json`

- Snapshot builder
  - Ingests latest outputs from:
    - `tests/advanced-testing-framework.py` (quality and FP candidates)
    - `tests/benchmark-testing.py` (aggregated metrics)
    - Optional direct Semgrep runs for rules changed in the PR
  - Produces a normalized snapshot schema (see Schema)

- Comparator
  - Compares current snapshot vs. baseline
  - Dimensions:
    - Metrics regression: precision, recall, fpr, fnr, f1
    - Findings drift: new/removed results on `tests/safe-examples/`, `tests/vulnerable-examples/`, and `corpus/`
    - Performance regression: wall time, RSS, CPU (reuses Task 2.5 outputs)
    - Rule integrity: rule content hash change (YAML hash) and metadata changes
  - Severity classification: info/warn/fail based on thresholds

- Change detector
  - `--changed-only` mode computes changed rules via `git diff --name-only` over `packs/**.yaml`
  - Falls back to full suite when not in a git repo or flag disabled

- Reporter
  - Outputs:
    - JSON diff: `results/regression/diffs/diff-<ts>.json`
    - Markdown summary: `results/regression/reports/regression-report-<ts>.md`
    - Optional GitHub Step Summary when running in CI
  - Exit codes suitable for CI gating

### Directory Layout
```
results/regression/
  baselines/
    baseline-<timestamp>.json
  baseline-current.json        # canonical pointer (file copy)
  diffs/
    diff-<timestamp>.json
  reports/
    regression-report-<timestamp>.md
```

### CLI Design (future script: `tests/regression-testing.py`)
- `--project-root .`
- `--save-baseline` (build snapshot and write to baselines/ and update baseline-current.json)
- `--compare` (default) compare current snapshot vs baseline-current
- `--baseline-file <path>` (override baseline)
- `--update-baseline-on-pass` (write new baseline if no failures)
- `--changed-only` (limit to rules modified vs main/default branch)
- `--rules <glob...>` (filter rules)
- `--strict` (treat warnings as failures)

### Thresholds and Policy
- Source of truth: `.rule-quality.yml`
  - `global_targets`: `precision_min`, `recall_min`, `fp_rate_max`, `fn_rate_max`
  - Optional `regression_thresholds` (add):
    - `precision_drop_max` (e.g., 0.02)
    - `recall_drop_max` (e.g., 0.02)
    - `new_fp_max` (count)
    - `lost_tp_max` (count)
    - `perf_time_increase_max_pct` (e.g., 20)
    - `perf_rss_increase_max_mb` (e.g., 50)

If absent, sensible defaults will be used and written into the report.

### Snapshot Schema (per rule)
```json
{
  "rule_id": "string",
  "rule_file": "packs/...yaml",
  "rule_hash": "sha256(yaml)",
  "vuln_class": "xss|sqli|csrf|...",
  "metadata": { "severity": "", "confidence": "" },
  "metrics": { "precision": 0, "recall": 0, "fpr": 0, "fnr": 0, "f1": 0 },
  "counts": { "tp": 0, "fp": 0, "fn": 0 },
  "performance": { "scan_time": 0, "rss_bytes": 0, "cpu_time": 0 },
  "findings": {
    "safe": ["file@line:message", "..."],
    "vulnerable": ["file@line:message", "..."],
    "corpus": ["file@line:message", "..."]
  }
}
```

Note: findings are stored in a compact, stable key (file path relative to project root + line + rule message) to enable deterministic diffing.

### Comparison Logic (high level)
1. Align rules by `rule_id`. If missing in either side, classify as added/removed.
2. For common rules:
   - Compute deltas for metrics and performance; evaluate against thresholds.
   - Compute set diffs per findings domain (safe/vulnerable/corpus): new vs removed.
   - Derive counts: `new_fp` (safe+corpus), `lost_tp` (vulnerable), `lost_findings_in_corpus` (potential recall issue).
   - If `rule_hash` changed, mark as rule-changed; elevate severity if metrics degraded.

### CI Integration
- Pre-merge (PR): `--changed-only --compare` (fast), fail on severity=fail
- Nightly: full `--compare` over all rules; `--update-baseline-on-pass` optional
- Release cut: force full suite, generate report artifact

### Data Sources
- Quality/metrics: `tests/advanced-testing-framework.py` outputs in `results/advanced-testing/`
- Performance: Task 2.5 outputs in `results/performance/`
- Findings: either parsed from advanced-testing results or direct fresh scans for changed rules

### Implementation Plan (next tasks)
1. Implement `tests/regression-testing.py` with the CLI above
2. Add `regression_thresholds` to `.rule-quality.yml` (optional, with defaults)
3. Wire into CI workflow after benchmarks
4. Generate first baseline from current green state

### Failure Policy
- Exit code 1 on any fail-severity regression
- Exit code 0 with warnings allowed unless `--strict`

### Security/Robustness
- UTF-8 I/O with `errors='replace'`
- Timeouts per downstream scans; reuse existing perf wrappers
- Respect `.gitignore`-like excludes already in advanced framework

This architecture uses existing shared libs in `tests/_lib/` and aligns with the repo’s corpus-based strategy to ensure stable, high-signal regression detection.


