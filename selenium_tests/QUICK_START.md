# ⚡ Quick Start - Selenium Testing

## 🎯 3 Steps to Run Tests

### 1️⃣ Setup (First Time Only)
```cmd
cd C:\xampp\htdocs\stu\selenium_tests
SETUP_TESTS.bat
```

### 2️⃣ Start XAMPP
- Open XAMPP Control Panel
- Start Apache ✅
- Start MySQL ✅

### 3️⃣ Run Tests
```cmd
RUN_ALL_SELENIUM_TESTS.bat
```

**Done!** Reports will open automatically.

---

## 📝 Commands Cheat Sheet

### All Tests (Complete Suite)
```cmd
cd C:\xampp\htdocs\stu\selenium_tests
RUN_ALL_SELENIUM_TESTS.bat
```

### Quick Test (Single File)
```cmd
run_quick_test.bat test_authentication.py
```

### View Last Report
```cmd
cd reports
dir /od
start master_report_*.html
```

---

## 🗂️ Test Files

| File | Description | Time |
|------|-------------|------|
| `test_complete_suite.py` | All tests (21 cases) | ~5 min |
| `test_authentication.py` | Login/Register (10 cases) | ~2 min |
| `test_navigation_ui.py` | Navigation/UI (15 cases) | ~3 min |
| `test_database.py` | Database tests | ~1 min |
| `test_integration.py` | Integration tests | ~2 min |

---

## ⚙️ Quick Configuration

Edit `.env` file:
```env
BASE_URL=http://localhost/stu/public
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=student_feedback
BROWSER=chrome
HEADLESS=false
```

---

## 🔥 Most Used Commands

```cmd
# Setup everything
SETUP_TESTS.bat

# Run all tests
RUN_ALL_SELENIUM_TESTS.bat

# Run one test file
run_quick_test.bat test_authentication.py

# Run with Python
pytest test_authentication.py -v

# Run specific test
pytest test_authentication.py::TestAuthentication::test_login_page_loads -v
```

---

## 🆘 Quick Troubleshooting

**Python not found?**
```cmd
python --version
# If error: Install Python from python.org
```

**Module not found?**
```cmd
pip install -r requirements.txt
```

**Can't connect to localhost?**
- Check XAMPP is running
- Visit http://localhost/stu/public in browser
- Update BASE_URL in `.env` if needed

**Tests fail?**
```cmd
# Run in visible mode
set HEADLESS=false
pytest test_complete_suite.py -v
```

---

## 📁 Folder Structure

```
selenium_tests/
├── RUN_ALL_SELENIUM_TESTS.bat    ← Run this!
├── SETUP_TESTS.bat               ← Setup first time
├── run_quick_test.bat            ← Quick single test
├── run_master_tests.py           ← Python runner
├── test_complete_suite.py        ← Main tests
├── test_authentication.py        ← Auth tests
├── test_navigation_ui.py         ← UI tests
├── .env                          ← Configuration
├── requirements.txt              ← Dependencies
└── reports/                      ← All reports here
    └── master_report_*.html      ← Summary report
```

---

## 🎯 Typical Workflow

### Daily Development
```cmd
# Quick test during development
run_quick_test.bat test_authentication.py
```

### Before Commit
```cmd
# Run relevant tests
pytest test_complete_suite.py -v
```

### Before Deployment
```cmd
# Run everything
RUN_ALL_SELENIUM_TESTS.bat
```

---

## 💡 Pro Tips

✅ Always start XAMPP before running tests
✅ Use `run_quick_test.bat` for faster feedback
✅ Check reports after each run
✅ Run `SETUP_TESTS.bat` after pulling new code
✅ Set `HEADLESS=false` to see browser in action

---

## 📊 What Gets Tested?

✅ Login/Registration pages
✅ Navigation and links
✅ Form validation
✅ Page loading
✅ UI elements (buttons, forms)
✅ Database connectivity
✅ API endpoints
✅ Responsive design
✅ JavaScript errors
✅ Admin/Student/Teacher modules

---

## 🎉 Success Indicators

After running tests, you should see:
- ✓ Green checkmarks for passed tests
- 📊 HTML report automatically opens
- 📁 Reports saved in `reports/` folder
- ⏱️ Total execution time
- 📈 Pass/Fail statistics

---

**Need more details?** See [SELENIUM_TEST_GUIDE.md](SELENIUM_TEST_GUIDE.md)

---

*Quick Start Guide v1.0*
