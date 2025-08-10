param(
    [string]$Config = "configs/plugin-development.yaml",
    [string]$Path = ".",
    [switch]$Install,
    [switch]$Verbose,
    [switch]$Performance,
    [switch]$Validate,
    [switch]$Cache,
    [switch]$Incremental,
    [string]$Output = "semgrep-results.json",
    [string]$Report = "semgrep-report.html",
    [int]$Timeout = 300,
    [switch]$Help
)

$version = "1.75.0"
$startTime = Get-Date
$scriptVersion = "2.0.0"

# Help function
function Show-Help {
    Write-Host "WordPress Semgrep Security Scanner v$scriptVersion" -ForegroundColor Cyan
    Write-Host "Usage: .\run-semgrep.ps1 [OPTIONS]" -ForegroundColor White
    Write-Host ""
    Write-Host "Options:" -ForegroundColor Yellow
    Write-Host "  -Config <file>        Configuration file (default: configs/plugin-development.yaml)" -ForegroundColor White
    Write-Host "  -Path <path>          Path to scan (default: .)" -ForegroundColor White
    Write-Host "  -Install              Install Semgrep if not found" -ForegroundColor White
    Write-Host "  -Verbose              Enable verbose output" -ForegroundColor White
    Write-Host "  -Performance          Enable performance monitoring" -ForegroundColor White
    Write-Host "  -Validate             Validate configuration before scanning" -ForegroundColor White
    Write-Host "  -Cache                Enable caching for repeated scans" -ForegroundColor White
    Write-Host "  -Incremental          Only scan changed files (requires git)" -ForegroundColor White
    Write-Host "  -Output <file>        Output file for results (default: semgrep-results.json)" -ForegroundColor White
    Write-Host "  -Report <file>        HTML report file (default: semgrep-report.html)" -ForegroundColor White
    Write-Host "  -Timeout <seconds>    Scan timeout in seconds (default: 300)" -ForegroundColor White
    Write-Host "  -Help                 Show this help message" -ForegroundColor White
    Write-Host ""
    Write-Host "Available Configurations:" -ForegroundColor Yellow
    Write-Host "  configs/basic.yaml                    - Essential security rules" -ForegroundColor White
    Write-Host "  configs/strict.yaml                   - Comprehensive security coverage" -ForegroundColor White
    Write-Host "  configs/plugin-development.yaml       - WordPress plugin development" -ForegroundColor White
    Write-Host "  configs/optimized-15s.yaml           - Fast scanning (< 15s)" -ForegroundColor White
    Write-Host "  configs/optimized-30s.yaml           - Balanced scanning (< 30s)" -ForegroundColor White
    Write-Host "  configs/performance-optimized.yaml   - Performance-focused rules" -ForegroundColor White
    Write-Host ""
    Write-Host "Examples:" -ForegroundColor Yellow
    Write-Host "  .\run-semgrep.ps1 -Config configs/strict.yaml -Verbose" -ForegroundColor White
    Write-Host "  .\run-semgrep.ps1 -Path src/ -Performance -Cache" -ForegroundColor White
    Write-Host "  .\run-semgrep.ps1 -Incremental -Output results.json" -ForegroundColor White
}

if ($Help) {
    Show-Help
    exit 0
}

