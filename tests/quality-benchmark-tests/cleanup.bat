@echo off
echo ========================================
echo WordPress Semgrep Rules Quality Benchmarks
echo Cleanup Script
echo ========================================
echo.

echo WARNING: This will delete ALL test files and results!
echo.
echo Files to be deleted:
echo - 100 PHP test files
echo - All benchmark results
echo - Generated reports
echo.

set /p CONFIRM="Are you sure you want to continue? (yes/no): "

if /i "%CONFIRM%"=="yes" (
    echo.
    echo Cleaning up test files...
    
    REM Delete PHP test files
    del /q *.php 2>nul
    echo - Deleted PHP test files
    
    REM Delete results directory
    if exist results (
        rmdir /s /q results
        echo - Deleted results directory
    )
    
    REM Delete generated files
    del /q TEST_SUMMARY.md 2>nul
    echo - Deleted test summary
    
    REM Keep the core files
    echo.
    echo Kept core files:
    echo - README.md
    echo - TEST_FILE_REGISTRY.md
    echo - generate_test_files.py
    echo - run-quality-benchmarks.py
    echo - run-benchmarks.bat
    echo - run-benchmarks.ps1
    echo - cleanup.bat
    echo.
    echo Cleanup completed successfully!
) else (
    echo Cleanup cancelled.
)

echo.
pause
