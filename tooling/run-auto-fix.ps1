#!/usr/bin/env pwsh
<#
.SYNOPSIS
    WordPress Semgrep Rules - Auto-fix Runner Script
    
.DESCRIPTION
    This script provides an easy-to-use interface for applying automatic fixes
    to WordPress security issues detected by Semgrep rules.
    
.PARAMETER Results
    Path to Semgrep results JSON file (required)
    
.PARAMETER Backup
    Create backups before applying fixes
    
.PARAMETER DryRun
    Show what would be fixed without applying changes
    
.PARAMETER Output
    Output report file name (default: auto-fix-report.json)
    
.PARAMETER Config
    Path to auto-fix configuration file
    
.PARAMETER Verbose
    Enable verbose output
    
.PARAMETER InstallDependencies
    Install required Python dependencies
    
.PARAMETER OpenReport
    Automatically open the generated report
    
.PARAMETER Help
    Show this help message
    
.EXAMPLE
    .\run-auto-fix.ps1 -Results semgrep-results.json -Backup
    
.EXAMPLE
    .\run-auto-fix.ps1 -Results semgrep-results.json -DryRun -Verbose
    
.EXAMPLE
    .\run-auto-fix.ps1 -Results semgrep-results.json -InstallDependencies -Backup -OpenReport
#>

param(
    [Parameter(Mandatory=$true)]
    [string]$Results,
    
    [switch]$Backup,
    [switch]$DryRun,
    [string]$Output = "auto-fix-report.json",
    [string]$Config,
    [switch]$VerboseOutput,
    [switch]$InstallDependencies,
    [switch]$OpenReport,
    [switch]$Help
)

$scriptVersion = "1.0.0"
$startTime = Get-Date

# Color functions
function Write-ColorOutput {
    param([string]$Message, [string]$Color = "White")
    Write-Host $Message -ForegroundColor $Color
}

function Write-Success { param([string]$Message) Write-ColorOutput $Message "Green" }
function Write-Warning { param([string]$Message) Write-ColorOutput $Message "Yellow" }
function Write-Error { param([string]$Message) Write-ColorOutput $Message "Red" }
function Write-Info { param([string]$Message) Write-ColorOutput $Message "Cyan" }

# Help function
function Show-Help {
    Write-ColorOutput "WordPress Semgrep Rules - Auto-fix System v$scriptVersion" "Cyan"
    Write-ColorOutput "Usage: .\run-auto-fix.ps1 [OPTIONS]" "White"
    Write-ColorOutput ""
    Write-ColorOutput "Required Parameters:" "Yellow"
    Write-ColorOutput "  -Results <file>        Path to Semgrep results JSON file" "White"
    Write-ColorOutput ""
    Write-ColorOutput "Optional Parameters:" "Yellow"
    Write-ColorOutput "  -Backup                Create backups before applying fixes" "White"
    Write-ColorOutput "  -DryRun                Show what would be fixed without applying changes" "White"
    Write-ColorOutput "  -Output <file>         Output report file (default: auto-fix-report.json)" "White"
    Write-ColorOutput "  -Config <file>         Path to auto-fix configuration file" "White"
    Write-ColorOutput "  -Verbose               Enable verbose output" "White"
    Write-ColorOutput "  -InstallDependencies   Install required Python dependencies" "White"
    Write-ColorOutput "  -OpenReport            Automatically open the generated report" "White"
    Write-ColorOutput "  -Help                  Show this help message" "White"
    Write-ColorOutput ""
    Write-ColorOutput "Examples:" "Yellow"
    Write-ColorOutput "  .\run-auto-fix.ps1 -Results semgrep-results.json -Backup" "White"
    Write-ColorOutput "  .\run-auto-fix.ps1 -Results semgrep-results.json -DryRun -Verbose" "White"
    Write-ColorOutput "  .\run-auto-fix.ps1 -Results semgrep-results.json -InstallDependencies" "White"
}

if ($Help) {
    Show-Help
    exit 0
}

# Check if virtual environment exists and activate it
function Test-VirtualEnvironment {
    $venvPath = ".venv"
    if (Test-Path $venvPath) {
        Write-Info "Found virtual environment, activating..."
        & "$venvPath\Scripts\Activate.ps1"
        return $true
    } else {
        Write-Warning "Virtual environment not found. Creating one..."
        python -m venv .venv
        & ".venv\Scripts\Activate.ps1"
        return $true
    }
}

# Check Python dependencies
function Test-PythonDependencies {
    $requiredPackages = @("yaml", "argparse", "json", "re", "os", "sys", "pathlib", "typing", "dataclasses", "datetime", "logging")
    $missingPackages = @()
    
    foreach ($package in $requiredPackages) {
        try {
            python -c "import $package" 2>$null
            if ($LASTEXITCODE -ne 0) {
                $missingPackages += $package
            }
        } catch {
            $missingPackages += $package
        }
    }
    
    return $missingPackages
}

# Install Python dependencies
function Install-PythonDependencies {
    Write-Info "Installing Python dependencies..."
    
    $packages = @("pyyaml", "matplotlib", "seaborn", "pandas", "jinja2")
    
    foreach ($package in $packages) {
        Write-Info "Installing $package..."
        pip install $package
        if ($LASTEXITCODE -ne 0) {
            Write-Error "Failed to install $package"
            return $false
        }
    }
    
    Write-Success "All dependencies installed successfully"
    return $true
}

