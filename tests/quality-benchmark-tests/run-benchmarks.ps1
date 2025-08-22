# WordPress Semgrep Rules Quality Benchmarks
# PowerShell Script

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "WordPress Semgrep Rules Quality Benchmarks" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Get the project root (parent of tests directory)
$PROJECT_ROOT = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $PROJECT_ROOT

Write-Host "Project Root: $PROJECT_ROOT" -ForegroundColor Green
Write-Host ""

# Check if Python is available
try {
    $pythonVersion = python --version 2>&1
    Write-Host "Python: $pythonVersion" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Python is not available in PATH" -ForegroundColor Red
    Write-Host "Please install Python and ensure it's in your PATH" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

# Check if Semgrep is available
try {
    $semgrepVersion = semgrep --version 2>&1
    Write-Host "Semgrep: $semgrepVersion" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Semgrep is not available in PATH" -ForegroundColor Red
    Write-Host "Please install Semgrep and ensure it's in your PATH" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "Python and Semgrep are available" -ForegroundColor Green
Write-Host ""

# Run benchmarks
Write-Host "Running quality benchmarks..." -ForegroundColor Yellow
Write-Host ""

try {
    python "tests\quality-benchmark-tests\run-quality-benchmarks.py" "$PROJECT_ROOT"
} catch {
    Write-Host "ERROR: Failed to run benchmarks" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "Benchmarks completed!" -ForegroundColor Green
Write-Host "Check the results directory for detailed reports." -ForegroundColor Cyan
Write-Host ""

Read-Host "Press Enter to exit"
