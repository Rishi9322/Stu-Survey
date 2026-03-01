@echo off
echo ==========================================
echo Standalone Real Test Execution
echo Student Feedback System
echo ==========================================
echo.

REM Set paths
set PHP_PATH=C:\xampp\php\php.exe
set SCRIPT_PATH=%~dp0StandaloneRealTestRunner.php
set BASE_URL=http://localhost/stu/public/

echo Configuration:
echo PHP: %PHP_PATH%
echo Base URL: %BASE_URL%
echo Method: Direct HTTP Testing (No Selenium Required)
echo.

REM Check if PHP exists
if not exist "%PHP_PATH%" (
    echo ERROR: PHP not found at %PHP_PATH%
    echo Please update PHP_PATH in this batch file
    pause
    exit /b 1
)

echo Starting standalone real tests...
echo This uses direct HTTP requests - no Selenium Server needed!
echo.

REM Run the standalone tests
"%PHP_PATH%" "%SCRIPT_PATH%" "%BASE_URL%"

echo.
echo ==========================================
echo Test execution completed!
echo Check test_reports/ folder for results
echo ==========================================
pause
