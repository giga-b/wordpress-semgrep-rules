#!/bin/bash
# WordPress Semgrep Rules - Auto-fix Runner Script
#
# This script provides an easy-to-use interface for applying automatic fixes
# to WordPress security issues detected by Semgrep rules.
#
# Usage: ./run-auto-fix.sh [OPTIONS]
#
# Required Parameters:
#   --results <file>        Path to Semgrep results JSON file
#
# Optional Parameters:
#   --backup                Create backups before applying fixes
#   --dry-run               Show what would be fixed without applying changes
#   --output <file>         Output report file (default: auto-fix-report.json)
#   --config <file>         Path to auto-fix configuration file
#   --verbose               Enable verbose output
#   --install-deps          Install required Python dependencies
#   --open-report           Automatically open the generated report
#   --help                  Show this help message
#
# Examples:
#   ./run-auto-fix.sh --results semgrep-results.json --backup
#   ./run-auto-fix.sh --results semgrep-results.json --dry-run --verbose
#   ./run-auto-fix.sh --results semgrep-results.json --install-deps --backup

set -e

SCRIPT_VERSION="1.0.0"
START_TIME=$(date '+%Y-%m-%d %H:%M:%S')

# Color functions
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

print_color() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

print_success() { print_color $GREEN "$1"; }
print_warning() { print_color $YELLOW "$1"; }
print_error() { print_color $RED "$1"; }
print_info() { print_color $CYAN "$1"; }

# Help function
show_help() {
    echo "WordPress Semgrep Rules - Auto-fix System v$SCRIPT_VERSION"
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Required Parameters:"
    echo "  --results <file>        Path to Semgrep results JSON file"
    echo ""
    echo "Optional Parameters:"
    echo "  --backup                Create backups before applying fixes"
    echo "  --dry-run               Show what would be fixed without applying changes"
    echo "  --output <file>         Output report file (default: auto-fix-report.json)"
    echo "  --config <file>         Path to auto-fix configuration file"
    echo "  --verbose               Enable verbose output"
    echo "  --install-deps          Install required Python dependencies"
    echo "  --open-report           Automatically open the generated report"
    echo "  --help                  Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 --results semgrep-results.json --backup"
    echo "  $0 --results semgrep-results.json --dry-run --verbose"
    echo "  $0 --results semgrep-results.json --install-deps --backup"
}

# Parse command line arguments
RESULTS_FILE=""
BACKUP=false
DRY_RUN=false
OUTPUT_FILE="auto-fix-report.json"
CONFIG_FILE=""
VERBOSE=false
INSTALL_DEPS=false
OPEN_REPORT=false

while [[ $# -gt 0 ]]; do
    case $1 in
        --results)
            RESULTS_FILE="$2"
            shift 2
            ;;
        --backup)
            BACKUP=true
            shift
            ;;
        --dry-run)
            DRY_RUN=true
            shift
            ;;
        --output)
            OUTPUT_FILE="$2"
            shift 2
            ;;
        --config)
            CONFIG_FILE="$2"
            shift 2
            ;;
        --verbose)
            VERBOSE=true
            shift
            ;;
        --install-deps)
            INSTALL_DEPS=true
            shift
            ;;
        --open-report)
            OPEN_REPORT=true
            shift
            ;;
        --help)
            show_help
            exit 0
            ;;
        *)
            print_error "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

# Check if results file is provided
if [[ -z "$RESULTS_FILE" ]]; then
    print_error "Results file is required. Use --results <file>"
    show_help
    exit 1
fi

# Check if virtual environment exists and activate it
check_virtual_environment() {
    if [[ -d ".venv" ]]; then
        print_info "Found virtual environment, activating..."
        source .venv/bin/activate
        return 0
    else
        print_warning "Virtual environment not found. Creating one..."
        python3 -m venv .venv
        source .venv/bin/activate
        return 0
    fi
}

# Check Python dependencies
check_python_dependencies() {
    local required_packages=("yaml" "argparse" "json" "re" "os" "sys" "pathlib" "typing" "dataclasses" "datetime" "logging")
    local missing_packages=()
    
    for package in "${required_packages[@]}"; do
        if ! python3 -c "import $package" 2>/dev/null; then
            missing_packages+=("$package")
        fi
    done
    
    echo "${missing_packages[@]}"
}

# Install Python dependencies
install_python_dependencies() {
    print_info "Installing Python dependencies..."
    
    local packages=("pyyaml" "matplotlib" "seaborn" "pandas" "jinja2")
    
    for package in "${packages[@]}"; do
        print_info "Installing $package..."
        if ! pip install "$package"; then
            print_error "Failed to install $package"
            return 1
        fi
    done
    
    print_success "All dependencies installed successfully"
    return 0
}

