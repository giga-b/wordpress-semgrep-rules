#!/usr/bin/env pwsh
<#
.SYNOPSIS
    WordPress Semgrep Rules - Metrics Dashboard Runner

.DESCRIPTION
    This script runs the metrics dashboard for tracking rule performance and false positive rates.
    It handles setup, data collection, dashboard generation, and web server hosting.

.PARAMETER CollectMetrics
    Collect metrics from recent scans and test results

.PARAMETER GenerateDashboard
    Generate HTML dashboard from collected metrics

.PARAMETER ServeDashboard
    Serve dashboard on local web server

.PARAMETER Port
    Port for web server (default: 8080)

.PARAMETER Config
    Configuration file path (default: metrics-config.yaml)

.PARAMETER Output
    Output directory for dashboard files (default: dashboard)

.PARAMETER UpdateInterval
    Update interval in seconds (default: 300)

.PARAMETER InstallDependencies
    Install required Python dependencies

.PARAMETER OpenBrowser
    Automatically open browser when serving dashboard

.EXAMPLE
    .\run-metrics-dashboard.ps1 -CollectMetrics -GenerateDashboard

.EXAMPLE
    .\run-metrics-dashboard.ps1 -ServeDashboard -Port 9000

.EXAMPLE
    .\run-metrics-dashboard.ps1 -InstallDependencies -CollectMetrics -GenerateDashboard -ServeDashboard
#>

param(
    [switch]$CollectMetrics,
    [switch]$GenerateDashboard,
    [switch]$ServeDashboard,
    [int]$Port = 8080,
    [string]$Config = "metrics-config.yaml",
    [string]$Output = "dashboard",
    [int]$UpdateInterval = 300,
    [switch]$InstallDependencies,
    [switch]$OpenBrowser
)

# Set error action preference
$ErrorActionPreference = "Stop"

# Script configuration
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectRoot = Split-Path -Parent $ScriptDir
$PythonScript = Join-Path $ScriptDir "metrics_dashboard.py"
$ConfigFile = Join-Path $ScriptDir $Config

# Colors for output
$Colors = @{
    Info = "Cyan"
    Success = "Green"
    Warning = "Yellow"
    Error = "Red"
}

function Write-ColorOutput {
    param(
        [string]$Message,
        [string]$Color = "White"
    )
    Write-Host $Message -ForegroundColor $Colors[$Color]
}

function Test-PythonDependencies {
    <#
    .SYNOPSIS
        Test if required Python dependencies are installed
    #>
    Write-ColorOutput "Testing Python dependencies..." "Info"
    
    $requiredPackages = @(
        "matplotlib",
        "seaborn", 
        "pandas",
        "jinja2",
        "pyyaml"
    )
    
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
        Write-ColorOutput "Missing Python packages: $($missingPackages -join ', ')" "Warning"
        return $false
    }
    
    Write-ColorOutput "All Python dependencies are installed" "Success"
    return $true
}

function Install-PythonDependencies {
    <#
    .SYNOPSIS
        Install required Python dependencies
    #>
    Write-ColorOutput "Installing Python dependencies..." "Info"
    
    $packages = @(
        "matplotlib",
        "seaborn",
        "pandas", 
        "jinja2",
        "pyyaml"
    )
    
    foreach ($package in $packages) {
        Write-ColorOutput "Installing $package..." "Info"
        try {
            pip install $package
            if ($LASTEXITCODE -ne 0) {
                throw "Failed to install $package"
            }
        }
        catch {
            Write-ColorOutput "Failed to install $package - $($_.Exception.Message)" "Error"
            return $false
        }
    }
    
    Write-ColorOutput "All dependencies installed successfully" "Success"
    return $true
}

function Test-Configuration {
    <#
    .SYNOPSIS
        Test configuration file and environment
    #>
    Write-ColorOutput "Testing configuration..." "Info"
    
    # Check if config file exists
    if (-not (Test-Path $ConfigFile)) {
        Write-ColorOutput "Configuration file not found: $ConfigFile" "Warning"
        Write-ColorOutput "Using default configuration" "Info"
        return $true
    }
    
    # Validate YAML syntax
    try {
        $configContent = Get-Content $ConfigFile -Raw
        # Basic YAML validation - check for common syntax errors
        if ($configContent -match "^\s*[a-zA-Z_][a-zA-Z0-9_]*\s*:") {
            Write-ColorOutput "Configuration file syntax appears valid" "Success"
        } else {
            Write-ColorOutput "Configuration file may have syntax issues" "Warning"
        }
    }
    catch {
        Write-ColorOutput "Error reading configuration file - $($_.Exception.Message)" "Error"
        return $false
    }
    
    return $true
}

function Test-DataSources {
    <#
    .SYNOPSIS
        Test if data sources exist and are accessible
    #>
    Write-ColorOutput "Testing data sources..." "Info"
    
    $dataSources = @(
        "tests/test-results/automated-test-report.json",
        "performance-optimization-report.json",
        "semgrep-results.json",
        "test-results.json"
    )
    
    $availableSources = @()
    
    foreach ($source in $dataSources) {
        $sourcePath = Join-Path $ProjectRoot $source
        if (Test-Path $sourcePath) {
            $availableSources += $source
            Write-ColorOutput "Found data source: $source" "Success"
        } else {
            Write-ColorOutput "Data source not found: $source" "Warning"
        }
    }
    
    if ($availableSources.Count -eq 0) {
        Write-ColorOutput "No data sources found. Dashboard will show empty data." "Warning"
    }
    
    return $availableSources.Count -gt 0
}

