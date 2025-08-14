### WordPress Semgrep Rules — Deployment Guide

This guide explains how to package, verify, and publish the WordPress Semgrep rule packs for consumers. It covers pre-release checks, versioning, release artifacts, and consumption methods.

### 1) Prerequisites

- Python 3.8+
- Semgrep installed and on PATH (`python -m pip install --user --upgrade semgrep`)
- Write access to the repository (for tagging/releases)

### 2) Pre-release checklist

Run the comprehensive validation to ensure rule quality and stable outputs.

Windows (PowerShell):

```powershell
chcp 65001 > $null
[Console]::OutputEncoding = [System.Text.UTF8Encoding]::new()
$env:PYTHONUTF8='1'; $env:PYTHONIOENCODING='utf-8'
python tests/validate-rule-metadata.py --project-root .
python tests/run-all-tests.py --project-root .
```

macOS/Linux (Bash):

```bash
python tests/validate-rule-metadata.py --project-root .
python tests/run-all-tests.py --project-root .
```

Verify artifacts in `results/`:

- `results/rule-metadata-validation.json` shows 100% valid
- `results/master-test-run-*.json` generated and saved
- Optional reports are present in `results/reports/`

### 3) Versioning and CHANGELOG

- Use semantic versioning (MAJOR.MINOR.PATCH)
- Update `CHANGELOG.md` with notable changes
- Commit the version bump and changelog edits on `main` (or a release branch)

### 4) Build release artifacts

Create versioned archives with the rule packs and key docs.

Windows (PowerShell):

```powershell
$version = (Get-Content CHANGELOG.md -Raw) -match '(?m)^##\s+v(?<v>[0-9]+\.[0-9]+\.[0-9]+)' | Out-Null; $zipv = $Matches['v']
$dest = Join-Path (Get-Location) 'dist'
New-Item -ItemType Directory -Force -Path $dest | Out-Null
Compress-Archive -Path packs\wp-core-security\* -DestinationPath (Join-Path $dest "wp-core-security-$zipv.zip") -Force
if (Test-Path packs\wp-core-quality) { Compress-Archive -Path packs\wp-core-quality\* -DestinationPath (Join-Path $dest "wp-core-quality-$zipv.zip") -Force }
Compress-Archive -Path packs\experimental\* -DestinationPath (Join-Path $dest "experimental-$zipv.zip") -Force
```

macOS/Linux (Bash):

```bash
version=$(grep -m1 -E '^##\s+v[0-9]+\.[0-9]+\.[0-9]+' CHANGELOG.md | sed -E 's/^## v//')
mkdir -p dist
( cd packs/wp-core-security && zip -r ../../dist/wp-core-security-$version.zip . )
[ -d packs/wp-core-quality ] && ( cd packs/wp-core-quality && zip -r ../../dist/wp-core-quality-$version.zip . )
( cd packs/experimental && zip -r ../../dist/experimental-$version.zip . )
```

Recommended: include a small `README.txt` in each archive (root-level docs/ can be used as the source), or add `docs/USER_GUIDE.md` to the release assets.

### 5) Tag and publish the release

```bash
git add -A
git commit -m "chore(release): vX.Y.Z"
git tag vX.Y.Z
git push origin HEAD --tags
```

Create a GitHub Release for `vX.Y.Z` and upload:

- `dist/wp-core-security-X.Y.Z.zip`
- `dist/wp-core-quality-X.Y.Z.zip` (if present)
- `dist/experimental-X.Y.Z.zip`
- `results/master-test-run-*.json` (optional)
- `results/reports/*` (optional)
- `docs/USER_GUIDE.md` and `docs/DEPLOYMENT_GUIDE.md` (optional)

### 6) How consumers can use the release

Option A — Clone/checkout:

```bash
git clone https://github.com/<org>/<repo>.git
cd <repo>
semgrep scan --config packs/wp-core-security --lang php /path/to/project
```

Option B — Download a zip from Releases:

```bash
unzip wp-core-security-X.Y.Z.zip -d rules
semgrep scan --config rules --lang php /path/to/project
```

Option C — Submodule/vendor (recommended for CI reproducibility):

```bash
git submodule add -b vX.Y.Z https://github.com/<org>/<repo>.git vendor/wordpress-semgrep-rules
semgrep scan --config vendor/wordpress-semgrep-rules/packs/wp-core-security --lang php /path/to/project
```

Notes:

- Use `--exclude '**/vendor/**' --exclude '**/node_modules/**'` for faster scans
- For large projects, add `--timeout 300` or scope with `--include '**/*.php'`

### 7) Optional: CI release validation

In your CI pipeline for tags:

```bash
python -m pip install --user --upgrade semgrep pyyaml
python tests/validate-rule-metadata.py --project-root .
python tests/run-all-tests.py --project-root .
# Upload results/ as build artifacts
```

### 8) Support and maintenance

- Keep `CHANGELOG.md` updated
- Address issues reported by consumers with example snippets
- Periodically re-run corpus scans and update metrics in `results/`