# Validate configuration
validate_configuration() {
    local config_path="$1"
    
    if [[ -z "$config_path" ]]; then
        config_path="tooling/auto-fix-config.yaml"
    fi
    
    if [[ ! -f "$config_path" ]]; then
        print_warning "Configuration file not found: $config_path"
        return 1
    fi
    
    if grep -q "settings:" "$config_path"; then
        print_success "Configuration file is valid"
        return 0
    else
        print_error "Invalid configuration format"
        return 1
    fi
}

# Check data sources
check_data_sources() {
    local results_file="$1"
    
    if [[ ! -f "$results_file" ]]; then
        print_error "Results file not found: $results_file"
        return 1
    fi
    
    if python3 -c "import json; data=json.load(open('$results_file')); print(len(data.get('results', [])))" 2>/dev/null; then
        local count=$(python3 -c "import json; data=json.load(open('$results_file')); print(len(data.get('results', [])))" 2>/dev/null)
        print_success "Found $count issues in results file"
        return 0
    else
        print_warning "No results found in file or invalid JSON"
        return 1
    fi
}

# Main execution function
main() {
    print_info "WordPress Semgrep Rules - Auto-fix System"
    print_info "============================================="
    print_info "Version: $SCRIPT_VERSION"
    print_info "Start Time: $START_TIME"
    echo ""
    
    # Check and activate virtual environment
    if ! check_virtual_environment; then
        print_error "Failed to set up virtual environment"
        exit 1
    fi
    
    # Check dependencies
    local missing_deps=($(check_python_dependencies))
    if [[ ${#missing_deps[@]} -gt 0 ]]; then
        if [[ "$INSTALL_DEPS" == true ]]; then
            if ! install_python_dependencies; then
                print_error "Failed to install dependencies"
                exit 1
            fi
        else
            print_error "Missing Python dependencies: ${missing_deps[*]}"
            print_info "Use --install-deps to install them automatically"
            exit 1
        fi
    fi
    
    # Validate configuration
    if ! validate_configuration "$CONFIG_FILE"; then
        print_warning "Configuration validation failed, continuing with defaults"
    fi
    
    # Check data sources
    if ! check_data_sources "$RESULTS_FILE"; then
        print_error "Data source validation failed"
        exit 1
    fi
    
    # Build command arguments
    local args=("tooling/auto_fix.py" "--results" "$RESULTS_FILE" "--output" "$OUTPUT_FILE")
    
    if [[ "$BACKUP" == true ]]; then
        args+=("--backup")
    fi
    
    if [[ "$DRY_RUN" == true ]]; then
        args+=("--dry-run")
    fi
    
    if [[ -n "$CONFIG_FILE" ]]; then
        args+=("--config" "$CONFIG_FILE")
    fi
    
    if [[ "$VERBOSE" == true ]]; then
        args+=("--verbose")
    fi
    
    # Execute auto-fix
    print_info "Starting auto-fix process..."
    print_info "Command: python3 ${args[*]}"
    
    if python3 "${args[@]}"; then
        print_success "Auto-fix completed successfully"
        
        # Check if report was generated
        if [[ -f "$OUTPUT_FILE" ]]; then
            print_info "Report generated: $OUTPUT_FILE"
            
            if [[ "$OPEN_REPORT" == true ]]; then
                print_info "Opening report..."
                if [[ "$OUTPUT_FILE" == *.html ]]; then
                    if command -v xdg-open >/dev/null 2>&1; then
                        xdg-open "$OUTPUT_FILE"
                    elif command -v open >/dev/null 2>&1; then
                        open "$OUTPUT_FILE"
                    else
                        print_warning "Could not open report automatically"
                    fi
                else
                    if command -v less >/dev/null 2>&1; then
                        less "$OUTPUT_FILE"
                    elif command -v cat >/dev/null 2>&1; then
                        cat "$OUTPUT_FILE"
                    fi
                fi
            fi
        fi
    else
        print_error "Auto-fix failed"
        exit 1
    fi
    
    local end_time=$(date '+%Y-%m-%d %H:%M:%S')
    local duration=$(($(date +%s) - $(date -d "$START_TIME" +%s)))
    
    echo ""
    print_info "Auto-fix Summary"
    print_info "================"
    print_info "Start Time: $START_TIME"
    print_info "End Time: $end_time"
    print_info "Duration: ${duration} seconds"
    print_info "Results File: $RESULTS_FILE"
    print_info "Report File: $OUTPUT_FILE"
    
    if [[ "$BACKUP" == true ]]; then
        print_success "Backups: Created"
    fi
    
    if [[ "$DRY_RUN" == true ]]; then
        print_warning "Mode: Dry Run (no changes applied)"
    else
        print_success "Mode: Live Fix (changes applied)"
    fi
}

# Error handling
trap 'print_error "An error occurred on line $LINENO"; exit 1' ERR

# Main execution
main "$@"
