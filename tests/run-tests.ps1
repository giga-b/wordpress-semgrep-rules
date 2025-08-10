#!/usr/bin/env pwsh
<#
.SYNOPSIS
    WordPress Semgrep Rules - Master Test Runner

.DESCRIPTION
    This script orchestrates all automated testing components including test execution,
    regression testing, and performance benchmarking for the WordPress Semgrep Rules project.

.PARAMETER Config
    Path to test configuration file (default: test-config.json)

.PARAMETER Mode
    Testing mode: 'all', 'tests', 'regression', 'performance', 'quick' (default: 'all')

.PARAMETER Output
    Output directory for test results (default: test-results/)

.PARAMETER Verbose
    Enable verbose output

.PARAMETER Html
    Generate HTML reports

.PARAMETER Baseline
    Path to baseline results file for regression testing

.PARAMETER Current
    Path to current results file for regression testing

.EXAMPLE
    .\run-tests.ps1 -Mode all -Verbose

.EXAMPLE
    .\run-tests.ps1 -Mode regression -Baseline baseline.json -Current current.json

.EXAMPLE
    .\run-tests.ps1 -Mode performance -Html
#>

param(
    [string]$Config = "test-config.json",
    [ValidateSet('all', 'tests', 'regression', 'performance', 'quick')]
    [string]$Mode = "all",
    [string]$Output = "test-results/",
    [switch]$Verbose,
    [switch]$Html,
    [string]$Baseline,
    [string]$Current
)

# Set error action preference
$ErrorActionPreference = "Stop"

# Function to write colored output
function Write-ColorOutput {
    param(
        [string]$Message,
        [string]$Color = "White"
    )
    Write-Host $Message -ForegroundColor $Color
}

# Function to check if Python is available
function Test-PythonAvailable {
    try {
        $pythonVersion = python --version 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-ColorOutput "Python found: $pythonVersion" "Green"
            return $true
        }
    }
    catch {
        Write-ColorOutput "Python not found in PATH" "Red"
        return $false
    }
    return $false
}

# Function to check if Semgrep is available
function Test-SemgrepAvailable {
    try {
        $semgrepVersion = semgrep --version 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-ColorOutput "Semgrep found: $semgrepVersion" "Green"
            return $true
        }
    }
    catch {
        Write-ColorOutput "Semgrep not found in PATH" "Red"
        return $false
    }
    return $false
}

# Function to install Python dependencies
function Install-PythonDependencies {
    Write-ColorOutput "Installing Python dependencies..." "Yellow"
    
    $requirements = @"
psutil>=5.8.0
"@
    
    $requirements | Out-File -FilePath "requirements-test.txt" -Encoding UTF8
    
    try {
        python -m pip install -r requirements-test.txt
        if ($LASTEXITCODE -eq 0) {
            Write-ColorOutput "Python dependencies installed successfully" "Green"
        } else {
            Write-ColorOutput "Failed to install Python dependencies" "Red"
            return $false
        }
    }
    catch {
        Write-ColorOutput "Error installing Python dependencies: $_" "Red"
        return $false
    }
    
    return $true
}

# Function to create output directory
function New-TestOutputDirectory {
    param([string]$Path)
    
    if (-not (Test-Path $Path)) {
        New-Item -ItemType Directory -Path $Path -Force | Out-Null
        Write-ColorOutput "Created output directory: $Path" "Green"
    }
}

# Function to run automated tests
function Invoke-AutomatedTests {
    param(
        [string]$Config,
        [string]$Output,
        [switch]$Verbose,
        [switch]$Html
    )
    
    Write-ColorOutput "Running Automated Tests..." "Yellow"
    
    $args = @(
        "run-automated-tests.py"
        "--config", $Config
        "--output", "$Output/automated-test-report.json"
    )
    
    if ($Verbose) {
        $args += "--verbose"
    }
    
    if ($Html) {
        $args += "--html"
    }
    
    try {
        python $args
        if ($LASTEXITCODE -eq 0) {
            Write-ColorOutput "Automated tests completed successfully" "Green"
            return $true
        } else {
            Write-ColorOutput "Automated tests failed" "Red"
            return $false
        }
    }
    catch {
        Write-ColorOutput "Error running automated tests: $_" "Red"
        return $false
    }
}

