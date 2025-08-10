#!/bin/bash
# WordPress Semgrep Rules - Comprehensive Performance Testing Runner
#
# This script runs comprehensive performance testing for WordPress Semgrep rules,
# including scan time analysis, memory usage monitoring, and optimization recommendations.
#
# Usage: ./run-comprehensive-performance-test.sh [options]
#
# Options:
#   --config <file>     Custom test configuration file
#   --rules <path>      Path to rules directory
#   --tests <path>      Path to test files directory
#   --output <path>     Output directory for test results
#   --iterations <int>  Number of iterations per test (default: 10)
#   --warmup <int>      Number of warmup runs (default: 3)
#   --verbose           Enable verbose output
#   --json              Output results in JSON format
#   --html              Generate HTML report
#   --optimize          Run optimization analysis
#   --baseline          Establish performance baseline
#   --compare           Compare against baseline
#   --visualize         Generate performance visualizations
#   --help              Show this help message
#
# Examples:
#   ./run-comprehensive-performance-test.sh --iterations 15 --visualize
#   ./run-comprehensive-performance-test.sh --config custom-config.json --output ./results
#
# Requirements:
#   - Python 3.7+
#   - psutil, matplotlib, numpy, pyyaml packages
#   - Semgrep

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
GRAY='\033[0;37m'
NC='\033[0m' # No Color

# Script configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
PYTHON_SCRIPT="$SCRIPT_DIR/comprehensive-performance-test.py"

# Default values
RULES_PATH="$PROJECT_ROOT/packs"
TESTS_PATH="$SCRIPT_DIR"
OUTPUT_PATH="$SCRIPT_DIR/performance-results"
ITERATIONS=10
WARMUP=3
VERBOSE=false
JSON=false
HTML=false
OPTIMIZE=false
BASELINE=false
COMPARE=false
VISUALIZE=false

# Function to print colored output
print_status() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Function to show help
show_help() {
    cat << EOF
WordPress Semgrep Rules - Comprehensive Performance Testing

Usage: $0 [options]

Options:
  --config <file>     Custom test configuration file
  --rules <path>      Path to rules directory
  --tests <path>      Path to test files directory
  --output <path>     Output directory for test results
  --iterations <int>  Number of iterations per test (default: 10)
  --warmup <int>      Number of warmup runs (default: 3)
  --verbose           Enable verbose output
  --json              Output results in JSON format
  --html              Generate HTML report
  --optimize          Run optimization analysis
  --baseline          Establish performance baseline
  --compare           Compare against baseline
  --visualize         Generate performance visualizations
  --help              Show this help message

Examples:
  $0 --iterations 15 --visualize
  $0 --config custom-config.json --output ./results

Requirements:
  - Python 3.7+
  - psutil, matplotlib, numpy, pyyaml packages
  - Semgrep
EOF
}

# Function to check Python availability
check_python() {
    if command -v python3 &> /dev/null; then
        PYTHON_CMD="python3"
        PYTHON_VERSION=$(python3 --version 2>&1)
        print_status "$GREEN" "Python found: $PYTHON_VERSION"
        return 0
    elif command -v python &> /dev/null; then
        PYTHON_CMD="python"
        PYTHON_VERSION=$(python --version 2>&1)
        print_status "$GREEN" "Python found: $PYTHON_VERSION"
        return 0
    else
        print_status "$RED" "Python is required but not found. Please install Python 3.7+ and add it to PATH."
        return 1
    fi
}

