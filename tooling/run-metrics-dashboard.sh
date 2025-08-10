#!/bin/bash
# WordPress Semgrep Rules - Metrics Dashboard Runner
#
# This script runs the metrics dashboard for tracking rule performance and false positive rates.
# It handles setup, data collection, dashboard generation, and web server hosting.
#
# Usage:
#   ./run-metrics-dashboard.sh [options]
#
# Options:
#   --collect-metrics     Collect metrics from recent scans
#   --generate-dashboard  Generate HTML dashboard
#   --serve-dashboard     Serve dashboard on local web server
#   --port PORT          Port for web server (default: 8080)
#   --config FILE        Configuration file path (default: metrics-config.yaml)
#   --output DIR         Output directory for dashboard files (default: dashboard)
#   --update-interval N  Update interval in seconds (default: 300)
#   --install-deps       Install required Python dependencies
#   --open-browser       Automatically open browser when serving dashboard
#   --help               Show this help message
#
# Examples:
#   ./run-metrics-dashboard.sh --install-deps --collect-metrics --generate-dashboard
#   ./run-metrics-dashboard.sh --serve-dashboard --port 9000
#   ./run-metrics-dashboard.sh --collect-metrics --generate-dashboard --serve-dashboard

set -euo pipefail

# Script configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
PYTHON_SCRIPT="$SCRIPT_DIR/metrics_dashboard.py"
CONFIG_FILE="$SCRIPT_DIR/metrics-config.yaml"

# Default values
COLLECT_METRICS=false
GENERATE_DASHBOARD=false
SERVE_DASHBOARD=false
PORT=8080
CONFIG="metrics-config.yaml"
OUTPUT="dashboard"
UPDATE_INTERVAL=300
INSTALL_DEPS=false
OPEN_BROWSER=false

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to show usage
show_usage() {
    cat << EOF
WordPress Semgrep Rules - Metrics Dashboard

Usage:
    $0 [options]

Options:
    --collect-metrics     Collect metrics from recent scans
    --generate-dashboard  Generate HTML dashboard
    --serve-dashboard     Serve dashboard on local web server
    --port PORT          Port for web server (default: 8080)
    --config FILE        Configuration file path
    --output DIR         Output directory for dashboard files
    --update-interval N  Update interval in seconds
    --install-deps       Install required Python dependencies
    --open-browser       Automatically open browser when serving
    --help               Show this help message

Examples:
    $0 --install-deps --collect-metrics --generate-dashboard
    $0 --serve-dashboard --port 9000
    $0 --collect-metrics --generate-dashboard --serve-dashboard

EOF
}

# Function to check if Python is available
check_python() {
    if ! command -v python3 &> /dev/null; then
        if ! command -v python &> /dev/null; then
            print_error "Python is not installed or not in PATH"
            exit 1
        else
            PYTHON_CMD="python"
        fi
    else
        PYTHON_CMD="python3"
    fi
    print_success "Using Python: $($PYTHON_CMD --version)"
}