# Function to run regression tests
function Invoke-RegressionTests {
    param(
        [string]$Baseline,
        [string]$Current,
        [string]$Output,
        [switch]$Verbose,
        [switch]$Html
    )
    
    Write-ColorOutput "Running Regression Tests..." "Yellow"
    
    if (-not (Test-Path $Baseline)) {
        Write-ColorOutput "Baseline file not found: $Baseline" "Red"
        return $false
    }
    
    if (-not (Test-Path $Current)) {
        Write-ColorOutput "Current file not found: $Current" "Red"
        return $false
    }
    
    $args = @(
        "regression-tests.py"
        "--baseline", $Baseline
        "--current", $Current
        "--output", "$Output/regression-report.json"
    )
    
    if ($Verbose) {
        $args += "--verbose"
    }
    
    if ($Html) {
        $args += "--html"
    }
    
    try {
        python $args
        if ($LASTEXITCODE -eq 0) {
            Write-ColorOutput "Regression tests completed successfully" "Green"
            return $true
        } else {
            Write-ColorOutput "Regression tests detected issues" "Yellow"
            return $true  # Regression tests can fail but still complete
        }
    }
    catch {
        Write-ColorOutput "Error running regression tests: $_" "Red"
        return $false
    }
}

# Function to run performance benchmarks
function Invoke-PerformanceBenchmarks {
    param(
        [string]$Config,
        [string]$Output,
        [switch]$Verbose,
        [switch]$Html
    )
    
    Write-ColorOutput "Running Performance Benchmarks..." "Yellow"
    
    $args = @(
        "performance-benchmarks.py"
        "--config", $Config
        "--output", "$Output/performance-benchmark-report.json"
        "--iterations", "3"
        "--warmup", "1"
    )
    
    if ($Verbose) {
        $args += "--verbose"
    }
    
    if ($Html) {
        $args += "--html"
    }
    
    try {
        python $args
        if ($LASTEXITCODE -eq 0) {
            Write-ColorOutput "Performance benchmarks completed successfully" "Green"
            return $true
        } else {
            Write-ColorOutput "Performance benchmarks failed" "Red"
            return $false
        }
    }
    catch {
        Write-ColorOutput "Error running performance benchmarks: $_" "Red"
        return $false
    }
}

# Function to run quick tests
function Invoke-QuickTests {
    param(
        [string]$Config,
        [string]$Output,
        [switch]$Verbose,
        [switch]$Html
    )
    
    Write-ColorOutput "Running Quick Tests..." "Yellow"
    
    # Run only basic security tests with minimal iterations
    $args = @(
        "run-automated-tests.py"
        "--config", $Config
        "--output", "$Output/quick-test-report.json"
    )
    
    if ($Verbose) {
        $args += "--verbose"
    }
    
    if ($Html) {
        $args += "--html"
    }
    
    try {
        python $args
        if ($LASTEXITCODE -eq 0) {
            Write-ColorOutput "Quick tests completed successfully" "Green"
            return $true
        } else {
            Write-ColorOutput "Quick tests failed" "Red"
            return $false
        }
    }
    catch {
        Write-ColorOutput "Error running quick tests: $_" "Red"
        return $false
    }
}

# Function to generate summary report
function New-SummaryReport {
    param(
        [string]$Output,
        [hashtable]$Results
    )
    
    Write-ColorOutput "Generating Summary Report..." "Yellow"
    
    $summary = @{
        timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
        mode = $Mode
        results = $Results
        total_tests = 0
        passed_tests = 0
        failed_tests = 0
    }
    
    # Count results
    foreach ($result in $Results.Values) {
        if ($result -eq $true) {
            $summary.passed_tests++
        } else {
            $summary.failed_tests++
        }
        $summary.total_tests++
    }
    
    # Save summary
    $summaryPath = Join-Path $Output "test-summary.json"
    $summary | ConvertTo-Json -Depth 10 | Out-File -FilePath $summaryPath -Encoding UTF8
    
    # Display summary
    Write-ColorOutput "`nTest Summary:" "Cyan"
    Write-ColorOutput "  Mode: $Mode" "White"
    Write-ColorOutput "  Total Tests: $($summary.total_tests)" "White"
    Write-ColorOutput "  Passed: $($summary.passed_tests)" "Green"
    Write-ColorOutput "  Failed: $($summary.failed_tests)" "Red"
    Write-ColorOutput "  Success Rate: $([math]::Round(($summary.passed_tests / $summary.total_tests) * 100, 1))%" "White"
    Write-ColorOutput "  Summary saved to: $summaryPath" "Yellow"
    
    return $summary
}

