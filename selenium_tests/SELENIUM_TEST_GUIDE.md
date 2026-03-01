# 🧪 Selenium Test Suite - Complete Guide

## 📋 Table of Contents
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Running Tests](#running-tests)
- [Test Files](#test-files)
- [Configuration](#configuration)
- [Reports](#reports)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

This comprehensive Selenium test suite provides automated testing for the Student Feedback System, covering:
- ✅ Authentication (login, registration, logout)
- ✅ Navigation and UI elements
- ✅ Database connectivity
- ✅ API endpoints
- ✅ Student, Teacher, and Admin modules

**Technology Stack:**
- Python 3.8+
- Selenium WebDriver
- Pytest framework
- Chrome/Firefox browsers
- HTML reporting

---

## 📦 Prerequisites

### Required Software
1. **Python 3.8 or higher**
   - Download: https://www.python.org/downloads/
   - ⚠️ Important: Check "Add Python to PATH" during installation

2. **XAMPP** (for local server)
   - Apache should be running
   - MySQL should be running
   - Project accessible at: `http://localhost/stu/public`

3. **Web Browsers**
   - Chrome (latest version) **OR**
   - Firefox (latest version)

### Optional
- ChromeDriver (auto-downloaded by webdriver-manager)
- GeckoDriver for Firefox (auto-downloaded)

---

## 🚀 Quick Start

### Step 1: Initial Setup

Open Command Prompt in the `selenium_tests` folder and run:

```cmd
SETUP_TESTS.bat
```

This will:
- Check Python installation
- Install all dependencies
- Create `.env` configuration file
- Set up reports directory

### Step 2: Configure Environment

Edit `.env` file with your settings:

```env
BASE_URL=http://localhost/stu/public
PHP_API_URL=http://localhost/stu/api
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=
DB_NAME=student_feedback
```

### Step 3: Start Your Server

Make sure XAMPP is running:
- ✅ Apache: Running
- ✅ MySQL: Running

### Step 4: Run Tests

```cmd
RUN_ALL_SELENIUM_TESTS.bat
```

That's it! Tests will run automatically and generate reports.

---

## 🎮 Running Tests

### Option 1: Run All Tests (Recommended)

```cmd
RUN_ALL_SELENIUM_TESTS.bat
```

**Features:**
- Runs all test suites sequentially
- Generates individual reports for each suite
- Creates master summary report
- Auto-opens final report in browser

**Time:** ~5-15 minutes depending on system

---

### Option 2: Run Quick Test (Single File)

```cmd
run_quick_test.bat test_authentication.py
```

Or run without arguments for interactive menu:

```cmd
run_quick_test.bat
```

**Available Quick Tests:**
1. `test_complete_suite.py` - Full comprehensive suite
2. `test_authentication.py` - Login/registration only
3. `test_navigation_ui.py` - Navigation and UI only
4. `test_database.py` - Database connectivity
5. `test_integration.py` - Integration tests

**Time:** ~2-5 minutes per file

---

### Option 3: Run with Python Directly

```cmd
cd selenium_tests
pytest test_authentication.py -v
```

**With HTML report:**
```cmd
pytest test_authentication.py -v --html=reports/my_report.html --self-contained-html
```

**Run specific test:**
```cmd
pytest test_authentication.py::TestAuthentication::test_login_page_loads -v
```

---

### Option 4: Run Master Python Script

```cmd
python run_master_tests.py
```

This runs all tests programmatically with enhanced reporting.

---

## 📁 Test Files

### `test_complete_suite.py`
Comprehensive test suite covering all major functionality:
- Homepage loading and navigation
- Login page elements
- Registration form validation
- Admin/Student/Teacher modules
- API endpoints
- Performance and responsive design
- JavaScript error checking

**Tests:** 21 test cases
**Time:** ~5 minutes

---

### `test_authentication.py`
Authentication-focused tests:
- Login page accessibility
- Form validation
- Password field security
- Registration process
- Remember me functionality
- Logout functionality
- Session management

**Tests:** 10 test cases
**Time:** ~2 minutes

---

### `test_navigation_ui.py`
Navigation and UI element tests:
- Navigation between pages
- Link functionality
- Header and footer elements
- Button visibility
- Form labels (accessibility)
- Image alt text
- Broken image detection
- Browser back/forward buttons

**Tests:** 15 test cases
**Time:** ~3 minutes

---

### `test_database.py`
Database connectivity tests (existing):
- Database connection
- Query execution
- Data integrity

---

### `test_integration.py`
Integration tests (existing):
- End-to-end workflows
- Module integration

---

## ⚙️ Configuration

### Environment Variables (.env)

```env
# Server URLs
BASE_URL=http://localhost/stu/public
PHP_API_URL=http://localhost/stu/api

# Database
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=
DB_NAME=student_feedback

# Test Credentials
TEST_EMAIL=testuser@example.com
TEST_PASSWORD=TestPassword123!

# Browser Options
BROWSER=chrome                  # chrome or firefox
HEADLESS=false                  # true for headless mode
WINDOW_WIDTH=1920
WINDOW_HEIGHT=1080

# Timeouts
IMPLICIT_WAIT=10
EXPLICIT_WAIT=10
PAGE_LOAD_TIMEOUT=30

# Reports
REPORT_DIR=./reports
REPORT_FORMAT=html
LOG_LEVEL=INFO
```

### Changing Browser

**Chrome (default):**
```env
BROWSER=chrome
```

**Firefox:**
```env
BROWSER=firefox
```

**Headless mode (no GUI):**
```env
HEADLESS=true
```

---

## 📊 Reports

### Report Types

1. **Individual Test Reports**
   - Location: `reports/[testname]_report_YYYYMMDD_HHMMSS.html`
   - Details: Detailed results for each test file
   - Format: Interactive HTML with pass/fail status

2. **Master Summary Report**
   - Location: `reports/master_report_YYYYMMDD_HHMMSS.html`
   - Details: Combined summary of all test executions
   - Format: Dashboard with statistics and links

### Opening Reports

Reports automatically open after test completion. To open manually:

```cmd
cd reports
start master_report_20260216_103045.html
```

Or simply double-click the HTML file.

### Report Features

✅ Pass/Fail status with color coding
✅ Execution time for each test
✅ Error messages and stack traces
✅ Screenshots (if configured)
✅ Links to detailed logs
✅ Test statistics and charts

---

## 🔧 Troubleshooting

### Issue: "Python is not recognized"

**Solution:**
1. Install Python from https://www.python.org/downloads/
2. During installation, check "Add Python to PATH"
3. Restart Command Prompt
4. Verify: `python --version`

---

### Issue: "ModuleNotFoundError: No module named 'selenium'"

**Solution:**
```cmd
cd selenium_tests
pip install -r requirements.txt
```

Or run:
```cmd
SETUP_TESTS.bat
```

---

### Issue: "selenium.common.exceptions.WebDriverException"

**Solution:**
1. Make sure Chrome/Firefox is installed
2. Update browser to latest version
3. Dependencies will auto-download drivers
4. If still failing, manually install:
   ```cmd
   pip install webdriver-manager --upgrade
   ```

---

### Issue: "Connection refused" or "Cannot connect to localhost"

**Solution:**
1. Make sure XAMPP is running (Apache + MySQL)
2. Test manually: Open `http://localhost/stu/public` in browser
3. Check `.env` file has correct BASE_URL
4. If using different port, update `.env`:
   ```env
   BASE_URL=http://localhost:8080/stu/public
   ```

---

### Issue: Tests fail with "Element not found"

**Solution:**
1. Website might be slow - increase timeout in `.env`:
   ```env
   EXPLICIT_WAIT=20
   ```
2. Check if page structure changed
3. Run tests in visible mode (not headless):
   ```env
   HEADLESS=false
   ```

---

### Issue: "Access Denied" or permission errors

**Solution:**
1. Run Command Prompt as Administrator
2. Check file permissions in selenium_tests folder
3. Disable antivirus temporarily (some block Selenium)

---

### Issue: Browser opens but immediately closes

**Solution:**
This is normal - Selenium controls the browser. Tests run automatically.
To see the browser in action:
```env
HEADLESS=false
```

---

### Issue: Tests run very slowly

**Potential causes and solutions:**
1. **Slow system:** Increase timeouts in `.env`
2. **Network issues:** Check internet connection
3. **Too many tests:** Run quick tests instead:
   ```cmd
   run_quick_test.bat test_authentication.py
   ```

---

## 🎯 Best Practices

### 1. Run Tests Before Deployment
```cmd
RUN_ALL_SELENIUM_TESTS.bat
```

### 2. Use Headless Mode for CI/CD
```env
HEADLESS=true
```

### 3. Keep Dependencies Updated
```cmd
pip install --upgrade -r requirements.txt
```

### 4. Review Reports After Each Run
Check `reports/master_report_*.html`

### 5. Run Quick Tests During Development
```cmd
run_quick_test.bat test_authentication.py
```

---

## 📞 Support

### Common Commands Reference

```cmd
# Setup
SETUP_TESTS.bat

# Run all tests
RUN_ALL_SELENIUM_TESTS.bat

# Run single test file
run_quick_test.bat test_authentication.py

# Run with pytest
pytest test_authentication.py -v

# Generate HTML report
pytest test_authentication.py --html=reports/report.html --self-contained-html

# Run in headless mode
set HEADLESS=true && pytest test_complete_suite.py

# Check Python version
python --version

# Check installed packages
pip list

# Reinstall dependencies
pip install -r requirements.txt --force-reinstall
```

---

## 🎓 Advanced Usage

### Run Tests in Parallel
```cmd
pytest -n 4 test_complete_suite.py
```
(Requires: `pip install pytest-xdist`)

### Run Only Failed Tests
```cmd
pytest --lf
```

### Run Tests with Markers
```cmd
pytest -m "authentication" -v
```

### Generate Coverage Report
```cmd
pytest --cov=. --cov-report=html
```

---

## 📝 Notes

- Tests are non-destructive (read-only operations)
- Some tests may skip if features are not implemented
- Reports are timestamped to prevent overwrites
- Browser drivers auto-update via webdriver-manager
- Tests run in isolated sessions (cookies cleared between tests)

---

## ✨ Summary

**To run tests:**
1. `SETUP_TESTS.bat` (first time only)
2. Start XAMPP
3. `RUN_ALL_SELENIUM_TESTS.bat`
4. View reports in `reports/` folder

**That's it!** 🎉

---

*Last Updated: February 2026*
*Version: 1.0.0*
