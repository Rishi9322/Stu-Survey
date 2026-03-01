@echo off
echo ==========================================
echo Real Selenium Test Execution
echo Student Feedback System
echo ==========================================
echo.

REM Check if Selenium Server is needed
echo Checking Selenium Server...
echo.

REM Set paths
set PHP_PATH=C:\xampp\php\php.exe
set SCRIPT_PATH=%~dp0RealSeleniumTestRunner.php
set BASE_URL=http://localhost/stu/public/
set SELENIUM_HOST=http://localhost:4444

echo Configuration:
echo PHP: %PHP_PATH%
echo Base URL: %BASE_URL%
echo Selenium Server: %SELENIUM_HOST%
echo.

REM Check if PHP exists
if not exist "%PHP_PATH%" (
    echo ERROR: PHP not found at %PHP_PATH%
    echo Please update PHP_PATH in this batch file
    pause
    exit /b 1
)

echo Starting real Selenium tests...
echo.
echo NOTE: This requires Selenium Server running on port 4444
echo If Selenium Server is not running, tests will fail
echo.
echo To start Selenium Server:
echo   1. Download: https://www.selenium.dev/downloads/
echo   2. Run: java -jar selenium-server-4.x.x.jar standalone
echo.
echo Press Ctrl+C to cancel, or
pause

REM Run the real Selenium tests
"%PHP_PATH%" "%SCRIPT_PATH%" "%BASE_URL%" "%SELENIUM_HOST%"

echo.
echo ==========================================
echo Test execution completed!
echo Check test_reports/ folder for results
echo ==========================================
pause