# Configuration validation function
function Test-Configuration {
    param([string]$ConfigPath)
    
    if (-not (Test-Path $ConfigPath)) {
        Write-Host "❌ Configuration file not found: $ConfigPath" -ForegroundColor Red
        return $false
    }
    
    try {
        $configContent = Get-Content $ConfigPath -Raw
        if ($configContent -match "rules:") {
            Write-Host "✅ Configuration file is valid" -ForegroundColor Green
            return $true
        } else {
            Write-Host "❌ Invalid configuration format" -ForegroundColor Red
            return $false
        }
    } catch {
        Write-Host "❌ Error reading configuration: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Performance monitoring function
function Start-PerformanceMonitoring {
    $global:performanceData = @{
        StartTime = Get-Date
        MemoryUsage = @()
        CpuUsage = @()
    }
    
    if ($Performance) {
        Write-Host "📊 Performance monitoring enabled" -ForegroundColor Cyan
        $job = Start-Job -ScriptBlock {
            while ($true) {
                $process = Get-Process -Name "semgrep" -ErrorAction SilentlyContinue
                if ($process) {
                    $memory = $process.WorkingSet64 / 1MB
                    $cpu = $process.CPU
                    [PSCustomObject]@{
                        Timestamp = Get-Date
                        MemoryMB = [math]::Round($memory, 2)
                        CpuTime = $cpu
                    }
                }
                Start-Sleep -Seconds 5
            }
        }
        $global:performanceJob = $job
    }
}

function Stop-PerformanceMonitoring {
    if ($global:performanceJob) {
        Stop-Job $global:performanceJob
        Remove-Job $global:performanceJob
        $global:performanceJob = $null
    }
}

# Cache management function
function Get-CacheKey {
    param([string]$ConfigPath, [string]$ScanPath)
    $configHash = (Get-FileHash $ConfigPath -Algorithm SHA256).Hash.Substring(0, 8)
    $pathHash = (Get-FileHash -InputObject $ScanPath -Algorithm SHA256).Hash.Substring(0, 8)
    return "semgrep_cache_${configHash}_${pathHash}.json"
}

function Test-Cache {
    param([string]$CacheKey)
    $cacheFile = Join-Path $env:TEMP $CacheKey
    if (Test-Path $cacheFile) {
        $cacheAge = (Get-Date) - (Get-Item $cacheFile).LastWriteTime
        if ($cacheAge.TotalHours -lt 24) {
            Write-Host "📋 Using cached results (age: $([math]::Round($cacheAge.TotalMinutes, 1)) minutes)" -ForegroundColor Yellow
            return $cacheFile
        }
    }
    return $null
}

function Save-Cache {
    param([string]$CacheKey, [string]$ResultsFile)
    $cacheFile = Join-Path $env:TEMP $CacheKey
    Copy-Item $ResultsFile $cacheFile
    Write-Host "📋 Results cached for future scans" -ForegroundColor Green
}

# Enhanced incremental scanning function
function Get-ChangedFiles {
    if (-not (Test-Path ".git")) {
        Write-Host "⚠️  Git repository not found, performing full scan" -ForegroundColor Yellow
        return $Path
    }
    
    try {
        # Use Python incremental scanner if available
        if (Get-Command python -ErrorAction SilentlyContinue) {
            $pythonScript = Join-Path $PSScriptRoot "incremental_scanner.py"
            if (Test-Path $pythonScript) {
                Write-Host "🔍 Using enhanced incremental scanning..." -ForegroundColor Cyan
                
                $result = python $pythonScript $Config $Path 2>$null
                if ($LASTEXITCODE -eq 0) {
                    $lines = $result -split "`n"
                    $scanType = $lines | Where-Object { $_ -match "Scan Type:" } | ForEach-Object { ($_ -split ": ")[1] }
                    $changedFiles = $lines | Where-Object { $_ -match "Changed Files:" } | ForEach-Object { [int]($_ -split ": ")[1] }
                    $affectedFiles = $lines | Where-Object { $_ -match "Affected Files:" } | ForEach-Object { [int]($_ -split ": ")[1] }
                    
                    Write-Host "📊 Scan Analysis:" -ForegroundColor Cyan
                    Write-Host "  Scan Type: $scanType" -ForegroundColor White
                    Write-Host "  Changed Files: $changedFiles" -ForegroundColor White
                    Write-Host "  Affected Files: $affectedFiles" -ForegroundColor White
                    
                    # Get scan paths from Python script
                    $scanPaths = $lines | Where-Object { $_ -match "Scan Paths:" } | ForEach-Object { ($_ -split ": ")[1] }
                    if ($scanPaths -and $scanPaths -gt 0) {
                        Write-Host "  Scan Paths: $scanPaths" -ForegroundColor White
                        return $Path  # Use full path for now, could be enhanced to use specific paths
                    }
                }
            }
        }
        
        # Fallback to basic git-based detection
        Write-Host "📝 Using basic git-based change detection..." -ForegroundColor Cyan
        $changedFiles = git diff --name-only HEAD~1 2>$null
        if ($changedFiles) {
            $phpFiles = $changedFiles | Where-Object { $_ -match "\.php$" }
            if ($phpFiles) {
                Write-Host "📝 Scanning $($phpFiles.Count) changed PHP files" -ForegroundColor Cyan
                return $phpFiles -join " "
            }
        }
        
        # Check for untracked files
        $untrackedFiles = git ls-files --others --exclude-standard 2>$null
        if ($untrackedFiles) {
            $phpUntracked = $untrackedFiles | Where-Object { $_ -match "\.php$" }
            if ($phpUntracked) {
                Write-Host "📝 Found $($phpUntracked.Count) untracked PHP files" -ForegroundColor Cyan
                return $phpUntracked -join " "
            }
        }
        
    } catch {
        Write-Host "⚠️  Error getting changed files, performing full scan" -ForegroundColor Yellow
    }
    
    return $Path
}

# Result analysis function
function Analyze-Results {
    param([string]$ResultsFile)
    
    if (-not (Test-Path $ResultsFile)) {
        Write-Host "❌ Results file not found: $ResultsFile" -ForegroundColor Red
        return
    }
    
    try {
        $results = Get-Content $ResultsFile | ConvertFrom-Json
        
        $totalFindings = $results.results.Count
        $errorFindings = ($results.results | Where-Object { $_.extra.severity -eq "ERROR" }).Count
        $warningFindings = ($results.results | Where-Object { $_.extra.severity -eq "WARNING" }).Count
        $infoFindings = ($results.results | Where-Object { $_.extra.severity -eq "INFO" }).Count
        
        Write-Host "`n📊 Scan Results Summary:" -ForegroundColor Cyan
        Write-Host "  Total Findings: $totalFindings" -ForegroundColor White
        Write-Host "  Errors: $errorFindings" -ForegroundColor Red
        Write-Host "  Warnings: $warningFindings" -ForegroundColor Yellow
        Write-Host "  Info: $infoFindings" -ForegroundColor Blue
        
        # Group by rule
        $ruleGroups = $results.results | Group-Object { $_.check_id }
        Write-Host "`n📋 Findings by Rule:" -ForegroundColor Cyan
        foreach ($group in $ruleGroups | Sort-Object Count -Descending) {
            $severity = ($group.Group[0].extra.severity).ToUpper()
            $color = switch ($severity) {
                "ERROR" { "Red" }
                "WARNING" { "Yellow" }
                "INFO" { "Blue" }
                default { "White" }
            }
            Write-Host "  $($group.Name): $($group.Count) ($severity)" -ForegroundColor $color
        }
        
        # Show critical findings
        if ($errorFindings -gt 0) {
            Write-Host "`n❌ Critical Security Issues:" -ForegroundColor Red
            $criticalFindings = $results.results | Where-Object { $_.extra.severity -eq "ERROR" }
            foreach ($finding in $criticalFindings | Select-Object -First 10) {
                Write-Host "  • $($finding.extra.message)" -ForegroundColor Red
                Write-Host "    File: $($finding.path):$($finding.start.line)" -ForegroundColor Yellow
                if ($finding.extra.fix) {
                    Write-Host "    Fix: $($finding.extra.fix)" -ForegroundColor Green
                }
            }
            
            if ($criticalFindings.Count -gt 10) {
                Write-Host "    ... and $($criticalFindings.Count - 10) more critical issues" -ForegroundColor Red
            }
        }
        
        return @{
            Total = $totalFindings
            Errors = $errorFindings
            Warnings = $warningFindings
            Info = $infoFindings
        }
        
    } catch {
        Write-Host "❌ Error analyzing results: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Generate HTML report
function Generate-HTMLReport {
    param([string]$ResultsFile, [string]$ReportFile, [hashtable]$Stats)
    
    try {
        $results = Get-Content $ResultsFile | ConvertFrom-Json
        $scanTime = (Get-Date) - $startTime
        
        $html = @"
<!DOCTYPE html>
<html>
<head>
    <title>WordPress Semgrep Security Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 5px; }
        .summary { background: #ecf0f1; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .finding { border: 1px solid #bdc3c7; margin: 10px 0; padding: 15px; border-radius: 5px; }
        .error { border-left: 5px solid #e74c3c; }
        .warning { border-left: 5px solid #f39c12; }
        .info { border-left: 5px solid #3498db; }
        .file { font-family: monospace; background: #f8f9fa; padding: 2px 5px; }
        .fix { background: #d5f4e6; padding: 10px; margin: 10px 0; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>WordPress Semgrep Security Report</h1>
        <p>Generated: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')</p>
        <p>Scan Duration: $([math]::Round($scanTime.TotalSeconds, 2)) seconds</p>
    </div>
    
    <div class="summary">
        <h2>Summary</h2>
        <p><strong>Total Findings:</strong> $($Stats.Total)</p>
        <p><strong>Errors:</strong> $($Stats.Errors)</p>
        <p><strong>Warnings:</strong> $($Stats.Warnings)</p>
        <p><strong>Info:</strong> $($Stats.Info)</p>
    </div>
    
    <h2>Findings</h2>
"@
        
        foreach ($finding in $results.results) {
            $severityClass = $finding.extra.severity.ToLower()
            $html += @"
    <div class="finding $severityClass">
        <h3>$($finding.check_id)</h3>
        <p><strong>Severity:</strong> $($finding.extra.severity)</p>
        <p><strong>Message:</strong> $($finding.extra.message)</p>
        <p><strong>File:</strong> <span class="file">$($finding.path):$($finding.start.line)</span></p>
"@
            
            if ($finding.extra.fix) {
                $html += @"
        <div class="fix">
            <strong>Suggested Fix:</strong><br>
            <code>$($finding.extra.fix)</code>
        </div>
"@
            }
            
            $html += "</div>`n"
        }
        
        $html += @"
</body>
</html>
"@
        
        $html | Out-File -FilePath $ReportFile -Encoding UTF8
        Write-Host "📄 HTML report generated: $ReportFile" -ForegroundColor Green
        
    } catch {
        Write-Host "❌ Error generating HTML report: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Main execution
Write-Host "🔍 WordPress Semgrep Security Scanner v$scriptVersion" -ForegroundColor Cyan
Write-Host "Configuration: $Config" -ForegroundColor White
Write-Host "Scan Path: $Path" -ForegroundColor White

# Validate configuration if requested
if ($Validate) {
    if (-not (Test-Configuration $Config)) {
        exit 1
    }
}

# Check if Semgrep is installed
if (-not (Get-Command semgrep -ErrorAction SilentlyContinue)) {
    if ($Install) {
        Write-Host "📦 Installing Semgrep version $version..." -ForegroundColor Yellow
        try {
            pip install "semgrep==$version"
            if ($LASTEXITCODE -ne 0) {
                Write-Host "❌ Failed to install Semgrep" -ForegroundColor Red
                exit 1
            }
        } catch {
            Write-Host "❌ Error installing Semgrep: $($_.Exception.Message)" -ForegroundColor Red
            exit 1
        }
    } else {
        Write-Host "❌ Semgrep not found. Use -Install flag to install automatically." -ForegroundColor Red
        Write-Host "Or install manually: pip install semgrep==$version" -ForegroundColor Yellow
        exit 1
    }
}

# Check version
$currentVersion = semgrep --version
Write-Host "📋 Using Semgrep: $currentVersion" -ForegroundColor Green

# Handle incremental scanning
$scanPath = $Path
if ($Incremental) {
    $scanPath = Get-ChangedFiles
}

# Handle caching
$cacheKey = $null
if ($Cache) {
    $cacheKey = Get-CacheKey $Config $scanPath
    $cachedResults = Test-Cache $cacheKey
    if ($cachedResults) {
        Copy-Item $cachedResults $Output
        Write-Host "✅ Using cached results" -ForegroundColor Green
        exit 0
    }
}

# Start performance monitoring
Start-PerformanceMonitoring

# Run Semgrep with timeout
Write-Host "🚀 Starting security scan..." -ForegroundColor Green
if ($Verbose) {
    Write-Host "Command: semgrep scan --config $Config --json --output $Output $scanPath" -ForegroundColor Gray
}

try {
    $job = Start-Job -ScriptBlock {
        param($Config, $Output, $ScanPath)
        semgrep scan --config $Config --json --output $Output $ScanPath
    } -ArgumentList $Config, $Output, $scanPath
    
    $completed = Wait-Job $job -Timeout $Timeout
    
    if ($completed) {
        $result = Receive-Job $job
        if ($Verbose) {
            Write-Host $result
        }
    } else {
        Stop-Job $job
        Remove-Job $job
        Write-Host "❌ Scan timed out after $Timeout seconds" -ForegroundColor Red
        exit 1
    }
    
    Remove-Job $job
    
} catch {
    Write-Host "❌ Error running Semgrep: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Stop performance monitoring
Stop-PerformanceMonitoring

# Calculate scan duration
$endTime = Get-Date
$scanDuration = $endTime - $startTime
Write-Host "⏱️  Scan completed in $([math]::Round($scanDuration.TotalSeconds, 2)) seconds" -ForegroundColor Green

# Analyze results
$stats = Analyze-Results $Output

# Generate HTML report if results exist
if ($stats -and $stats.Total -gt 0) {
    Generate-HTMLReport $Output $Report $stats
}

# Save cache if requested
if ($Cache -and $cacheKey) {
    Save-Cache $cacheKey $Output
}

# Exit with appropriate code
if ($stats -and $stats.Errors -gt 0) {
    Write-Host "`n❌ Scan completed with $($stats.Errors) critical security issues" -ForegroundColor Red
    exit 1
} else {
    Write-Host "`n✅ Scan completed successfully" -ForegroundColor Green
    exit 0
}
