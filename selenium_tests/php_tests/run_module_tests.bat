@echo off
echo ===========================================
echo Student Feedback System - Module Testing
echo ===========================================
echo.

REM Check if XAMPP is running
echo Checking XAMPP status...
tasklist | find "httpd.exe" > nul
if %errorlevel% == 0 (
    echo [OK] Apache is running
) else (
    echo [WARNING] Apache may not be running. Please start XAMPP first.
    pause
)

echo.
echo Starting comprehensive module tests...
echo.

REM Change to the test directory
cd /d "%~dp0"

REM Run the simplified test runner (no Selenium server required)
C:\xampp\php\php.exe SimplifiedTestRunner.php

echo.
echo Test execution completed!
echo.
echo Reports generated in: test_reports\
echo.

REM Open the test reports directory
explorer ..\test_reports\

pause