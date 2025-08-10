#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Comprehensive Performance Testing Runner for WordPress Semgrep Rules

.DESCRIPTION
    This script runs comprehensive performance testing for WordPress Semgrep rules,
    including scan time analysis, memory usage monitoring, and optimization recommendations.

.PARAMETER Config
    Custom test configuration file path

.PARAMETER Rules
    Path to rules directory

.PARAMETER Tests
    Path to test files directory

.PARAMETER Output
    Output directory for test results

.PARAMETER Iterations
    Number of iterations per test (default: 10)

.PARAMETER Warmup
    Number of warmup runs (default: 3)

.PARAMETER Verbose
    Enable verbose output

.PARAMETER Json
    Output results in JSON format

.PARAMETER Html
    Generate HTML report

.PARAMETER Optimize
    Run optimization analysis

.PARAMETER Baseline
    Establish performance baseline

.PARAMETER Compare
    Compare against baseline

.PARAMETER Visualize
    Generate performance visualizations

.EXAMPLE
    .\run-comprehensive-performance-test.ps1 -Iterations 15 -Visualize

.EXAMPLE
    .\run-comprehensive-performance-test.ps1 -Config custom-config.json -Output ./results

.NOTES
    Requires Python 3.7+ and the following packages:
    - psutil
    - matplotlib
    - numpy
    - pyyaml
#>

param(
    [string]$Config,
    [string]$Rules,
    [string]$Tests,
    [string]$Output,
    [int]$Iterations = 10,
    [int]$Warmup = 3,
    [switch]$Verbose,
    [switch]$Json,
    [switch]$Html,
    [switch]$Optimize,
    [switch]$Baseline,
    [switch]$Compare,
    [switch]$Visualize
)

# Script configuration
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectRoot = Split-Path -Parent $ScriptDir
$PythonScript = Join-Path $ScriptDir "comprehensive-performance-test.py"

# Default values
if (-not $Rules) { $Rules = Join-Path $ProjectRoot "packs" }
if (-not $Tests) { $Tests = $ScriptDir }
if (-not $Output) { $Output = Join-Path $ScriptDir "performance-results" }

# Function to check Python availability
function Test-Python {
    try {
        $pythonVersion = python --version 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Host "Python found: $pythonVersion" -ForegroundColor Green
            return $true
        }
    }
    catch {
        Write-Host "Python not found in PATH" -ForegroundColor Red
        return $false
    }
    return $false
}

# Function to check required Python packages
function Test-PythonPackages {
    $requiredPackages = @("psutil", "matplotlib", "numpy", "yaml")
    $missingPackages = @()
    
    foreach ($package in $requiredPackages) {
        try {
            python -c "import $package" 2>$null
            if ($LASTEXITCODE -ne 0) {
                $missingPackages += $package
            }
        }
        catch {
            $missingPackages += $package
        }
    }
    
    if ($missingPackages.Count -gt 0) {
        Write-Host "Missing Python packages: $($missingPackages -join ', ')" -ForegroundColor Yellow
        Write-Host "Installing missing packages..." -ForegroundColor Yellow
        
        foreach ($package in $missingPackages) {
            Write-Host "Installing $package..." -ForegroundColor Yellow
            pip install $package
            if ($LASTEXITCODE -ne 0) {
                Write-Host "Failed to install $package" -ForegroundColor Red
                return $false
            }
        }
    }
    
    Write-Host "All required Python packages are available" -ForegroundColor Green
    return $true
}

# Function to check Semgrep availability
function Test-Semgrep {
    try {
        $semgrepVersion = semgrep --version 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Host "Semgrep found: $semgrepVersion" -ForegroundColor Green
            return $true
        }
    }
    catch {
        Write-Host "Semgrep not found in PATH" -ForegroundColor Red
        return $false
    }
    return $false
}

# Function to create output directory
function New-OutputDirectory {
    if (-not (Test-Path $Output)) {
        New-Item -ItemType Directory -Path $Output -Force | Out-Null
        Write-Host "Created output directory: $Output" -ForegroundColor Green
    }
}

