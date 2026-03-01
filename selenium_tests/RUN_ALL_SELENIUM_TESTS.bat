@echo off
REM ================================================================
REM Selenium Test Suite - Master Test Runner
REM Executes all Selenium tests with comprehensive reporting
REM ================================================================

echo.
echo ================================================================
echo SELENIUM TEST SUITE - MASTER RUNNER
echo Student Feedback System
echo ================================================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python is not installed or not in PATH
    echo Please install Python 3.8 or higher
    pause
    exit /b 1
)

echo [1/4] Checking Python installation...
python --version

REM Check if in correct directory
if not exist "conftest.py" (
    echo ERROR: Please run this script from the selenium_tests directory
    pause
    exit /b 1
)

echo [2/4] Installing/updating dependencies...
pip install -r requirements.txt --quiet

echo [3/4] Setting up environment...
if not exist ".env" (
    if exist ".env.example" (
        echo Creating .env from .env.example...
        copy .env.example .env
    )
)

echo [4/4] Running test suite...
echo.
echo ================================================================
echo Starting test execution...
echo ================================================================
echo.

REM Run the master test runner
python run_master_tests.py

set EXIT_CODE=%ERRORLEVEL%

echo.
echo ================================================================
echo Test execution completed
echo ================================================================
echo.
echo To view detailed reports, check the 'reports' folder
echo The master report has been automatically opened (if available)
echo.

if %EXIT_CODE% equ 0 (
    echo ✓ All tests passed successfully!
) else (
    echo ✗ Some tests failed. Check reports for details.
)

echo.
pause
exit /b %EXIT_CODE%
