# 🧪 Complete Selenium Test Suite - Student Feedback System

![Tests](https://img.shields.io/badge/tests-46+-brightgreen)
![Python](https://img.shields.io/badge/python-3.8+-blue)
![Selenium](https://img.shields.io/badge/selenium-4.15+-orange)

## 🎯 Quick Start for Windows CMD

### Step 1: Setup (First Time Only)
```cmd
cd C:\xampp\htdocs\stu\selenium_tests
SETUP_TESTS.bat
```

### Step 2: Start XAMPP
- Open XAMPP Control Panel
- Start Apache ✅
- Start MySQL ✅

### Step 3: Run Tests
```cmd
RUN_ALL_SELENIUM_TESTS.bat
```

**Done!** 🎉 Reports open automatically.

---

## 📂 New Test Files Created

### Complete Test Suite
- ✅ **test_complete_suite.py** - 21 comprehensive tests
- ✅ **test_authentication.py** - 10 authentication tests  
- ✅ **test_navigation_ui.py** - 15 navigation & UI tests

### Runners
- ✅ **run_master_tests.py** - Python test orchestrator
- ✅ **RUN_ALL_SELENIUM_TESTS.bat** - Windows batch runner
- ✅ **run_quick_test.bat** - Single test runner
- ✅ **SETUP_TESTS.bat** - One-time setup

### Documentation
- ✅ **QUICK_START.md** - Fast reference
- ✅ **SELENIUM_TEST_GUIDE.md** - Complete guide
- ✅ **README_NEW.md** - This file

---

## 🎮 Available Commands

### Run All Tests (Full Suite)
```cmd
cd C:\xampp\htdocs\stu\selenium_tests
RUN_ALL_SELENIUM_TESTS.bat
```

### Run Single Test File
```cmd
run_quick_test.bat test_authentication.py
```

### Interactive Menu
```cmd
run_quick_test.bat
```

### Using Python Directly
```cmd
pytest test_complete_suite.py -v
```

### With HTML Report
```cmd
pytest test_complete_suite.py -v --html=reports/my_report.html --self-contained-html
```

---

## 📊 Test Coverage

| Test File | Tests | Description | Time |
|-----------|-------|-------------|------|
| test_complete_suite.py | 21 | Full system test | ~5 min |
| test_authentication.py | 10 | Login/Register | ~2 min |
| test_navigation_ui.py | 15 | Navigation & UI | ~3 min |
| test_database.py | 5 | Database tests | ~1 min |
| test_integration.py | 6 | Integration tests | ~2 min |
| **TOTAL** | **57** | **All tests** | **~13 min** |

---

## 🎯 What's Tested

### ✅ Authentication (10 tests)
- Login page loading and accessibility
- Form validation and elements
- Password field security
- Registration page and forms
- Error handling for invalid credentials
- Remember me functionality
- Forgot password links
- Terms agreement

### ✅ Navigation & UI (15 tests)
- Homepage to login navigation
- Homepage to registration navigation
- All navigation links functionality
- Header and footer presence
- Logo/brand elements
- Browser back/forward buttons
- Page titles and meta tags
- Button visibility and styling
- Form labels for accessibility
- Image alt text
- External link handling
- Broken image detection

### ✅ Complete Suite (21 tests)
- Homepage loading and content
- All public pages (login, register, about, contact, privacy, terms, help)
- Form elements validation
- Admin module protection
- Student dashboard
- Teacher dashboard
- API endpoints
- Page load performance
- Responsive design
- JavaScript error detection

---

## ⚙️ Configuration

Edit `.env` file:

```env
# Application URLs
BASE_URL=http://localhost/stu/public
PHP_API_URL=http://localhost/stu/api

# Database
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=
DB_NAME=student_feedback

# Browser Settings
BROWSER=chrome              # chrome or firefox
HEADLESS=false             # true for no GUI
WINDOW_WIDTH=1920
WINDOW_HEIGHT=1080

# Timeouts
IMPLICIT_WAIT=10
EXPLICIT_WAIT=10
PAGE_LOAD_TIMEOUT=30
```

---

## 📊 Reports

### Report Locations
All reports saved in: `C:\xampp\htdocs\stu\selenium_tests\reports\`

### Report Types

1. **Master Report** - `master_report_YYYYMMDD_HHMMSS.html`
   - Dashboard with all results
   - Statistics and charts
   - Links to individual reports

2. **Individual Reports** - `[testname]_report_YYYYMMDD_HHMMSS.html`
   - Detailed test results
   - Error messages and stack traces
   - Execution times

### View Reports
```cmd
cd reports
dir /od
start master_report_20260216_103045.html
```

---

## 🔧 Troubleshooting

### Python not found
```cmd
python --version
```
If error: Install from https://python.org (check "Add Python to PATH")

### Module errors
```cmd
SETUP_TESTS.bat
```

### Cannot connect to localhost
1. Start XAMPP (Apache + MySQL)
2. Test: http://localhost/stu/public in browser
3. Check BASE_URL in `.env`

### See browser in action
Edit `.env`:
```env
HEADLESS=false
```

---

## 📚 Full Documentation

- 📘 **QUICK_START.md** - Quick reference guide (3 pages)
- 📗 **SELENIUM_TEST_GUIDE.md** - Complete guide with troubleshooting (15+ pages)
- 📙 **README_NEW.md** - This overview

---

## 🎓 Advanced Usage

### Run specific test
```cmd
pytest test_authentication.py::TestAuthentication::test_login_page_loads -v
```

### Run in parallel (faster)
```cmd
pytest -n 4 test_complete_suite.py
```

### Run only failed tests
```cmd
pytest --lf
```

### Generate coverage report
```cmd
pytest --cov=. --cov-report=html
```

---

## ✨ Features

✅ 57+ automated test cases
✅ One-click execution
✅ Comprehensive HTML reports
✅ Master test orchestration
✅ Chrome & Firefox support
✅ Headless mode option
✅ Detailed documentation
✅ Error screenshots (configurable)
✅ Parallel execution support
✅ CI/CD ready

---

## 🚀 Typical Workflow

### Development
```cmd
run_quick_test.bat test_authentication.py
```

### Before Commit
```cmd
pytest test_complete_suite.py -v
```

### Before Deployment
```cmd
RUN_ALL_SELENIUM_TESTS.bat
```

---

## 📈 Success Indicators

After running tests successfully:
- ✅ Green checkmarks in terminal
- ✅ HTML reports open automatically
- ✅ All reports saved in `reports/` folder
- ✅ Master report shows statistics
- ✅ Zero failed tests

---

## 💡 Pro Tips

1. Always start XAMPP before testing
2. Use `run_quick_test.bat` for faster feedback during development
3. Check reports after each run for detailed insights
4. Run `SETUP_TESTS.bat` after pulling code updates
5. Set `HEADLESS=false` to watch tests execute

---

## 🎯 Command Summary

```cmd
# First time setup
SETUP_TESTS.bat

# Run all tests (recommended)
RUN_ALL_SELENIUM_TESTS.bat

# Run one test file
run_quick_test.bat test_authentication.py

# Interactive menu
run_quick_test.bat

# Python direct
pytest test_authentication.py -v

# With HTML report
pytest test_authentication.py --html=reports/report.html --self-contained-html

# List all tests
pytest --collect-only

# Help
pytest --help
```

---

## 🎉 Summary

### To Execute Tests:

1. **First Time:**
   ```cmd
   cd C:\xampp\htdocs\stu\selenium_tests
   SETUP_TESTS.bat
   ```

2. **Every Time:**
   ```cmd
   RUN_ALL_SELENIUM_TESTS.bat
   ```

3. **View Results:**
   - Reports open automatically
   - Or: `cd reports` → double-click HTML files

**That's it!** 🚀

---

*Version: 1.0.0*
*Last Updated: February 2026*
*Created by: GitHub Copilot*