# Function to run performance test
function Start-PerformanceTest {
    $arguments = @()
    
    if ($Config) { $arguments += "--config", $Config }
    if ($Rules) { $arguments += "--rules", $Rules }
    if ($Tests) { $arguments += "--tests", $Tests }
    if ($Output) { $arguments += "--output", $Output }
    if ($Iterations) { $arguments += "--iterations", $Iterations }
    if ($Warmup) { $arguments += "--warmup", $Warmup }
    if ($Verbose) { $arguments += "--verbose" }
    if ($Json) { $arguments += "--json" }
    if ($Html) { $arguments += "--html" }
    if ($Optimize) { $arguments += "--optimize" }
    if ($Baseline) { $arguments += "--baseline" }
    if ($Compare) { $arguments += "--compare" }
    if ($Visualize) { $arguments += "--visualize" }
    
    Write-Host "Running comprehensive performance test..." -ForegroundColor Cyan
    Write-Host "Command: python $PythonScript $($arguments -join ' ')" -ForegroundColor Gray
    
    $startTime = Get-Date
    
    try {
        & python $PythonScript @arguments
        $exitCode = $LASTEXITCODE
        
        $endTime = Get-Date
        $duration = $endTime - $startTime
        
        if ($exitCode -eq 0) {
            Write-Host "Performance test completed successfully in $($duration.TotalSeconds.ToString('F2')) seconds" -ForegroundColor Green
        } else {
            Write-Host "Performance test failed with exit code $exitCode" -ForegroundColor Red
        }
        
        return $exitCode
    }
    catch {
        Write-Host "Error running performance test: $($_.Exception.Message)" -ForegroundColor Red
        return 1
    }
}

# Function to run optimization analysis
function Start-OptimizationAnalysis {
    if (-not $Optimize) { return }
    
    Write-Host "Running optimization analysis..." -ForegroundColor Cyan
    
    $performanceReport = Join-Path $Output "comprehensive-performance-report.json"
    
    if (-not (Test-Path $performanceReport)) {
        Write-Host "Performance report not found: $performanceReport" -ForegroundColor Red
        return 1
    }
    
    $optimizerScript = Join-Path $ProjectRoot "tooling" "performance-optimizer.py"
    
    if (-not (Test-Path $optimizerScript)) {
        Write-Host "Optimizer script not found: $optimizerScript" -ForegroundColor Red
        return 1
    }
    
    $optimizerArgs = @(
        "--project-root", $ProjectRoot,
        "--performance-report", $performanceReport,
        "--output", (Join-Path $Output "optimization-report.json")
    )
    
    try {
        & python $optimizerScript @optimizerArgs
        $exitCode = $LASTEXITCODE
        
        if ($exitCode -eq 0) {
            Write-Host "Optimization analysis completed successfully" -ForegroundColor Green
        } else {
            Write-Host "Optimization analysis failed with exit code $exitCode" -ForegroundColor Red
        }
        
        return $exitCode
    }
    catch {
        Write-Host "Error running optimization analysis: $($_.Exception.Message)" -ForegroundColor Red
        return 1
    }
}

