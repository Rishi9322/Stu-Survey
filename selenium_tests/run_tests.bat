@echo off
REM Windows batch script to run Selenium tests

echo ================================
echo Student Feedback System - Selenium Tests
echo ================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Error: Python is not installed or not in PATH
    exit /b 1
)

REM Install requirements if needed
echo Installing dependencies...
pip install -q -r requirements.txt

echo.
echo ================================
echo Running Tests
echo ================================
echo.

REM Run pytest with HTML report
pytest -v --html=reports/test_report.html --self-contained-html

echo.
echo ================================
echo Test Execution Complete
echo ================================
echo Report generated: reports/test_report.html

REM Open report in default browser
start reports/test_report.html

pause
