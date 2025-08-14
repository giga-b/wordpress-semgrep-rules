### WordPress Semgrep Rules — User Guide

This guide explains how to install Semgrep, use the WordPress rule packs to scan your code, and run the project’s comprehensive tests. It is written for Windows, macOS, and Linux users.

### 1) Install prerequisites

- Semgrep (via Python):
  - Windows/macOS/Linux: `python -m pip install --user --upgrade semgrep`
  - Verify: `semgrep --version`

If `semgrep` is not found after install on Windows, add the Python user Scripts directory to PATH for the session:

```powershell
$pyUserBase = python -c "import site; print(site.USER_BASE)"
$env:Path = (Join-Path $pyUserBase 'Scripts') + ';' + $env:Path
```

### 2) Scan your WordPress codebase

Run Semgrep against your project’s PHP code using a rule pack.

```bash
# Example: scan with core security rules
semgrep scan \
  --config packs/wp-core-security \
  --lang php \
  --json \
  --no-git-ignore \
  --exclude '**/vendor/**' --exclude '**/node_modules/**' \
  /path/to/your/wordpress/plugin-or-theme
```

Other packs:

- `packs/wp-core-security` — security rules for nonce verification, capability checks, XSS/SQLi prevention, REST/AJAX security, file uploads, etc.
- `packs/wp-core-quality` — quality and maintainability rules (when present).
- `packs/experimental` — advanced and experimental rules (may be noisy; use for exploratory scans).

Tip: To scan a single file quickly:

```bash
semgrep scan --config packs/wp-core-security --lang php ./path/to/file.php
```

### 3) Run the comprehensive test suite (optional)

Use the master runner to validate rule quality, scan the corpus, and generate reports.

Windows (PowerShell):

```powershell
# From repository root
chcp 65001 > $null
[Console]::OutputEncoding = [System.Text.UTF8Encoding]::new()
$env:PYTHONUTF8='1'; $env:PYTHONIOENCODING='utf-8'
python tests/run-all-tests.py --project-root .
```

macOS/Linux (Bash):

```bash
python tests/run-all-tests.py --project-root .
```

Run specific components only:

```bash
# Metadata validation
python tests/validate-rule-metadata.py --project-root .

# Corpus scans
python tests/run-corpus-scans.py --project-root .

# Advanced testing (parallel)
python tests/advanced-testing-framework.py --project-root . --workers 4
```

Parallel execution (optional):

```bash
python tests/run-all-tests.py --project-root . --parallel --workers 4
```

### 4) Where to find results

Artifacts are saved under `results/`:

- `results/master-test-run-*.json` — master summary
- `results/rule-metadata-validation.json` — metadata/structure report
- `results/corpus-validation/*.json` — corpus integrity reports
- `results/corpus-scans/*.json` — corpus scan results
- `results/advanced-testing/*.json` — advanced testing results and quality metrics
- `results/final-validation/*.json` — final validation report
- `results/reports/*.md|*.json` — generated human-readable reports

### 5) Troubleshooting

- Windows console Unicode errors: set UTF-8 for the session before running tests.

```powershell
chcp 65001 > $null
[Console]::OutputEncoding = [System.Text.UTF8Encoding]::new()
$env:PYTHONUTF8='1'; $env:PYTHONIOENCODING='utf-8'
```

- Semgrep not found after install: ensure the Python user Scripts path is in PATH (see Section 1).

- Performance/timeouts on large corpora: prefer the master runner (it auto-tunes scopes and timeouts), or add includes/excludes:

```bash
semgrep scan --config packs/wp-core-security --lang php \
  --include '**/*.php' --exclude '**/vendor/**' --exclude '**/node_modules/**' \
  --timeout 300 /path/to/project
```

### 6) CI usage (example)

Minimal CI step to validate rule metadata and run a scan:

```bash
python -m pip install --user --upgrade semgrep pyyaml
semgrep scan --config packs/wp-core-security --lang php /path/to/src
python tests/validate-rule-metadata.py --project-root .
```

For full pipelines, use `tests/run-all-tests.py` and upload `results/` as artifacts.

### 7) Support

- See `docs/automated-testing-guide.md` and `docs/ADVANCED_TESTING_IMPLEMENTATION.md` for deeper details.
- File issues or questions alongside sample snippets to improve detection quality.