# Function to check Python dependencies
check_python_dependencies() {
    print_info "Testing Python dependencies..."
    
    local required_packages=("matplotlib" "seaborn" "pandas" "jinja2" "yaml")
    local missing_packages=()
    
    for package in "${required_packages[@]}"; do
        if ! $PYTHON_CMD -c "import $package" 2>/dev/null; then
            missing_packages+=("$package")
        fi
    done
    
    if [ ${#missing_packages[@]} -gt 0 ]; then
        print_warning "Missing Python packages: ${missing_packages[*]}"
        return 1
    fi
    
    print_success "All Python dependencies are installed"
    return 0
}

# Function to install Python dependencies
install_python_dependencies() {
    print_info "Installing Python dependencies..."
    
    local packages=("matplotlib" "seaborn" "pandas" "jinja2" "pyyaml")
    
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

# Function to check configuration
check_configuration() {
    print_info "Testing configuration..."
    
    if [ ! -f "$CONFIG_FILE" ]; then
        print_warning "Configuration file not found: $CONFIG_FILE"
        print_info "Using default configuration"
        return 0
    fi
    
    # Basic YAML validation
    if command -v python3 &> /dev/null; then
        if python3 -c "import yaml; yaml.safe_load(open('$CONFIG_FILE'))" 2>/dev/null; then
            print_success "Configuration file syntax is valid"
        else
            print_warning "Configuration file may have syntax issues"
        fi
    else
        print_warning "Cannot validate YAML syntax (python3 not available)"
    fi
    
    return 0
}

# Function to check data sources
check_data_sources() {
    print_info "Testing data sources..."
    
    local data_sources=(
        "tests/test-results/automated-test-report.json"
        "performance-optimization-report.json"
        "semgrep-results.json"
        "test-results.json"
    )
    
    local available_sources=0
    
    for source in "${data_sources[@]}"; do
        if [ -f "$PROJECT_ROOT/$source" ]; then
            print_success "Found data source: $source"
            ((available_sources++))
        else
            print_warning "Data source not found: $source"
        fi
    done
    
    if [ $available_sources -eq 0 ]; then
        print_warning "No data sources found. Dashboard will show empty data."
    fi
    
    return 0
}

# Function to start metrics collection
start_metrics_collection() {
    print_info "Starting metrics collection..."
    
    local args=("$PYTHON_SCRIPT" "--collect-metrics")
    
    if [ "$CONFIG" != "metrics-config.yaml" ]; then
        args+=("--config" "$CONFIG")
    fi
    
    if $PYTHON_CMD "${args[@]}"; then
        print_success "Metrics collection completed successfully"
        return 0
    else
        print_error "Metrics collection failed"
        return 1
    fi
}

# Function to generate dashboard
generate_dashboard() {
    print_info "Generating dashboard..."
    
    local args=("$PYTHON_SCRIPT" "--generate-dashboard" "--output" "$OUTPUT")
    
    if [ "$CONFIG" != "metrics-config.yaml" ]; then
        args+=("--config" "$CONFIG")
    fi
    
    if $PYTHON_CMD "${args[@]}"; then
        print_success "Dashboard generation completed successfully"
        return 0
    else
        print_error "Dashboard generation failed"
        return 1
    fi
}

# Function to start dashboard server
start_dashboard_server() {
    print_info "Starting dashboard server..."
    
    local dashboard_path="$PROJECT_ROOT/$OUTPUT"
    if [ ! -d "$dashboard_path" ]; then
        print_error "Dashboard directory not found: $dashboard_path"
        print_info "Run with --generate-dashboard first"
        return 1
    fi
    
    local args=("$PYTHON_SCRIPT" "--serve-dashboard" "--port" "$PORT" "--output" "$OUTPUT")
    
    if [ "$CONFIG" != "metrics-config.yaml" ]; then
        args+=("--config" "$CONFIG")
    fi
    
    print_info "Dashboard server starting on port $PORT..."
    print_success "Access dashboard at: http://localhost:$PORT"
    
    if [ "$OPEN_BROWSER" = true ]; then
        sleep 2
        if command -v xdg-open &> /dev/null; then
            xdg-open "http://localhost:$PORT" &
        elif command -v open &> /dev/null; then
            open "http://localhost:$PORT" &
        elif command -v sensible-browser &> /dev/null; then
            sensible-browser "http://localhost:$PORT" &
        fi
    fi
    
    if $PYTHON_CMD "${args[@]}"; then
        return 0
    else
        print_error "Dashboard server failed"
        return 1
    fi
}

# Function to parse command line arguments
parse_arguments() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --collect-metrics)
                COLLECT_METRICS=true
                shift
                ;;
            --generate-dashboard)
                GENERATE_DASHBOARD=true
                shift
                ;;
            --serve-dashboard)
                SERVE_DASHBOARD=true
                shift
                ;;
            --port)
                PORT="$2"
                shift 2
                ;;
            --config)
                CONFIG="$2"
                CONFIG_FILE="$SCRIPT_DIR/$CONFIG"
                shift 2
                ;;
            --output)
                OUTPUT="$2"
                shift 2
                ;;
            --update-interval)
                UPDATE_INTERVAL="$2"
                shift 2
                ;;
            --install-deps)
                INSTALL_DEPS=true
                shift
                ;;
            --open-browser)
                OPEN_BROWSER=true
                shift
                ;;
            --help)
                show_usage
                exit 0
                ;;
            *)
                print_error "Unknown option: $1"
                show_usage
                exit 1
                ;;
        esac
    done
}

# Main execution
main() {
    print_info "WordPress Semgrep Rules - Metrics Dashboard"
    print_info "==========================================="
    
    # Change to project root
    cd "$PROJECT_ROOT"
    
    # Parse arguments
    parse_arguments "$@"
    
    # Show usage if no parameters provided
    if [ "$COLLECT_METRICS" = false ] && [ "$GENERATE_DASHBOARD" = false ] && [ "$SERVE_DASHBOARD" = false ] && [ "$INSTALL_DEPS" = false ]; then
        show_usage
        exit 0
    fi
    
    # Check Python
    check_python
    
    # Install dependencies if requested
    if [ "$INSTALL_DEPS" = true ]; then
        if ! install_python_dependencies; then
            exit 1
        fi
    fi
    
    # Check dependencies
    if ! check_python_dependencies; then
        print_error "Missing Python dependencies. Use --install-deps to install them."
        exit 1
    fi
    
    # Check configuration
    if ! check_configuration; then
        exit 1
    fi
    
    # Check data sources
    check_data_sources
    
    # Execute requested operations
    local success=true
    
    if [ "$COLLECT_METRICS" = true ]; then
        if ! start_metrics_collection; then
            success=false
        fi
    fi
    
    if [ "$GENERATE_DASHBOARD" = true ]; then
        if ! generate_dashboard; then
            success=false
        fi
    fi
    
    if [ "$SERVE_DASHBOARD" = true ]; then
        if ! start_dashboard_server; then
            success=false
        fi
    fi
    
    if [ "$success" = true ]; then
        print_success "Metrics dashboard operations completed successfully"
    else
        print_error "Some operations failed. Check the output above for details."
        exit 1
    fi
}

# Run main function with all arguments
main "$@"
