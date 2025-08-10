#!/bin/bash

# WordPress Semgrep Security Scanner v2.0.0
# Enhanced runner script with advanced features

set -euo pipefail

# Default values
CONFIG="configs/plugin-development.yaml"
PATH_TO_SCAN="."
VERSION="1.75.0"
SCRIPT_VERSION="2.0.0"
VERBOSE=false
PERFORMANCE=false
VALIDATE=false
CACHE=false
INCREMENTAL=false
OUTPUT="semgrep-results.json"
REPORT="semgrep-report.html"
TIMEOUT=300
HELP=false

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
GRAY='\033[0;37m'
NC='\033[0m' # No Color

# Start time for performance tracking
START_TIME=$(date +%s)

# Help function
show_help() {
    echo -e "${CYAN}WordPress Semgrep Security Scanner v${SCRIPT_VERSION}${NC}"
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo -e "${YELLOW}Options:${NC}"
    echo "  -c, --config CONFIG        Configuration file (default: configs/plugin-development.yaml)"
    echo "  -p, --path PATH            Path to scan (default: .)"
    echo "  --install                  Install Semgrep if not found"
    echo "  -v, --verbose              Enable verbose output"
    echo "  --performance              Enable performance monitoring"
    echo "  --validate                 Validate configuration before scanning"
    echo "  --cache                    Enable caching for repeated scans"
    echo "  --incremental              Only scan changed files (requires git)"
    echo "  -o, --output FILE          Output file for results (default: semgrep-results.json)"
    echo "  -r, --report FILE          HTML report file (default: semgrep-report.html)"
    echo "  -t, --timeout SECONDS      Scan timeout in seconds (default: 300)"
    echo "  -h, --help                 Show this help message"
    echo ""
    echo -e "${YELLOW}Available Configurations:${NC}"
    echo "  configs/basic.yaml                    - Essential security rules"
    echo "  configs/strict.yaml                   - Comprehensive security coverage"
    echo "  configs/plugin-development.yaml       - WordPress plugin development"
    echo "  configs/optimized-15s.yaml           - Fast scanning (< 15s)"
    echo "  configs/optimized-30s.yaml           - Balanced scanning (< 30s)"
    echo "  configs/performance-optimized.yaml   - Performance-focused rules"
    echo ""
    echo -e "${YELLOW}Examples:${NC}"
    echo "  $0 -c configs/strict.yaml -v"
    echo "  $0 -p src/ --performance --cache"
    echo "  $0 --incremental -o results.json"
}

# Logging functions
log_info() {
    echo -e "${GREEN}ℹ️  $1${NC}"
}

log_warn() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_debug() {
    if [ "$VERBOSE" = true ]; then
        echo -e "${GRAY}🔍 $1${NC}"
    fi
}

# Configuration validation function
validate_config() {
    local config_path="$1"
    
    if [ ! -f "$config_path" ]; then
        log_error "Configuration file not found: $config_path"
        return 1
    fi
    
    if grep -q "rules:" "$config_path"; then
        log_success "Configuration file is valid"
        return 0
    else
        log_error "Invalid configuration format"
        return 1
    fi
}

# Performance monitoring function
start_performance_monitoring() {
    if [ "$PERFORMANCE" = true ]; then
        log_info "Performance monitoring enabled"
        # Start background monitoring
        (
            while true; do
                if pgrep -x "semgrep" > /dev/null; then
                    local memory=$(ps -o rss= -p $(pgrep -x "semgrep") 2>/dev/null | awk '{print $1/1024}')
                    local cpu=$(ps -o %cpu= -p $(pgrep -x "semgrep") 2>/dev/null)
                    echo "$(date '+%H:%M:%S') - Memory: ${memory}MB, CPU: ${cpu}%" >> /tmp/semgrep_performance.log
                fi
                sleep 5
            done
        ) &
        PERFORMANCE_PID=$!
    fi
}

stop_performance_monitoring() {
    if [ -n "${PERFORMANCE_PID:-}" ]; then
        kill $PERFORMANCE_PID 2>/dev/null || true
        if [ -f /tmp/semgrep_performance.log ]; then
            log_info "Performance log saved to /tmp/semgrep_performance.log"
        fi
    fi
}

