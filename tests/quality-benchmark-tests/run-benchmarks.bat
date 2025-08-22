@echo off
echo ========================================
echo WordPress Semgrep Rules Quality Benchmarks
echo ========================================
echo.

REM Get the project root (parent of tests directory)
set "PROJECT_ROOT=%~dp0..\.."
cd /d "%PROJECT_ROOT%"

echo Project Root: %PROJECT_ROOT%
echo.

REM Check if Python is available
python --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python is not available in PATH
    echo Please install Python and ensure it's in your PATH
    pause
    exit /b 1
)

REM Check if Semgrep is available
semgrep --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Semgrep is not available in PATH
    echo Please install Semgrep and ensure it's in your PATH
    pause
    exit /b 1
)

echo Python and Semgrep are available
echo.

REM Run benchmarks
echo Running quality benchmarks...
echo.

python tests\quality-benchmark-tests\run-quality-benchmarks.py "%PROJECT_ROOT%"

echo.
echo Benchmarks completed!
echo Check the results directory for detailed reports.
echo.
pause
