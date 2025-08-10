#!/bin/bash

# Default values
CONFIG="configs/plugin-development.yaml"
PATH_TO_SCAN="."
VERSION="1.75.0"

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
        -h|--help)
            echo "Usage: $0 [OPTIONS]"
            echo "Options:"
            echo "  -c, --config CONFIG    Configuration file (default: configs/plugin-development.yaml)"
            echo "  -p, --path PATH        Path to scan (default: .)"
            echo "  --install              Install Semgrep if not found"
            echo "  -h, --help             Show this help message"
            exit 0
            ;;
        *)
            echo "Unknown option: $1"
            exit 1
            ;;
    esac
done

# Check if Semgrep is installed
if ! command -v semgrep &> /dev/null; then
    if [ "$INSTALL" = true ]; then
        echo "Installing Semgrep version $VERSION..."
        pip install "semgrep==$VERSION"
    else
        echo "Semgrep not found. Use --install flag to install automatically."
        echo "Or install manually: pip install semgrep==$VERSION"
        exit 1
    fi
fi

# Check version
CURRENT_VERSION=$(semgrep --version)
echo "Using Semgrep: $CURRENT_VERSION"

# Run Semgrep
echo "Running Semgrep with config: $CONFIG"
echo "Scanning path: $PATH_TO_SCAN"

semgrep scan --config "$CONFIG" --json --output semgrep-results.json "$PATH_TO_SCAN"

# Check for critical findings
if command -v jq &> /dev/null; then
    CRITICAL_FINDINGS=$(jq '.results[] | select(.extra.severity == "ERROR")' semgrep-results.json 2>/dev/null)
    
    if [ -n "$CRITICAL_FINDINGS" ]; then
        echo -e "\n❌ Critical security findings detected:"
        echo "$CRITICAL_FINDINGS" | jq -r '.extra.message + " in " + .path + ":" + (.start.line | tostring)'
        exit 1
    else
        echo -e "\n✅ No critical security issues found"
    fi
else
    echo "jq not found. Install jq for better result parsing."
    echo "Results saved to: semgrep-results.json"
fi

echo -e "\nResults saved to: semgrep-results.json"
