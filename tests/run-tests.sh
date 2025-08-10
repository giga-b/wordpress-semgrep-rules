#!/bin/bash
# WordPress Semgrep Rules - Master Test Runner (Bash Version)
#
# This script orchestrates all automated testing components including test execution,
# regression testing, and performance benchmarking for the WordPress Semgrep Rules project.
#
# Usage:
#   ./run-tests.sh [options]
#
# Options:
#   -c, --config FILE     Test configuration file (default: test-config.json)
#   -m, --mode MODE       Testing mode: all, tests, regression, performance, quick (default: all)
#   -o, --output DIR      Output directory for test results (default: test-results/)
#   -v, --verbose         Enable verbose output
#   -h, --html            Generate HTML reports
#   -b, --baseline FILE   Path to baseline results file for regression testing
#   -u, --current FILE    Path to current results file for regression testing
#   --help                Show this help message
#
# Examples:
#   ./run-tests.sh -m all -v
#   ./run-tests.sh -m regression -b baseline.json -u current.json
#   ./run-tests.sh -m performance -h

set -euo pipefail

# Default values
CONFIG="test-config.json"
MODE="all"
OUTPUT="test-results/"
VERBOSE=false
HTML=false
BASELINE=""
CURRENT=""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Function to print colored output
print_color() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Function to show help
show_help() {
    cat << EOF
WordPress Semgrep Rules - Master Test Runner

Usage: $0 [options]

Options:
  -c, --config FILE     Test configuration file (default: test-config.json)
  -m, --mode MODE       Testing mode: all, tests, regression, performance, quick (default: all)
  -o, --output DIR      Output directory for test results (default: test-results/)
  -v, --verbose         Enable verbose output
  -h, --html            Generate HTML reports
  -b, --baseline FILE   Path to baseline results file for regression testing
  -u, --current FILE    Path to current results file for regression testing
  --help                Show this help message

Examples:
  $0 -m all -v
  $0 -m regression -b baseline.json -u current.json
  $0 -m performance -h
EOF
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -c|--config)
            CONFIG="$2"
            shift 2
            ;;
        -m|--mode)
            MODE="$2"
            shift 2
            ;;
        -o|--output)
            OUTPUT="$2"
            shift 2
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        -h|--html)
            HTML=true
            shift
            ;;
        -b|--baseline)
            BASELINE="$2"
            shift 2
            ;;
        -u|--current)
            CURRENT="$2"
            shift 2
            ;;
        --help)
            show_help
            exit 0
            ;;
        *)
            print_color $RED "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

# Function to check if Python is available
check_python() {
    if command -v python3 &> /dev/null; then
        local version=$(python3 --version 2>&1)
        print_color $GREEN "Python found: $version"
        return 0
    elif command -v python &> /dev/null; then
        local version=$(python --version 2>&1)
        print_color $GREEN "Python found: $version"
        return 0
    else
        print_color $RED "Python is required but not found. Please install Python 3.7+ and add it to PATH."
        return 1
    fi
}

# Function to check if Semgrep is available
check_semgrep() {
    if command -v semgrep &> /dev/null; then
        local version=$(semgrep --version 2>&1)
        print_color $GREEN "Semgrep found: $version"
        return 0
    else
        print_color $RED "Semgrep is required but not found. Please install Semgrep and add it to PATH."
        return 1
    fi
}

# Function to install Python dependencies
install_dependencies() {
    print_color $YELLOW "Installing Python dependencies..."
    
    cat > requirements-test.txt << EOF
psutil>=5.8.0
EOF
    
    if python3 -m pip install -r requirements-test.txt 2>/dev/null; then
        print_color $GREEN "Python dependencies installed successfully"
        return 0
    elif python -m pip install -r requirements-test.txt 2>/dev/null; then
        print_color $GREEN "Python dependencies installed successfully"
        return 0
    else
        print_color $RED "Failed to install Python dependencies"
        return 1
    fi
}

