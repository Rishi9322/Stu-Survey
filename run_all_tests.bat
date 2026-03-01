@echo off
echo ========================================
echo Running Codeception + Behat Test Suite
echo ========================================
echo.

REM Check if Composer dependencies are installed
if not exist "vendor\" (
    echo ERROR: Vendor folder not found!
    echo Please run: composer install
    pause
    exit /b 1
)

echo [1/5] Running Codeception Unit Tests...
echo ========================================
call vendor\bin\codecept run unit --html
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: Unit tests encountered issues
)
echo.

echo [2/5] Running Codeception Functional Tests...
echo ========================================
call vendor\bin\codecept run functional --html
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: Functional tests encountered issues
)
echo.

echo [3/5] Running Codeception Acceptance Tests...
echo ========================================
call vendor\bin\codecept run acceptance --html
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: Acceptance tests encountered issues
)
echo.

echo [4/5] Running Behat BDD Tests...
echo ========================================
call vendor\bin\behat --format=pretty --out=std --format=html --out=tests/_output/behat_report.html
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: Behat tests encountered issues
)
echo.

echo [5/5] Test Execution Complete!
echo ========================================
echo.
echo Test Reports Generated:
echo - Codeception: tests\_output\report.html
echo - Behat: tests\_output\behat_report.html
echo.
echo Press any key to exit...
pause >nul
