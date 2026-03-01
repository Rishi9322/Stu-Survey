@echo off
REM ================================================================
REM Quick Selenium Test Runner - Single Test File
REM ================================================================

echo.
echo ================================================================
echo QUICK TEST RUNNER
echo ================================================================
echo.

if not exist "conftest.py" (
    echo ERROR: Please run from selenium_tests directory
    pause
    exit /b 1
)

REM Check which test to run
if "%1"=="" (
    echo Usage: run_quick_test.bat [test_file]
    echo.
    echo Available test files:
    echo   1. test_complete_suite.py - Full test suite
    echo   2. test_authentication.py - Authentication tests only
    echo   3. test_navigation_ui.py - Navigation and UI tests
    echo   4. test_database.py - Database tests
    echo   5. test_integration.py - Integration tests
    echo.
    set /p choice="Enter number (1-5) or filename: "
    
    if "!choice!"=="1" set TESTFILE=test_complete_suite.py
    if "!choice!"=="2" set TESTFILE=test_authentication.py
    if "!choice!"=="3" set TESTFILE=test_navigation_ui.py
    if "!choice!"=="4" set TESTFILE=test_database.py
    if "!choice!"=="5" set TESTFILE=test_integration.py
    
    if not defined TESTFILE set TESTFILE=!choice!
) else (
    set TESTFILE=%1
)

if not exist "%TESTFILE%" (
    echo ERROR: Test file '%TESTFILE%' not found
    pause
    exit /b 1
)

echo Running: %TESTFILE%
echo.

REM Run the test with HTML report
pytest "%TESTFILE%" -v --html=reports/quick_test_report.html --self-contained-html

echo.
echo Test execution completed
echo Report: reports/quick_test_report.html
echo.
pause