# Function to check Python packages
check_python_packages() {
    local required_packages=("psutil" "matplotlib" "numpy" "yaml")
    local missing_packages=()
    
    for package in "${required_packages[@]}"; do
        if ! $PYTHON_CMD -c "import $package" 2>/dev/null; then
            missing_packages+=("$package")
        fi
    done
    
    if [ ${#missing_packages[@]} -gt 0 ]; then
        print_status "$YELLOW" "Missing Python packages: ${missing_packages[*]}"
        print_status "$YELLOW" "Installing missing packages..."
        
        for package in "${missing_packages[@]}"; do
            print_status "$YELLOW" "Installing $package..."
            if ! pip install "$package"; then
                print_status "$RED" "Failed to install $package"
                return 1
            fi
        done
    fi
    
    print_status "$GREEN" "All required Python packages are available"
    return 0
}

# Function to check Semgrep availability
check_semgrep() {
    if command -v semgrep &> /dev/null; then
        SEMGREP_VERSION=$(semgrep --version 2>&1)
        print_status "$GREEN" "Semgrep found: $SEMGREP_VERSION"
        return 0
    else
        print_status "$RED" "Semgrep is required but not found. Please install Semgrep and add it to PATH."
        return 1
    fi
}

# Function to create output directory
create_output_directory() {
    if [ ! -d "$OUTPUT_PATH" ]; then
        mkdir -p "$OUTPUT_PATH"
        print_status "$GREEN" "Created output directory: $OUTPUT_PATH"
    fi
}

# Function to run performance test
run_performance_test() {
    local args=()
    
    [ -n "$CONFIG" ] && args+=("--config" "$CONFIG")
    [ -n "$RULES_PATH" ] && args+=("--rules" "$RULES_PATH")
    [ -n "$TESTS_PATH" ] && args+=("--tests" "$TESTS_PATH")
    [ -n "$OUTPUT_PATH" ] && args+=("--output" "$OUTPUT_PATH")
    [ -n "$ITERATIONS" ] && args+=("--iterations" "$ITERATIONS")
    [ -n "$WARMUP" ] && args+=("--warmup" "$WARMUP")
    [ "$VERBOSE" = true ] && args+=("--verbose")
    [ "$JSON" = true ] && args+=("--json")
    [ "$HTML" = true ] && args+=("--html")
    [ "$OPTIMIZE" = true ] && args+=("--optimize")
    [ "$BASELINE" = true ] && args+=("--baseline")
    [ "$COMPARE" = true ] && args+=("--compare")
    [ "$VISUALIZE" = true ] && args+=("--visualize")
    
    print_status "$CYAN" "Running comprehensive performance test..."
    print_status "$GRAY" "Command: $PYTHON_CMD $PYTHON_SCRIPT ${args[*]}"
    
    local start_time=$(date +%s)
    
    if $PYTHON_CMD "$PYTHON_SCRIPT" "${args[@]}"; then
        local end_time=$(date +%s)
        local duration=$((end_time - start_time))
        print_status "$GREEN" "Performance test completed successfully in ${duration}s"
        return 0
    else
        local exit_code=$?
        print_status "$RED" "Performance test failed with exit code $exit_code"
        return $exit_code
    fi
}

# Function to run optimization analysis
run_optimization_analysis() {
    if [ "$OPTIMIZE" != true ]; then
        return 0
    fi
    
    print_status "$CYAN" "Running optimization analysis..."
    
    local performance_report="$OUTPUT_PATH/comprehensive-performance-report.json"
    
    if [ ! -f "$performance_report" ]; then
        print_status "$RED" "Performance report not found: $performance_report"
        return 1
    fi
    
    local optimizer_script="$PROJECT_ROOT/tooling/performance-optimizer.py"
    
    if [ ! -f "$optimizer_script" ]; then
        print_status "$RED" "Optimizer script not found: $optimizer_script"
        return 1
    fi
    
    local optimizer_args=(
        "--project-root" "$PROJECT_ROOT"
        "--performance-report" "$performance_report"
        "--output" "$OUTPUT_PATH/optimization-report.json"
    )
    
    if $PYTHON_CMD "$optimizer_script" "${optimizer_args[@]}"; then
        print_status "$GREEN" "Optimization analysis completed successfully"
        return 0
    else
        local exit_code=$?
        print_status "$RED" "Optimization analysis failed with exit code $exit_code"
        return $exit_code
    fi
}

# Function to display results summary
show_results_summary() {
    local results_file="$OUTPUT_PATH/comprehensive-performance-report.json"
    
    if [ -f "$results_file" ]; then
        echo
        print_status "$CYAN" "============================================================"
        print_status "$CYAN" "PERFORMANCE TEST RESULTS SUMMARY"
        print_status "$CYAN" "============================================================"
        
        if command -v jq &> /dev/null; then
            # Use jq for JSON parsing if available
            local total_tests=$(jq -r '.total_tests' "$results_file")
            local successful_tests=$(jq -r '.successful_tests' "$results_file")
            local failed_tests=$(jq -r '.failed_tests' "$results_file")
            local duration=$(jq -r '.duration' "$results_file")
            
            print_status "$WHITE" "Total tests run: $total_tests"
            print_status "$GREEN" "Successful tests: $successful_tests"
            print_status "$RED" "Failed tests: $failed_tests"
            
            if [ "$total_tests" -gt 0 ]; then
                local success_rate=$(echo "scale=1; $successful_tests * 100 / $total_tests" | bc -l 2>/dev/null || echo "0")
                print_status "$WHITE" "Success rate: ${success_rate}%"
            fi
            
            print_status "$WHITE" "Total duration: ${duration}s"
            
            # Show top performing configurations
            local fastest_configs=$(jq -r '.performance_rankings.fastest_configs[]?' "$results_file" | head -3)
            if [ -n "$fastest_configs" ]; then
                echo
                print_status "$YELLOW" "Top performing configurations:"
                local i=1
                while IFS= read -r config; do
                    print_status "$WHITE" "  $i. $config"
                    ((i++))
                done <<< "$fastest_configs"
            fi
            
            # Show optimization recommendations
            local recommendations=$(jq -r '.optimization_recommendations[]?' "$results_file" | head -3)
            if [ -n "$recommendations" ]; then
                echo
                print_status "$YELLOW" "Optimization recommendations:"
                local i=1
                while IFS= read -r rec; do
                    print_status "$WHITE" "  $i. $rec"
                    ((i++))
                done <<< "$recommendations"
            fi
        else
            print_status "$YELLOW" "Results file found but jq not available for parsing"
            print_status "$WHITE" "Results file: $results_file"
        fi
    fi
    
    # Show output files
    echo
    print_status "$YELLOW" "Output files:"
    if [ -d "$OUTPUT_PATH" ]; then
        for file in "$OUTPUT_PATH"/*; do
            if [ -f "$file" ]; then
                print_status "$WHITE" "  $(basename "$file")"
            fi
        done
    fi
}

# Function to parse command line arguments
parse_arguments() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --config)
                CONFIG="$2"
                shift 2
                ;;
            --rules)
                RULES_PATH="$2"
                shift 2
                ;;
            --tests)
                TESTS_PATH="$2"
                shift 2
                ;;
            --output)
                OUTPUT_PATH="$2"
                shift 2
                ;;
            --iterations)
                ITERATIONS="$2"
                shift 2
                ;;
            --warmup)
                WARMUP="$2"
                shift 2
                ;;
            --verbose)
                VERBOSE=true
                shift
                ;;
            --json)
                JSON=true
                shift
                ;;
            --html)
                HTML=true
                shift
                ;;
            --optimize)
                OPTIMIZE=true
                shift
                ;;
            --baseline)
                BASELINE=true
                shift
                ;;
            --compare)
                COMPARE=true
                shift
                ;;
            --visualize)
                VISUALIZE=true
                shift
                ;;
            --help|-h)
                show_help
                exit 0
                ;;
            *)
                print_status "$RED" "Unknown option: $1"
                show_help
                exit 1
                ;;
        esac
    done
}

# Main function
main() {
    print_status "$CYAN" "WordPress Semgrep Rules - Comprehensive Performance Testing"
    print_status "$CYAN" "============================================================"
    
    # Parse command line arguments
    parse_arguments "$@"
    
    # Check prerequisites
    echo
    print_status "$YELLOW" "Checking prerequisites..."
    
    if ! check_python; then
        exit 1
    fi
    
    if ! check_python_packages; then
        exit 1
    fi
    
    if ! check_semgrep; then
        exit 1
    fi
    
    # Check if Python script exists
    if [ ! -f "$PYTHON_SCRIPT" ]; then
        print_status "$RED" "Performance test script not found: $PYTHON_SCRIPT"
        exit 1
    fi
    
    # Create output directory
    create_output_directory
    
    # Run performance test
    if ! run_performance_test; then
        exit 1
    fi
    
    # Run optimization analysis if requested
    if [ "$OPTIMIZE" = true ]; then
        if ! run_optimization_analysis; then
            print_status "$YELLOW" "Warning: Optimization analysis failed, but performance test completed successfully"
        fi
    fi
    
    # Display results summary
    show_results_summary
    
    echo
    print_status "$GREEN" "Performance testing completed!"
    exit 0
}

# Run main function with all arguments
main "$@"