# Cache management functions
get_cache_key() {
    local config_path="$1"
    local scan_path="$2"
    local config_hash=$(sha256sum "$config_path" | cut -d' ' -f1 | cut -c1-8)
    local path_hash=$(echo "$scan_path" | sha256sum | cut -d' ' -f1 | cut -c1-8)
    echo "semgrep_cache_${config_hash}_${path_hash}.json"
}

test_cache() {
    local cache_key="$1"
    local cache_file="/tmp/$cache_key"
    
    if [ -f "$cache_file" ]; then
        local cache_age=$(( $(date +%s) - $(stat -c %Y "$cache_file") ))
        local cache_age_minutes=$((cache_age / 60))
        
        if [ $cache_age_minutes -lt 1440 ]; then # 24 hours
            log_warn "Using cached results (age: ${cache_age_minutes} minutes)"
            return 0
        fi
    fi
    return 1
}

save_cache() {
    local cache_key="$1"
    local results_file="$2"
    local cache_file="/tmp/$cache_key"
    cp "$results_file" "$cache_file"
    log_success "Results cached for future scans"
}

# Enhanced incremental scanning function
get_changed_files() {
    if [ ! -d ".git" ]; then
        log_warn "Git repository not found, performing full scan"
        echo "$PATH_TO_SCAN"
        return
    fi
    
    if command -v git >/dev/null 2>&1; then
        # Use Python incremental scanner if available
        if command -v python3 >/dev/null 2>&1; then
            local python_script="$(dirname "$0")/incremental_scanner.py"
            if [ -f "$python_script" ]; then
                log_info "Using enhanced incremental scanning..."
                
                local result=$(python3 "$python_script" "$CONFIG_FILE" "$PATH_TO_SCAN" 2>/dev/null)
                if [ $? -eq 0 ]; then
                    local scan_type=$(echo "$result" | grep "Scan Type:" | cut -d: -f2 | tr -d ' ')
                    local changed_files=$(echo "$result" | grep "Changed Files:" | cut -d: -f2 | tr -d ' ')
                    local affected_files=$(echo "$result" | grep "Affected Files:" | cut -d: -f2 | tr -d ' ')
                    local scan_paths=$(echo "$result" | grep "Scan Paths:" | cut -d: -f2 | tr -d ' ')
                    
                    echo ""
                    echo -e "${CYAN}📊 Scan Analysis:${NC}"
                    echo "  Scan Type: $scan_type"
                    echo "  Changed Files: $changed_files"
                    echo "  Affected Files: $affected_files"
                    echo "  Scan Paths: $scan_paths"
                    
                    if [ -n "$scan_paths" ] && [ "$scan_paths" -gt 0 ]; then
                        return  # Use full path for now, could be enhanced to use specific paths
                    fi
                fi
            fi
        fi
        
        # Fallback to basic git-based detection
        log_info "Using basic git-based change detection..."
        local changed_files=$(git diff --name-only HEAD~1 2>/dev/null || true)
        if [ -n "$changed_files" ]; then
            local php_files=$(echo "$changed_files" | grep '\.php$' || true)
            if [ -n "$php_files" ]; then
                local count=$(echo "$php_files" | wc -l)
                log_info "Scanning $count changed PHP files"
                echo "$php_files"
                return
            fi
        fi
        
        # Check for untracked files
        local untracked_files=$(git ls-files --others --exclude-standard 2>/dev/null || true)
        if [ -n "$untracked_files" ]; then
            local php_untracked=$(echo "$untracked_files" | grep '\.php$' || true)
            if [ -n "$php_untracked" ]; then
                local count=$(echo "$php_untracked" | wc -l)
                log_info "Found $count untracked PHP files"
                echo "$php_untracked"
                return
            fi
        fi
    fi
    
    log_warn "No changed files detected or error getting changes, performing full scan"
    echo "$PATH_TO_SCAN"
}

