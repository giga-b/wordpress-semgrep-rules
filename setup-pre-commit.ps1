# WordPress Semgrep Rules - Pre-commit Hook Setup Script
# This script sets up the pre-commit hook environment for the WordPress Semgrep Rules project

param(
    [switch]$SkipInstall,
    [switch]$Force,
    [string]$PythonVersion = "python3.11"
)

Write-Host "🔧 Setting up Pre-commit Hook for WordPress Semgrep Rules" -ForegroundColor Green
Write-Host ""

# Check if we're in the right directory
if (-not (Test-Path ".pre-commit-config.yaml")) {
    Write-Host "❌ Error: .pre-commit-config.yaml not found. Please run this script from the project root." -ForegroundColor Red
    exit 1
}

# Check Python installation
Write-Host "📋 Checking Python installation..." -ForegroundColor Yellow
try {
    $pythonVersion = & $PythonVersion --version 2>&1
    Write-Host "✅ Found Python: $pythonVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ Python not found. Please install Python 3.11 or later." -ForegroundColor Red
    Write-Host "   Download from: https://www.python.org/downloads/" -ForegroundColor Yellow
    exit 1
}

# Check pip installation
Write-Host "📋 Checking pip installation..." -ForegroundColor Yellow
try {
    $pipVersion = & $PythonVersion -m pip --version 2>&1
    Write-Host "✅ Found pip: $pipVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ pip not found. Please install pip." -ForegroundColor Red
    exit 1
}

# Install pre-commit if not already installed
if (-not $SkipInstall) {
    Write-Host "📦 Installing pre-commit..." -ForegroundColor Yellow
    try {
        & $PythonVersion -m pip install pre-commit
        Write-Host "✅ Pre-commit installed successfully" -ForegroundColor Green
    } catch {
        Write-Host "❌ Failed to install pre-commit. Please install manually:" -ForegroundColor Red
        Write-Host "   pip install pre-commit" -ForegroundColor Yellow
        exit 1
    }
}

# Install project dependencies
Write-Host "📦 Installing project dependencies..." -ForegroundColor Yellow
try {
    & $PythonVersion -m pip install -r requirements.txt
    Write-Host "✅ Project dependencies installed successfully" -ForegroundColor Green
} catch {
    Write-Host "❌ Failed to install project dependencies." -ForegroundColor Red
    exit 1
}

# Install pre-commit hooks
Write-Host "🔗 Installing pre-commit hooks..." -ForegroundColor Yellow
try {
    pre-commit install
    Write-Host "✅ Pre-commit hooks installed successfully" -ForegroundColor Green
} catch {
    Write-Host "❌ Failed to install pre-commit hooks." -ForegroundColor Red
    exit 1
}

# Install additional dependencies for hooks
Write-Host "📦 Installing hook dependencies..." -ForegroundColor Yellow
try {
    pre-commit install-hooks
    Write-Host "✅ Hook dependencies installed successfully" -ForegroundColor Green
} catch {
    Write-Host "❌ Failed to install hook dependencies." -ForegroundColor Red
    exit 1
}

# Validate configuration
Write-Host "🔍 Validating pre-commit configuration..." -ForegroundColor Yellow
try {
    pre-commit validate-config
    Write-Host "✅ Pre-commit configuration is valid" -ForegroundColor Green
} catch {
    Write-Host "❌ Pre-commit configuration validation failed." -ForegroundColor Red
    exit 1
}

# Test the hooks
Write-Host "🧪 Testing pre-commit hooks..." -ForegroundColor Yellow
try {
    pre-commit run --all-files
    Write-Host "✅ Pre-commit hooks test completed successfully" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Pre-commit hooks test completed with issues. This is normal for initial setup." -ForegroundColor Yellow
    Write-Host "   Some hooks may fail on existing files. New commits will be checked properly." -ForegroundColor Yellow
}

# Create .gitignore entries if needed
Write-Host "📝 Updating .gitignore..." -ForegroundColor Yellow
$gitignoreContent = @"

# Pre-commit hook outputs
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
Thumbs.db
"@

if (-not (Test-Path ".gitignore")) {
    Set-Content -Path ".gitignore" -Value $gitignoreContent
    Write-Host "✅ Created .gitignore file" -ForegroundColor Green
} else {
    $existingContent = Get-Content ".gitignore" -Raw
    if ($existingContent -notmatch "\.pre-commit-semgrep-.*\.json") {
        Add-Content -Path ".gitignore" -Value "`n# Pre-commit hook outputs`n.pre-commit-semgrep-*.json`n.secrets.baseline"
        Write-Host "✅ Updated .gitignore file" -ForegroundColor Green
    } else {
        Write-Host "✅ .gitignore already contains pre-commit entries" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "🎉 Pre-commit hook setup completed successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 What was installed:" -ForegroundColor Cyan
Write-Host "   • Pre-commit framework" -ForegroundColor White
Write-Host "   • Semgrep security scanning hooks" -ForegroundColor White
Write-Host "   • YAML and JSON validation hooks" -ForegroundColor White
Write-Host "   • PHP syntax checking hooks" -ForegroundColor White
Write-Host "   • File formatting and cleanup hooks" -ForegroundColor White
Write-Host "   • Secrets detection hooks" -ForegroundColor White
Write-Host "   • Custom WordPress validation hooks" -ForegroundColor White
Write-Host ""
Write-Host "🔧 Available commands:" -ForegroundColor Cyan
Write-Host "   • pre-commit run --all-files     # Run all hooks on all files" -ForegroundColor White
Write-Host "   • pre-commit run                 # Run hooks on staged files" -ForegroundColor White
Write-Host "   • pre-commit run --hook-stage manual # Run hooks manually" -ForegroundColor White
Write-Host "   • pre-commit clean               # Clean hook environments" -ForegroundColor White
Write-Host "   • pre-commit uninstall          # Remove pre-commit hooks" -ForegroundColor White
Write-Host ""
Write-Host "📚 Documentation:" -ForegroundColor Cyan
Write-Host "   • Pre-commit: https://pre-commit.com/" -ForegroundColor White
Write-Host "   • Semgrep: https://semgrep.dev/docs/" -ForegroundColor White
Write-Host "   • Project docs: docs/DEVELOPMENT-GUIDE.md" -ForegroundColor White
Write-Host ""
Write-Host "✅ Setup complete! Pre-commit hooks will now run automatically on each commit." -ForegroundColor Green
