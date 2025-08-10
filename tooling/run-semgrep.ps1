param(
    [string]$Config = "configs/plugin-development.yaml",
    [string]$Path = ".",
    [switch]$Install
)

$version = "1.75.0"

# Check if Semgrep is installed
if (-not (Get-Command semgrep -ErrorAction SilentlyContinue)) {
    if ($Install) {
        Write-Host "Installing Semgrep version $version..."
        pip install "semgrep==$version"
    } else {
        Write-Host "Semgrep not found. Use -Install flag to install automatically."
        Write-Host "Or install manually: pip install semgrep==$version"
        exit 1
    }
}

# Check version
$currentVersion = semgrep --version
Write-Host "Using Semgrep: $currentVersion"

# Run Semgrep
Write-Host "Running Semgrep with config: $Config"
Write-Host "Scanning path: $Path"

semgrep scan --config $Config --json --output semgrep-results.json $Path

# Check for critical findings
$results = Get-Content semgrep-results.json | ConvertFrom-Json
$criticalFindings = $results.results | Where-Object { $_.extra.severity -eq "ERROR" }

if ($criticalFindings) {
    Write-Host "`n❌ Critical security findings detected:" -ForegroundColor Red
    foreach ($finding in $criticalFindings) {
        Write-Host "- $($finding.extra.message)" -ForegroundColor Red
        Write-Host "  File: $($finding.path):$($finding.start.line)" -ForegroundColor Yellow
    }
    exit 1
} else {
    Write-Host "`n✅ No critical security issues found" -ForegroundColor Green
}

Write-Host "`nResults saved to: semgrep-results.json"