# Result analysis function
analyze_results() {
    local results_file="$1"
    
    if [ ! -f "$results_file" ]; then
        log_error "Results file not found: $results_file"
        return
    fi
    
    if ! command -v jq >/dev/null 2>&1; then
        log_warn "jq not found. Install jq for better result parsing."
        return
    fi
    
    local total_findings=$(jq '.results | length' "$results_file" 2>/dev/null || echo "0")
    local error_findings=$(jq '.results | map(select(.extra.severity == "ERROR")) | length' "$results_file" 2>/dev/null || echo "0")
    local warning_findings=$(jq '.results | map(select(.extra.severity == "WARNING")) | length' "$results_file" 2>/dev/null || echo "0")
    local info_findings=$(jq '.results | map(select(.extra.severity == "INFO")) | length' "$results_file" 2>/dev/null || echo "0")
    
    echo ""
    echo -e "${CYAN}📊 Scan Results Summary:${NC}"
    echo "  Total Findings: $total_findings"
    echo -e "  Errors: ${RED}$error_findings${NC}"
    echo -e "  Warnings: ${YELLOW}$warning_findings${NC}"
    echo -e "  Info: ${BLUE}$info_findings${NC}"
    
    # Group by rule
    echo ""
    echo -e "${CYAN}📋 Findings by Rule:${NC}"
    jq -r '.results | group_by(.check_id) | map({rule: .[0].check_id, count: length, severity: .[0].extra.severity}) | sort_by(.count) | reverse[] | "  \(.rule): \(.count) (\(.severity))"' "$results_file" 2>/dev/null || true
    
    # Show critical findings
    if [ "$error_findings" -gt 0 ]; then
        echo ""
        echo -e "${RED}❌ Critical Security Issues:${NC}"
        jq -r '.results | map(select(.extra.severity == "ERROR")) | .[0:10][] | "  • \(.extra.message)\n    File: \(.path):\(.start.line)"' "$results_file" 2>/dev/null || true
        
        if [ "$error_findings" -gt 10 ]; then
            local remaining=$((error_findings - 10))
            echo -e "    ... and ${RED}$remaining${NC} more critical issues"
        fi
    fi
    
    # Export stats for other functions
    echo "$total_findings:$error_findings:$warning_findings:$info_findings"
}

# Generate HTML report
generate_html_report() {
    local results_file="$1"
    local report_file="$2"
    local stats="$3"
    
    if ! command -v jq >/dev/null 2>&1; then
        log_warn "jq not found. Skipping HTML report generation."
        return
    fi
    
    local total=$(echo "$stats" | cut -d: -f1)
    local errors=$(echo "$stats" | cut -d: -f2)
    local warnings=$(echo "$stats" | cut -d: -f3)
    local info=$(echo "$stats" | cut -d: -f4)
    local scan_duration=$(( $(date +%s) - START_TIME ))
    
    cat > "$report_file" << EOF
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
        <p>Generated: $(date '+%Y-%m-%d %H:%M:%S')</p>
        <p>Scan Duration: ${scan_duration} seconds</p>
    </div>
    
    <div class="summary">
        <h2>Summary</h2>
        <p><strong>Total Findings:</strong> $total</p>
        <p><strong>Errors:</strong> $errors</p>
        <p><strong>Warnings:</strong> $warnings</p>
        <p><strong>Info:</strong> $info</p>
    </div>
    
    <h2>Findings</h2>
EOF
    
    # Add findings to HTML
    jq -r '.results[] | @html "    <div class=\"finding \(.extra.severity | ascii_downcase)\">\n        <h3>\(.check_id)</h3>\n        <p><strong>Severity:</strong> \(.extra.severity)</p>\n        <p><strong>Message:</strong> \(.extra.message)</p>\n        <p><strong>File:</strong> <span class=\"file\">\(.path):\(.start.line)</span></p>\n        \(if .extra.fix then "<div class=\"fix\"><strong>Suggested Fix:</strong><br><code>\(.extra.fix)</code></div>" else "" end)\n    </div>"' "$results_file" >> "$report_file" 2>/dev/null || true
    
    cat >> "$report_file" << EOF
</body>
</html>
EOF
    
    log_success "HTML report generated: $report_file"
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -c|--config)
            CONFIG="$2"
            shift 2
            ;;
        -p|--path)
            PATH_TO_SCAN="$2"
            shift 2
            ;;
        --install)
            INSTALL=true
            shift
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        --performance)
            PERFORMANCE=true
            shift
            ;;
        --validate)
            VALIDATE=true
            shift
            ;;
        --cache)
            CACHE=true
            shift
            ;;
        --incremental)
            INCREMENTAL=true
            shift
            ;;
        -o|--output)
            OUTPUT="$2"
            shift 2
            ;;
        -r|--report)
            REPORT="$2"
            shift 2
            ;;
        -t|--timeout)
            TIMEOUT="$2"
            shift 2
            ;;
        -h|--help)
            HELP=true
            shift
            ;;
        *)
            log_error "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