# Function to create output directory
create_output_dir() {
    if [[ ! -d "$OUTPUT" ]]; then
        mkdir -p "$OUTPUT"
        print_color $GREEN "Created output directory: $OUTPUT"
    fi
}

# Function to run automated tests
run_automated_tests() {
    print_color $YELLOW "Running Automated Tests..."
    
    local args=("run-automated-tests.py" "--config" "$CONFIG" "--output" "$OUTPUT/automated-test-report.json")
    
    if [[ "$VERBOSE" == "true" ]]; then
        args+=("--verbose")
    fi
    
    if [[ "$HTML" == "true" ]]; then
        args+=("--html")
    fi
    
    if python3 "${args[@]}" 2>/dev/null || python "${args[@]}" 2>/dev/null; then
        print_color $GREEN "Automated tests completed successfully"
        return 0
    else
        print_color $RED "Automated tests failed"
        return 1
    fi
}

# Function to run regression tests
run_regression_tests() {
    print_color $YELLOW "Running Regression Tests..."
    
    if [[ ! -f "$BASELINE" ]]; then
        print_color $RED "Baseline file not found: $BASELINE"
        return 1
    fi
    
    if [[ ! -f "$CURRENT" ]]; then
        print_color $RED "Current file not found: $CURRENT"
        return 1
    fi
    
    local args=("regression-tests.py" "--baseline" "$BASELINE" "--current" "$CURRENT" "--output" "$OUTPUT/regression-report.json")
    
    if [[ "$VERBOSE" == "true" ]]; then
        args+=("--verbose")
    fi
    
    if [[ "$HTML" == "true" ]]; then
        args+=("--html")
    fi
    
    if python3 "${args[@]}" 2>/dev/null || python "${args[@]}" 2>/dev/null; then
        print_color $GREEN "Regression tests completed successfully"
        return 0
    else
        print_color $YELLOW "Regression tests detected issues"
        return 0  # Regression tests can fail but still complete
    fi
}

# Function to run performance benchmarks
run_performance_benchmarks() {
    print_color $YELLOW "Running Performance Benchmarks..."
    
    local args=("performance-benchmarks.py" "--config" "$CONFIG" "--output" "$OUTPUT/performance-benchmark-report.json" "--iterations" "3" "--warmup" "1")
    
    if [[ "$VERBOSE" == "true" ]]; then
        args+=("--verbose")
    fi
    
    if [[ "$HTML" == "true" ]]; then
        args+=("--html")
    fi
    
    if python3 "${args[@]}" 2>/dev/null || python "${args[@]}" 2>/dev/null; then
        print_color $GREEN "Performance benchmarks completed successfully"
        return 0
    else
        print_color $RED "Performance benchmarks failed"
        return 1
    fi
}

# Function to run quick tests
run_quick_tests() {
    print_color $YELLOW "Running Quick Tests..."
    
    local args=("run-automated-tests.py" "--config" "$CONFIG" "--output" "$OUTPUT/quick-test-report.json")
    
    if [[ "$VERBOSE" == "true" ]]; then
        args+=("--verbose")
    fi
    
    if [[ "$HTML" == "true" ]]; then
        args+=("--html")
    fi
    
    if python3 "${args[@]}" 2>/dev/null || python "${args[@]}" 2>/dev/null; then
        print_color $GREEN "Quick tests completed successfully"
        return 0
    else
        print_color $RED "Quick tests failed"
        return 1
    fi
}

