# WordPress Semgrep Rules Quality Benchmarks
# Cleanup Script

Write-Host "========================================" -ForegroundColor Yellow
Write-Host "WordPress Semgrep Rules Quality Benchmarks" -ForegroundColor Yellow
Write-Host "Cleanup Script" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow
Write-Host ""

Write-Host "WARNING: This will delete ALL test files and results!" -ForegroundColor Red
Write-Host ""
Write-Host "Files to be deleted:" -ForegroundColor Yellow
Write-Host "- 100 PHP test files" -ForegroundColor Yellow
Write-Host "- All benchmark results" -ForegroundColor Yellow
Write-Host "- Generated reports" -ForegroundColor Yellow
Write-Host ""

$confirm = Read-Host "Are you sure you want to continue? (yes/no)"

if ($confirm -eq "yes") {
    Write-Host ""
    Write-Host "Cleaning up test files..." -ForegroundColor Green
    
    # Delete PHP test files
    Get-ChildItem -Filter "*.php" | Remove-Item -Force
    Write-Host "- Deleted PHP test files" -ForegroundColor Green
    
    # Delete results directory
    if (Test-Path "results") {
        Remove-Item -Recurse -Force "results"
        Write-Host "- Deleted results directory" -ForegroundColor Green
    }
    
    # Delete generated files
    if (Test-Path "TEST_SUMMARY.md") {
        Remove-Item "TEST_SUMMARY.md" -Force
        Write-Host "- Deleted test summary" -ForegroundColor Green
    }
    
    # Keep the core files
    Write-Host ""
    Write-Host "Kept core files:" -ForegroundColor Cyan
    Write-Host "- README.md" -ForegroundColor Cyan
    Write-Host "- TEST_FILE_REGISTRY.md" -ForegroundColor Cyan
    Write-Host "- generate_test_files.py" -ForegroundColor Cyan
    Write-Host "- run-quality-benchmarks.py" -ForegroundColor Cyan
    Write-Host "- run-benchmarks.bat" -ForegroundColor Cyan
    Write-Host "- run-benchmarks.ps1" -ForegroundColor Cyan
    Write-Host "- cleanup.bat" -ForegroundColor Cyan
    Write-Host "- cleanup.ps1" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Cleanup completed successfully!" -ForegroundColor Green
} else {
    Write-Host "Cleanup cancelled." -ForegroundColor Yellow
}

Write-Host ""
Read-Host "Press Enter to exit"