# Show help if requested
if [ "$HELP" = true ]; then
    show_help
    exit 0
fi

# Main execution
echo -e "${CYAN}🔍 WordPress Semgrep Security Scanner v${SCRIPT_VERSION}${NC}"
echo "Configuration: $CONFIG"
echo "Scan Path: $PATH_TO_SCAN"

# Validate configuration if requested
if [ "$VALIDATE" = true ]; then
    if ! validate_config "$CONFIG"; then
        exit 1
    fi
fi

# Check if Semgrep is installed
if ! command -v semgrep &> /dev/null; then
    if [ "${INSTALL:-false}" = true ]; then
        log_info "Installing Semgrep version $VERSION..."
        if pip install "semgrep==$VERSION"; then
            log_success "Semgrep installed successfully"
        else
            log_error "Failed to install Semgrep"
            exit 1
        fi
    else
        log_error "Semgrep not found. Use --install flag to install automatically."
        echo "Or install manually: pip install semgrep==$VERSION"
        exit 1
    fi
fi

# Check version
CURRENT_VERSION=$(semgrep --version)
log_success "Using Semgrep: $CURRENT_VERSION"

# Handle incremental scanning
SCAN_PATH="$PATH_TO_SCAN"
if [ "$INCREMENTAL" = true ]; then
    SCAN_PATH=$(get_changed_files)
fi

# Handle caching
CACHE_KEY=""
if [ "$CACHE" = true ]; then
    CACHE_KEY=$(get_cache_key "$CONFIG" "$SCAN_PATH")
    if test_cache "$CACHE_KEY"; then
        cp "/tmp/$CACHE_KEY" "$OUTPUT"
        log_success "Using cached results"
        exit 0
    fi
fi

# Start performance monitoring
start_performance_monitoring

# Run Semgrep with timeout
log_info "Starting security scan..."
log_debug "Command: semgrep scan --config $CONFIG --json --output $OUTPUT $SCAN_PATH"

# Run semgrep with timeout
if timeout "$TIMEOUT" semgrep scan --config "$CONFIG" --json --output "$OUTPUT" $SCAN_PATH; then
    log_success "Scan completed successfully"
else
    local exit_code=$?
    if [ $exit_code -eq 124 ]; then
        log_error "Scan timed out after $TIMEOUT seconds"
    else
        log_error "Scan failed with exit code $exit_code"
    fi
    stop_performance_monitoring
    exit $exit_code
fi

# Stop performance monitoring
stop_performance_monitoring

# Calculate scan duration
END_TIME=$(date +%s)
SCAN_DURATION=$((END_TIME - START_TIME))
log_success "Scan completed in ${SCAN_DURATION} seconds"

# Analyze results
STATS=$(analyze_results "$OUTPUT")

# Generate HTML report if results exist
if [ -n "$STATS" ]; then
    local total=$(echo "$STATS" | cut -d: -f1)
    if [ "$total" -gt 0 ]; then
        generate_html_report "$OUTPUT" "$REPORT" "$STATS"
    fi
fi

# Save cache if requested
if [ "$CACHE" = true ] && [ -n "$CACHE_KEY" ]; then
    save_cache "$CACHE_KEY" "$OUTPUT"
fi

# Exit with appropriate code
if [ -n "$STATS" ]; then
    local errors=$(echo "$STATS" | cut -d: -f2)
    if [ "$errors" -gt 0 ]; then
        echo ""
        log_error "Scan completed with $errors critical security issues"
        exit 1
    else
        echo ""
        log_success "Scan completed successfully"
        exit 0
    fi
else
    echo ""
    log_success "Scan completed successfully"
    exit 0
fi