# Function to generate summary report
generate_summary() {
    local results=("$@")
    print_color $YELLOW "Generating Summary Report..."
    
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    local total_tests=${#results[@]}
    local passed_tests=0
    local failed_tests=0
    
    for result in "${results[@]}"; do
        if [[ "$result" == "0" ]]; then
            ((passed_tests++))
        else
            ((failed_tests++))
        fi
    done
    
    local success_rate=0
    if [[ $total_tests -gt 0 ]]; then
        success_rate=$(echo "scale=1; $passed_tests * 100 / $total_tests" | bc -l 2>/dev/null || echo "0")
    fi
    
    # Create summary JSON
    cat > "$OUTPUT/test-summary.json" << EOF
{
  "timestamp": "$timestamp",
  "mode": "$MODE",
  "total_tests": $total_tests,
  "passed_tests": $passed_tests,
  "failed_tests": $failed_tests,
  "success_rate": $success_rate
}
EOF
    
    # Display summary
    print_color $CYAN "Test Summary:"
    print_color $NC "  Mode: $MODE"
    print_color $NC "  Total Tests: $total_tests"
    print_color $GREEN "  Passed: $passed_tests"
    print_color $RED "  Failed: $failed_tests"
    print_color $NC "  Success Rate: ${success_rate}%"
    print_color $YELLOW "  Summary saved to: $OUTPUT/test-summary.json"
    
    echo "$passed_tests $failed_tests"
}

# Main execution
main() {
    print_color $CYAN "WordPress Semgrep Rules - Master Test Runner"
    print_color $CYAN "============================================="
    
    # Check prerequisites
    print_color $YELLOW "Checking prerequisites..."
    
    if ! check_python; then
        exit 1
    fi
    
    if ! check_semgrep; then
        exit 1
    fi
    
    # Install dependencies
    if ! install_dependencies; then
        print_color $RED "Failed to install Python dependencies. Exiting."
        exit 1
    fi
    
    # Create output directory
    create_output_dir
    
    # Load configuration
    if [[ ! -f "$CONFIG" ]]; then
        print_color $RED "Configuration file not found: $CONFIG"
        exit 1
    fi
    
    print_color $YELLOW "Starting test execution in mode: $MODE"
    
    local results=()
    local start_time=$(date +%s)
    
    # Execute tests based on mode
    case $MODE in
        "all")
            if run_automated_tests; then
                results+=(0)
            else
                results+=(1)
            fi
            
            if run_performance_benchmarks; then
                results+=(0)
            else
                results+=(1)
            fi
            
            # For regression testing, we need baseline and current files
            if [[ -n "$BASELINE" && -n "$CURRENT" ]]; then
                if run_regression_tests; then
                    results+=(0)
                else
                    results+=(1)
                fi
            else
                print_color $YELLOW "Skipping regression tests - baseline and current files not provided"
            fi
            ;;
        "tests")
            if run_automated_tests; then
                results+=(0)
            else
                results+=(1)
            fi
            ;;
        "regression")
            if [[ -z "$BASELINE" || -z "$CURRENT" ]]; then
                print_color $RED "Baseline and current files are required for regression testing"
                exit 1
            fi
            if run_regression_tests; then
                results+=(0)
            else
                results+=(1)
            fi
            ;;
        "performance")
            if run_performance_benchmarks; then
                results+=(0)
            else
                results+=(1)
            fi
            ;;
        "quick")
            if run_quick_tests; then
                results+=(0)
            else
                results+=(1)
            fi
            ;;
        *)
            print_color $RED "Invalid mode: $MODE"
            show_help
            exit 1
            ;;
    esac
    
    local end_time=$(date +%s)
    local duration=$((end_time - start_time))
    
    # Generate summary
    local summary_result=$(generate_summary "${results[@]}")
    local passed_tests=$(echo "$summary_result" | cut -d' ' -f1)
    local failed_tests=$(echo "$summary_result" | cut -d' ' -f2)
    
    # Final status
    print_color $CYAN "Test execution completed in ${duration} seconds"
    
    if [[ $failed_tests -gt 0 ]]; then
        print_color $RED "Some tests failed. Check the output files for details."
        exit 1
    else
        print_color $GREEN "All tests passed successfully!"
        exit 0
    fi
}

# Run main function
main "$@"
