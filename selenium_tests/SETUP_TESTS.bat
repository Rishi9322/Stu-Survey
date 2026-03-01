@echo off
REM ================================================================
REM Selenium Test Suite - Setup Script
REM Installs all required dependencies for testing
REM ================================================================

echo.
echo ================================================================
echo SELENIUM TEST SUITE - SETUP
echo ================================================================
echo.

REM Check Python installation
python --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python is not installed or not in PATH
    echo.
    echo Please download and install Python 3.8+ from:
    echo https://www.python.org/downloads/
    echo.
    echo Make sure to check "Add Python to PATH" during installation
    pause
    exit /b 1
)

echo [1/5] Python found:
python --version
echo.

REM Check pip
echo [2/5] Checking pip...
python -m pip --version
if errorlevel 1 (
    echo Installing pip...
    python -m ensurepip --upgrade
)
echo.

REM Upgrade pip
echo [3/5] Upgrading pip...
python -m pip install --upgrade pip
echo.

REM Install dependencies
echo [4/5] Installing test dependencies...
echo This may take a few minutes...
echo.
pip install -r requirements.txt
echo.

REM Setup environment file
echo [5/5] Setting up environment...
if not exist ".env" (
    if exist ".env.example" (
        echo Creating .env file from template...
        copy .env.example .env
        echo.
        echo Please edit .env file to configure your test settings:
        echo   - BASE_URL: Your application URL
        echo   - Database credentials
        echo   - Test user credentials
        echo.
    ) else (
        echo WARNING: .env.example not found
    )
) else (
    echo .env file already exists
)
echo.

REM Create reports directory
if not exist "reports" (
    echo Creating reports directory...
    mkdir reports
)

REM Check if Chrome is installed
echo.
echo Checking browsers...
where chrome >nul 2>&1
if errorlevel 1 (
    echo WARNING: Chrome not detected in PATH
    echo Please make sure Chrome or ChromeDriver is installed
) else (
    echo ✓ Chrome detected
)

where firefox >nul 2>&1
if not errorlevel 1 (
    echo ✓ Firefox detected
)

echo.
echo ================================================================
echo SETUP COMPLETE!
echo ================================================================
echo.
echo Next steps:
echo   1. Edit .env file if needed (update URLs and credentials)
echo   2. Make sure XAMPP is running (Apache and MySQL)
echo   3. Run: RUN_ALL_SELENIUM_TESTS.bat
echo.
echo Quick test: run_quick_test.bat [test_file]
echo.
pause