# Validate configuration
function Test-Configuration {
    param([string]$ConfigPath)
    
    if (-not $ConfigPath) {
        $ConfigPath = "tooling/auto-fix-config.yaml"
    }
    
    if (-not (Test-Path $ConfigPath)) {
        Write-Warning "Configuration file not found: $ConfigPath"
        return $false
    }
    
    try {
        $configContent = Get-Content $ConfigPath -Raw
        if ($configContent -match "settings:") {
            Write-Success "Configuration file is valid"
            return $true
        } else {
            Write-Error "Invalid configuration format"
            return $false
        }
    } catch {
        Write-Error "Error reading configuration: $($_.Exception.Message)"
        return $false
    }
}

# Check data sources
function Test-DataSources {
    param([string]$ResultsFile)
    
    if (-not (Test-Path $ResultsFile)) {
        Write-Error "Results file not found: $ResultsFile"
        return $false
    }
    
    try {
        $results = Get-Content $ResultsFile | ConvertFrom-Json
        if ($results.results) {
            Write-Success "Found $($results.results.Count) issues in results file"
            return $true
        } else {
            Write-Warning "No results found in file"
            return $false
        }
    } catch {
        Write-Error "Error reading results file: $($_.Exception.Message)"
        return $false
    }
}

# Main execution function
function Start-AutoFix {
    Write-ColorOutput "WordPress Semgrep Rules - Auto-fix System" "Cyan"
    Write-ColorOutput "=============================================" "Cyan"
    Write-ColorOutput "Version: $scriptVersion" "White"
    Write-ColorOutput "Start Time: $startTime" "White"
    Write-ColorOutput ""
    
    # Check and activate virtual environment
    if (-not (Test-VirtualEnvironment)) {
        Write-Error "Failed to set up virtual environment"
        exit 1
    }
    
    # Check dependencies
    $missingDeps = Test-PythonDependencies
    if ($missingDeps.Count -gt 0) {
        if ($InstallDependencies) {
            if (-not (Install-PythonDependencies)) {
                Write-Error "Failed to install dependencies"
                exit 1
            }
        } else {
            Write-Error "Missing Python dependencies: $($missingDeps -join ', ')"
            Write-Info "Use -InstallDependencies to install them automatically"
            exit 1
        }
    }
    
    # Validate configuration
    if (-not (Test-Configuration $Config)) {
        Write-Warning "Configuration validation failed, continuing with defaults"
    }
    
    # Check data sources
    if (-not (Test-DataSources $Results)) {
        Write-Error "Data source validation failed"
        exit 1
    }
    
    # Build command arguments
    $args = @("tooling/auto_fix.py", "--results", $Results, "--output", $Output)
    
    if ($Backup) {
        $args += "--backup"
    }
    
    if ($DryRun) {
        $args += "--dry-run"
    }
    
    if ($Config) {
        $args += "--config", $Config
    }
    
    if ($VerboseOutput) {
        $args += "--verbose"
    }
    
    # Execute auto-fix
    Write-Info "Starting auto-fix process..."
    Write-Info "Command: python $($args -join ' ')"
    
    try {
        $process = Start-Process python -ArgumentList $args -Wait -PassThru -NoNewWindow
        
        if ($process.ExitCode -eq 0) {
            Write-Success "Auto-fix completed successfully"
            
            # Check if report was generated
            if (Test-Path $Output) {
                Write-Info "Report generated: $Output"
                
                if ($OpenReport) {
                    Write-Info "Opening report..."
                    if ($Output -like "*.html") {
                        Start-Process $Output
                    } else {
                        Start-Process notepad $Output
                    }
                }
            }
        } else {
            Write-Error "Auto-fix failed with exit code: $($process.ExitCode)"
            exit $process.ExitCode
        }
    } catch {
        Write-Error "Error executing auto-fix: $($_.Exception.Message)"
        exit 1
    }
    
    $endTime = Get-Date
    $duration = $endTime - $startTime
    
    Write-ColorOutput ""
    Write-ColorOutput "Auto-fix Summary" "Cyan"
    Write-ColorOutput "================" "Cyan"
    Write-ColorOutput "Start Time: $startTime" "White"
    Write-ColorOutput "End Time: $endTime" "White"
    Write-ColorOutput "Duration: $($duration.TotalSeconds.ToString('F2')) seconds" "White"
    Write-ColorOutput "Results File: $Results" "White"
    Write-ColorOutput "Report File: $Output" "White"
    
    if ($Backup) {
        Write-ColorOutput "Backups: Created" "Green"
    }
    
    if ($DryRun) {
        Write-ColorOutput "Mode: Dry Run (no changes applied)" "Yellow"
    } else {
        Write-ColorOutput "Mode: Live Fix (changes applied)" "Green"
    }
}

# Error handling
trap {
    Write-Error "An error occurred: $($_.Exception.Message)"
    Write-Error "Stack trace: $($_.ScriptStackTrace)"
    exit 1
}

# Main execution
try {
    Start-AutoFix
} catch {
    Write-Error "Fatal error: $($_.Exception.Message)"
    exit 1
}
