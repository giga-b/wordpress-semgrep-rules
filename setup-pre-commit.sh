#!/bin/bash

# WordPress Semgrep Rules - Pre-commit Hook Setup Script
# This script sets up the pre-commit hook environment for the WordPress Semgrep Rules project

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
WHITE='\033[1;37m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${CYAN}📋 $1${NC}"
}

print_header() {
    echo -e "${WHITE}$1${NC}"
}

# Parse command line arguments
SKIP_INSTALL=false
FORCE=false
PYTHON_VERSION="python3.11"

while [[ $# -gt 0 ]]; do
    case $1 in
        --skip-install)
            SKIP_INSTALL=true
            shift
            ;;
        --force)
            FORCE=true
            shift
            ;;
        --python)
            PYTHON_VERSION="$2"
            shift 2
            ;;
        -h|--help)
            echo "Usage: $0 [OPTIONS]"
            echo "Options:"
            echo "  --skip-install    Skip pre-commit installation"
            echo "  --force           Force installation even if already installed"
            echo "  --python VERSION  Specify Python version (default: python3.11)"
            echo "  -h, --help        Show this help message"
            exit 0
            ;;
        *)
            echo "Unknown option: $1"
            exit 1
            ;;
    esac
done

print_header "🔧 Setting up Pre-commit Hook for WordPress Semgrep Rules"
echo ""

# Check if we're in the right directory
if [[ ! -f ".pre-commit-config.yaml" ]]; then
    print_error "Error: .pre-commit-config.yaml not found. Please run this script from the project root."
    exit 1
fi

# Check Python installation
print_info "Checking Python installation..."
if command -v "$PYTHON_VERSION" &> /dev/null; then
    PYTHON_VER=$("$PYTHON_VERSION" --version 2>&1)
    print_status "Found Python: $PYTHON_VER"
else
    print_error "Python not found. Please install Python 3.11 or later."
    echo "   Download from: https://www.python.org/downloads/"
    exit 1
fi

# Check pip installation
print_info "Checking pip installation..."
if "$PYTHON_VERSION" -m pip --version &> /dev/null; then
    PIP_VER=$("$PYTHON_VERSION" -m pip --version 2>&1)
    print_status "Found pip: $PIP_VER"
else
    print_error "pip not found. Please install pip."
    exit 1
fi

# Install pre-commit if not already installed
if [[ "$SKIP_INSTALL" == false ]]; then
    print_info "Installing pre-commit..."
    if "$PYTHON_VERSION" -m pip install pre-commit; then
        print_status "Pre-commit installed successfully"
    else
        print_error "Failed to install pre-commit. Please install manually:"
        echo "   pip install pre-commit"
        exit 1
    fi
fi

# Install project dependencies
print_info "Installing project dependencies..."
if "$PYTHON_VERSION" -m pip install -r requirements.txt; then
    print_status "Project dependencies installed successfully"
else
    print_error "Failed to install project dependencies."
    exit 1
fi

# Install pre-commit hooks
print_info "Installing pre-commit hooks..."
if pre-commit install; then
    print_status "Pre-commit hooks installed successfully"
else
    print_error "Failed to install pre-commit hooks."
    exit 1
fi

# Install additional dependencies for hooks
print_info "Installing hook dependencies..."
if pre-commit install-hooks; then
    print_status "Hook dependencies installed successfully"
else
    print_error "Failed to install hook dependencies."
    exit 1
fi

# Validate configuration
print_info "Validating pre-commit configuration..."
if pre-commit validate-config; then
    print_status "Pre-commit configuration is valid"
else
    print_error "Pre-commit configuration validation failed."
    exit 1
fi

# Test the hooks
print_info "Testing pre-commit hooks..."
if pre-commit run --all-files; then
    print_status "Pre-commit hooks test completed successfully"
else
    print_warning "Pre-commit hooks test completed with issues. This is normal for initial setup."
    echo "   Some hooks may fail on existing files. New commits will be checked properly."
fi

# Create .gitignore entries if needed
print_info "Updating .gitignore..."
GITIGNORE_ENTRIES="# Pre-commit hook outputs
.pre-commit-semgrep-*.json
.secrets.baseline

# Python cache
__pycache__/
*.pyc
*.pyo
*.pyd
.Python
*.so

# Virtual environments
venv/
env/
ENV/

# IDE files
.vscode/
.idea/
*.swp
*.swo
*~

# OS files
.DS_Store
Thumbs.db"

if [[ ! -f ".gitignore" ]]; then
    echo "$GITIGNORE_ENTRIES" > .gitignore
    print_status "Created .gitignore file"
else
    if ! grep -q "\.pre-commit-semgrep-.*\.json" .gitignore; then
        echo "" >> .gitignore
        echo "# Pre-commit hook outputs" >> .gitignore
        echo ".pre-commit-semgrep-*.json" >> .gitignore
        echo ".secrets.baseline" >> .gitignore
        print_status "Updated .gitignore file"
    else
        print_status ".gitignore already contains pre-commit entries"
    fi
fi

echo ""
print_header "🎉 Pre-commit hook setup completed successfully!"
echo ""
print_info "What was installed:"
echo "   • Pre-commit framework"
echo "   • Semgrep security scanning hooks"
echo "   • YAML and JSON validation hooks"
echo "   • PHP syntax checking hooks"
echo "   • File formatting and cleanup hooks"
echo "   • Secrets detection hooks"
echo "   • Custom WordPress validation hooks"
echo ""
print_info "Available commands:"
echo "   • pre-commit run --all-files     # Run all hooks on all files"
echo "   • pre-commit run                 # Run hooks on staged files"
echo "   • pre-commit run --hook-stage manual # Run hooks manually"
echo "   • pre-commit clean               # Clean hook environments"
echo "   • pre-commit uninstall          # Remove pre-commit hooks"
echo ""
print_info "Documentation:"
echo "   • Pre-commit: https://pre-commit.com/"
echo "   • Semgrep: https://semgrep.dev/docs/"
echo "   • Project docs: docs/DEVELOPMENT-GUIDE.md"
echo ""
print_status "Setup complete! Pre-commit hooks will now run automatically on each commit."