# Function to display results summary
function Show-ResultsSummary {
    $resultsFile = Join-Path $Output "comprehensive-performance-report.json"
    
    if (Test-Path $resultsFile) {
        Write-Host "`n" + "="*60 -ForegroundColor Cyan
        Write-Host "PERFORMANCE TEST RESULTS SUMMARY" -ForegroundColor Cyan
        Write-Host "="*60 -ForegroundColor Cyan
        
        try {
            $results = Get-Content $resultsFile | ConvertFrom-Json
            
            Write-Host "Total tests run: $($results.total_tests)" -ForegroundColor White
            Write-Host "Successful tests: $($results.successful_tests)" -ForegroundColor Green
            Write-Host "Failed tests: $($results.failed_tests)" -ForegroundColor Red
            Write-Host "Success rate: $([math]::Round($results.successful_tests / $results.total_tests * 100, 1))%" -ForegroundColor White
            Write-Host "Total duration: $([math]::Round($results.duration, 2)) seconds" -ForegroundColor White
            
            if ($results.performance_rankings.fastest_configs) {
                Write-Host "`nTop performing configurations:" -ForegroundColor Yellow
                for ($i = 0; $i -lt [math]::Min(3, $results.performance_rankings.fastest_configs.Count); $i++) {
                    Write-Host "  $($i + 1). $($results.performance_rankings.fastest_configs[$i])" -ForegroundColor White
                }
            }
            
            if ($results.optimization_recommendations) {
                Write-Host "`nOptimization recommendations:" -ForegroundColor Yellow
                for ($i = 0; $i -lt [math]::Min(3, $results.optimization_recommendations.Count); $i++) {
                    Write-Host "  $($i + 1). $($results.optimization_recommendations[$i])" -ForegroundColor White
                }
            }
        }
        catch {
            Write-Host "Error reading results file: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    
    # Show output files
    Write-Host "`nOutput files:" -ForegroundColor Yellow
    Get-ChildItem $Output -File | ForEach-Object {
        Write-Host "  $($_.Name)" -ForegroundColor White
    }
}

# Function to display help
function Show-Help {
    Write-Host "WordPress Semgrep Rules - Comprehensive Performance Testing" -ForegroundColor Cyan
    Write-Host "`nUsage: .\run-comprehensive-performance-test.ps1 [options]" -ForegroundColor White
    Write-Host "`nOptions:" -ForegroundColor Yellow
    Write-Host "  -Config <file>     Custom test configuration file" -ForegroundColor White
    Write-Host "  -Rules <path>      Path to rules directory" -ForegroundColor White
    Write-Host "  -Tests <path>      Path to test files directory" -ForegroundColor White
    Write-Host "  -Output <path>     Output directory for test results" -ForegroundColor White
    Write-Host "  -Iterations <int>  Number of iterations per test (default: 10)" -ForegroundColor White
    Write-Host "  -Warmup <int>      Number of warmup runs (default: 3)" -ForegroundColor White
    Write-Host "  -Verbose           Enable verbose output" -ForegroundColor White
    Write-Host "  -Json              Output results in JSON format" -ForegroundColor White
    Write-Host "  -Html              Generate HTML report" -ForegroundColor White
    Write-Host "  -Optimize          Run optimization analysis" -ForegroundColor White
    Write-Host "  -Baseline          Establish performance baseline" -ForegroundColor White
    Write-Host "  -Compare           Compare against baseline" -ForegroundColor White
    Write-Host "  -Visualize         Generate performance visualizations" -ForegroundColor White
    Write-Host "  -Help              Show this help message" -ForegroundColor White
    Write-Host "`nExamples:" -ForegroundColor Yellow
    Write-Host "  .\run-comprehensive-performance-test.ps1 -Iterations 15 -Visualize" -ForegroundColor White
    Write-Host "  .\run-comprehensive-performance-test.ps1 -Config custom-config.json -Output ./results" -ForegroundColor White
}

# Main execution
function Main {
    # Check for help parameter
    if ($args -contains "-Help" -or $args -contains "--help" -or $args -contains "-h") {
        Show-Help
        return 0
    }
    
    Write-Host "WordPress Semgrep Rules - Comprehensive Performance Testing" -ForegroundColor Cyan
    Write-Host "="*60 -ForegroundColor Cyan
    
    # Check prerequisites
    Write-Host "`nChecking prerequisites..." -ForegroundColor Yellow
    
    if (-not (Test-Python)) {
        Write-Host "Python is required but not found. Please install Python 3.7+ and add it to PATH." -ForegroundColor Red
        return 1
    }
    
    if (-not (Test-PythonPackages)) {
        Write-Host "Failed to install required Python packages." -ForegroundColor Red
        return 1
    }
    
    if (-not (Test-Semgrep)) {
        Write-Host "Semgrep is required but not found. Please install Semgrep and add it to PATH." -ForegroundColor Red
        return 1
    }
    
    # Check if Python script exists
    if (-not (Test-Path $PythonScript)) {
        Write-Host "Performance test script not found: $PythonScript" -ForegroundColor Red
        return 1
    }
    
    # Create output directory
    New-OutputDirectory
    
    # Run performance test
    $testResult = Start-PerformanceTest
    if ($testResult -ne 0) {
        return $testResult
    }
    
    # Run optimization analysis if requested
    if ($Optimize) {
        $optimizeResult = Start-OptimizationAnalysis
        if ($optimizeResult -ne 0) {
            Write-Host "Warning: Optimization analysis failed, but performance test completed successfully" -ForegroundColor Yellow
        }
    }
    
    # Display results summary
    Show-ResultsSummary
    
    Write-Host "`nPerformance testing completed!" -ForegroundColor Green
    return 0
}

# Run main function
exit (Main)