function Start-MetricsCollection {
    <#
    .SYNOPSIS
        Start metrics collection process
    #>
    Write-ColorOutput "Starting metrics collection..." "Info"
    
    $arguments = @(
        $PythonScript
        "--collect-metrics"
    )
    
    if ($Config -ne "metrics-config.yaml") {
        $arguments += "--config", $Config
    }
    
    try {
        $process = Start-Process python -ArgumentList $arguments -Wait -PassThru -NoNewWindow
        if ($process.ExitCode -eq 0) {
            Write-ColorOutput "Metrics collection completed successfully" "Success"
            return $true
        } else {
            Write-ColorOutput "Metrics collection failed with exit code: $($process.ExitCode)" "Error"
            return $false
        }
    }
    catch {
        Write-ColorOutput "Error during metrics collection - $($_.Exception.Message)" "Error"
        return $false
    }
}

function Start-DashboardGeneration {
    <#
    .SYNOPSIS
        Generate HTML dashboard from collected metrics
    #>
    Write-ColorOutput "Generating dashboard..." "Info"
    
    $arguments = @(
        $PythonScript
        "--generate-dashboard"
        "--output", $Output
    )
    
    if ($Config -ne "metrics-config.yaml") {
        $arguments += "--config", $Config
    }
    
    try {
        $process = Start-Process python -ArgumentList $arguments -Wait -PassThru -NoNewWindow
        if ($process.ExitCode -eq 0) {
            Write-ColorOutput "Dashboard generation completed successfully" "Success"
            return $true
        } else {
            Write-ColorOutput "Dashboard generation failed with exit code: $($process.ExitCode)" "Error"
            return $false
        }
    }
    catch {
        Write-ColorOutput "Error during dashboard generation - $($_.Exception.Message)" "Error"
        return $false
    }
}

function Start-DashboardServer {
    <#
    .SYNOPSIS
        Start dashboard web server
    #>
    Write-ColorOutput "Starting dashboard server..." "Info"
    
    $dashboardPath = Join-Path $ProjectRoot $Output
    if (-not (Test-Path $dashboardPath)) {
        Write-ColorOutput "Dashboard directory not found: $dashboardPath" "Error"
        Write-ColorOutput "Run with -GenerateDashboard first" "Info"
        return $false
    }
    
    $arguments = @(
        $PythonScript
        "--serve-dashboard"
        "--port", $Port
        "--output", $Output
    )
    
    if ($Config -ne "metrics-config.yaml") {
        $arguments += "--config", $Config
    }
    
    try {
        Write-ColorOutput "Dashboard server starting on port $Port..." "Info"
        Write-ColorOutput "Access dashboard at: http://localhost:$Port" "Success"
        
        if ($OpenBrowser) {
            Start-Sleep -Seconds 2
            Start-Process "http://localhost:$Port"
        }
        
        $process = Start-Process python -ArgumentList $arguments -Wait -PassThru -NoNewWindow
        return $true
    }
    catch {
        Write-ColorOutput "Error starting dashboard server - $($_.Exception.Message)" "Error"
        return $false
    }
}

function Show-Usage {
    <#
    .SYNOPSIS
        Show usage information
    #>
    Write-ColorOutput @"
WordPress Semgrep Rules - Metrics Dashboard

Usage:
    .\run-metrics-dashboard.ps1 [options]

Options:
    -CollectMetrics      Collect metrics from recent scans
    -GenerateDashboard   Generate HTML dashboard
    -ServeDashboard      Serve dashboard on local web server
    -Port <int>         Port for web server (default: 8080)
    -Config <file>      Configuration file path
    -Output <dir>       Output directory for dashboard files
    -UpdateInterval <int> Update interval in seconds
    -InstallDependencies Install required Python dependencies
    -OpenBrowser        Automatically open browser when serving

Examples:
    .\run-metrics-dashboard.ps1 -InstallDependencies -CollectMetrics -GenerateDashboard
    .\run-metrics-dashboard.ps1 -ServeDashboard -Port 9000 -OpenBrowser
    .\run-metrics-dashboard.ps1 -CollectMetrics -GenerateDashboard -ServeDashboard

"@ "Info"
}

# Main execution
try {
    Write-ColorOutput "WordPress Semgrep Rules - Metrics Dashboard" "Info"
    Write-ColorOutput "=============================================" "Info"
    
    # Change to project root
    Set-Location $ProjectRoot
    
    # Show usage if no parameters provided
    if ($PSBoundParameters.Count -eq 0) {
        Show-Usage
        exit 0
    }
    
    # Install dependencies if requested
    if ($InstallDependencies) {
        if (-not (Install-PythonDependencies)) {
            exit 1
        }
    }
    
    # Test dependencies
    if (-not (Test-PythonDependencies)) {
        Write-ColorOutput "Missing Python dependencies. Use -InstallDependencies to install them." "Error"
        exit 1
    }
    
    # Test configuration
    if (-not (Test-Configuration)) {
        exit 1
    }
    
    # Test data sources
    Test-DataSources | Out-Null
    
    # Execute requested operations
    $success = $true
    
    if ($CollectMetrics) {
        if (-not (Start-MetricsCollection)) {
            $success = $false
        }
    }
    
    if ($GenerateDashboard) {
        if (-not (Start-DashboardGeneration)) {
            $success = $false
        }
    }
    
    if ($ServeDashboard) {
        if (-not (Start-DashboardServer)) {
            $success = $false
        }
    }
    
    if ($success) {
        Write-ColorOutput "Metrics dashboard operations completed successfully" "Success"
    } else {
        Write-ColorOutput "Some operations failed. Check the output above for details." "Error"
        exit 1
    }
}
catch {
    Write-ColorOutput "Unexpected error - $($_.Exception.Message)" "Error"
    exit 1
}
finally {
    # Restore original location
    if ($PWD.Path -ne $ProjectRoot) {
        Set-Location $ProjectRoot
    }
}
