param([switch]$InstallSemgrep)

Write-Host "Setting up WordPress Semgrep Rules Development Environment..." -ForegroundColor Green

# Check if Semgrep is installed
if (-not (Get-Command semgrep -ErrorAction SilentlyContinue)) {
    if ($InstallSemgrep) {
        Write-Host "Installing Semgrep..." -ForegroundColor Yellow
        pip install semgrep
    } else {
        Write-Host "Semgrep not found. Run with -InstallSemgrep flag to install automatically." -ForegroundColor Yellow
        Write-Host "Or install manually: pip install semgrep" -ForegroundColor Yellow
    }
} else {
    Write-Host "Semgrep is already installed" -ForegroundColor Green
}

# Test the setup
Write-Host "`nTesting rules against vulnerable examples..." -ForegroundColor Yellow
if (Test-Path "configs\plugin-development.yaml") {
    semgrep scan --config=configs/plugin-development.yaml tests/vulnerable-examples/ --json --output=test-results.json
    
    if (Test-Path "test-results.json") {
        Write-Host "✅ Test scan completed successfully" -ForegroundColor Green
        $results = Get-Content test-results.json | ConvertFrom-Json
        $findingCount = $results.results.Count
        Write-Host "Found $findingCount security issues in test files" -ForegroundColor Cyan
    } else {
        Write-Host "❌ Test scan failed" -ForegroundColor Red
    }
} else {
    Write-Host "❌ Configuration file not found" -ForegroundColor Red
}

Write-Host "`nTesting rules against safe examples..." -ForegroundColor Yellow
if (Test-Path "configs\plugin-development.yaml") {
    semgrep scan --config=configs/plugin-development.yaml tests/safe-examples/ --json --output=safe-test-results.json
    
    if (Test-Path "safe-test-results.json") {
        Write-Host "✅ Safe test scan completed successfully" -ForegroundColor Green
        $safeResults = Get-Content safe-test-results.json | ConvertFrom-Json
        $safeFindingCount = $safeResults.results.Count
        Write-Host "Found $safeFindingCount issues in safe examples (should be low)" -ForegroundColor Cyan
    } else {
        Write-Host "❌ Safe test scan failed" -ForegroundColor Red
    }
}

Write-Host "`nSetup complete! You can now:" -ForegroundColor Green
Write-Host "1. Open this folder in Cursor: C:\Users\mobet\DevProjects\wordpress-semgrep-rules" -ForegroundColor White
Write-Host "2. Edit rules in the packs/ directory" -ForegroundColor White
Write-Host "3. Test rules with: .\tooling\run-semgrep.ps1" -ForegroundColor White
Write-Host "4. Generate new rules with: python tooling\generate_rules.py" -ForegroundColor White

Write-Host "`nProject structure created:" -ForegroundColor Cyan
Get-ChildItem -Recurse -Directory | ForEach-Object {
    $indent = "  " * ($_.FullName.Split('\').Count - $PWD.Path.Split('\').Count)
    Write-Host "$indent $($_.Name)" -ForegroundColor White
}