# Main execution
function Main {
    Write-ColorOutput "WordPress Semgrep Rules - Master Test Runner" "Cyan"
    Write-ColorOutput "=============================================" "Cyan"
    
    # Check prerequisites
    Write-ColorOutput "`nChecking prerequisites..." "Yellow"
    
    if (-not (Test-PythonAvailable)) {
        Write-ColorOutput "Python is required but not found. Please install Python 3.7+ and add it to PATH." "Red"
        exit 1
    }
    
    if (-not (Test-SemgrepAvailable)) {
        Write-ColorOutput "Semgrep is required but not found. Please install Semgrep and add it to PATH." "Red"
        exit 1
    }
    
    # Install dependencies
    if (-not (Install-PythonDependencies)) {
        Write-ColorOutput "Failed to install Python dependencies. Exiting." "Red"
        exit 1
    }
    
    # Create output directory
    New-TestOutputDirectory -Path $Output
    
    # Load configuration
    if (-not (Test-Path $Config)) {
        Write-ColorOutput "Configuration file not found: $Config" "Red"
        exit 1
    }
    
    Write-ColorOutput "`nStarting test execution in mode: $Mode" "Yellow"
    
    $results = @{}
    $startTime = Get-Date
    
    # Execute tests based on mode
    switch ($Mode) {
        "all" {
            $results.automated_tests = Invoke-AutomatedTests -Config $Config -Output $Output -Verbose:$Verbose -Html:$Html
            $results.performance_benchmarks = Invoke-PerformanceBenchmarks -Config $Config -Output $Output -Verbose:$Verbose -Html:$Html
            
            # For regression testing, we need baseline and current files
            if ($Baseline -and $Current) {
                $results.regression_tests = Invoke-RegressionTests -Baseline $Baseline -Current $Current -Output $Output -Verbose:$Verbose -Html:$Html
            } else {
                Write-ColorOutput "Skipping regression tests - baseline and current files not provided" "Yellow"
                $results.regression_tests = $null
            }
        }
        "tests" {
            $results.automated_tests = Invoke-AutomatedTests -Config $Config -Output $Output -Verbose:$Verbose -Html:$Html
        }
        "regression" {
            if (-not $Baseline -or -not $Current) {
                Write-ColorOutput "Baseline and current files are required for regression testing" "Red"
                exit 1
            }
            $results.regression_tests = Invoke-RegressionTests -Baseline $Baseline -Current $Current -Output $Output -Verbose:$Verbose -Html:$Html
        }
        "performance" {
            $results.performance_benchmarks = Invoke-PerformanceBenchmarks -Config $Config -Output $Output -Verbose:$Verbose -Html:$Html
        }
        "quick" {
            $results.quick_tests = Invoke-QuickTests -Config $Config -Output $Output -Verbose:$Verbose -Html:$Html
        }
    }
    
    $endTime = Get-Date
    $duration = $endTime - $startTime
    
    # Generate summary
    $summary = New-SummaryReport -Output $Output -Results $results
    
    # Final status
    Write-ColorOutput "`nTest execution completed in $($duration.TotalSeconds.ToString('F1')) seconds" "Cyan"
    
    if ($summary.failed_tests -gt 0) {
        Write-ColorOutput "Some tests failed. Check the output files for details." "Red"
        exit 1
    } else {
        Write-ColorOutput "All tests passed successfully!" "Green"
        exit 0
    }
}

# Run main function
Main
