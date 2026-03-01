# Real Selenium Testing Guide
## Student Feedback System - Automated Testing

This guide covers how to run **real** automated tests (not mocks) on your Student Feedback System.

---

## 🎯 Test Options

We provide **TWO** ways to run real tests:

### Option 1: Full Selenium WebDriver Tests (Browser Automation)
- **File:** `RealSeleniumTestRunner.php`
- **Method:** Launches real Chrome browser and interacts with UI
- **Requirements:** Selenium Server must be running
- **Best for:** Complete UI/UX testing with real browser interactions

### Option 2: Standalone HTTP Tests (Quick & Easy)
- **File:** `StandaloneRealTestRunner.php`
- **Method:** Direct HTTP requests using cURL
- **Requirements:** Just PHP (no Selenium Server needed)
- **Best for:** Quick validation, CI/CD pipelines, API testing

---

## 🚀 Option 1: Full Selenium Tests (Recommended for Complete Testing)

### Prerequisites
1. **Selenium Server** must be running
2. **ChromeDriver** must be installed
3. **Java** must be installed (for Selenium Server)

### Step 1: Download Selenium Server
```bash
# Download from: https://www.selenium.dev/downloads/
# Or use direct link for Selenium 4:
curl -O https://github.com/SeleniumHQ/selenium/releases/download/selenium-4.16.0/selenium-server-4.16.1.jar
```

### Step 2: Download ChromeDriver
```bash
# Download from: https://chromedriver.chromium.org/downloads
# Must match your Chrome browser version
# Extract to a directory in your PATH
```

### Step 3: Start Selenium Server
```bash
# Open a new terminal and run:
java -jar selenium-server-4.16.1.jar standalone

# You should see:
# "Selenium Server is up and running on port 4444"
```

### Step 4: Run the Tests
```bash
# Option A: Use the batch file (easiest)
cd selenium_tests/php_tests
run_real_tests.bat

# Option B: Run directly with PHP
C:\xampp\php\php.exe RealSeleniumTestRunner.php

# Option C: Custom URL and Selenium host
C:\xampp\php\php.exe RealSeleniumTestRunner.php "http://localhost/stu/public/" "http://localhost:4444"
```

---

## ⚡ Option 2: Standalone Tests (No Selenium Required)

### Prerequisites
- Just PHP (nothing else needed!)

### Run the Tests
```bash
# Option A: Use the batch file (easiest)
cd selenium_tests/php_tests
run_standalone_tests.bat

# Option B: Run directly with PHP
C:\xampp\php\php.exe StandaloneRealTestRunner.php

# Option C: Custom URL
C:\xampp\php\php.exe StandaloneRealTestRunner.php "http://localhost/stu/public/"
```

---

## 📊 What Gets Tested

Both test runners test all 4 main modules:

### ✅ Admin Module (5 tests)
- Login page accessibility and form elements
- Dashboard access control
- User management interface
- Reports page functionality
- Authentication protection

### ✅ Student Module (5 tests)
- Registration page and form validation
- Student dashboard protection
- Feedback submission interface
- Profile management
- Feedback history viewing

### ✅ Teacher Module (5 tests)
- Teacher dashboard access control
- Student feedback viewing capabilities
- Teacher profile management
- Analytics dashboard
- Response to feedback functionality

### ✅ API Module (5 tests)
- API health check endpoint
- Feedback API functionality
- User management API
- Statistics API endpoint
- Data export API

**Total: 20 real tests per run**

---

## 📁 Test Reports

After running tests, you'll find reports in: `test_reports/`

### Report Files Generated

#### Selenium Tests:
- `selenium_real_raw_YYYY-MM-DD_HH-MM-SS.txt` - Raw test output
- `selenium_real_academic_YYYY-MM-DD_HH-MM-SS.html` - Professional HTML report

#### Standalone Tests:
- `standalone_real_raw_YYYY-MM-DD_HH-MM-SS.txt` - Raw test output
- `standalone_real_academic_YYYY-MM-DD_HH-MM-SS.html` - Professional HTML report

### Report Contents
- ✅ Pass/Fail status for each test
- ⏱ Execution time per test
- 📊 Success rate statistics
- 📈 Module-specific performance
- 💡 Detailed findings and recommendations

---

## 🔧 Troubleshooting

### Selenium Tests Not Working?

**Problem:** "Failed to initialize WebDriver"
```
Solution: Make sure Selenium Server is running
Command: java -jar selenium-server-4.16.1.jar standalone
Check: Open http://localhost:4444 in browser to verify
```

**Problem:** "ChromeDriver version mismatch"
```
Solution: Download ChromeDriver matching your Chrome version
Check Chrome version: chrome://version/
Download matching ChromeDriver from: https://chromedriver.chromium.org/
```

**Problem:** "Connection refused on port 4444"
```
Solution 1: Check if Selenium Server is running
Solution 2: Check firewall settings
Solution 3: Try different port and specify in command:
    php RealSeleniumTestRunner.php "http://localhost/stu/public/" "http://localhost:9515"
```

### Standalone Tests Not Working?

**Problem:** "Could not resolve host"
```
Solution: Check if XAMPP Apache is running
Verify: Open http://localhost/stu/public/ in browser
```

**Problem:** "PHP not found"
```
Solution: Update PHP path in batch file
Edit: run_standalone_tests.bat
Change: set PHP_PATH=C:\xampp\php\php.exe (adjust to your path)
```

---

## 🎨 Customization

### Change Base URL
Edit the batch files or pass as parameter:
```bash
php StandaloneRealTestRunner.php "http://your-custom-url/stu/public/"
```

### Run Headless (Selenium)
The Selenium tests already run in headless mode by default (no visible browser window).

To see the browser during testing, edit `RealSeleniumTestRunner.php`:
```php
// Line ~42: Remove '--headless' from args
'args' => ['--no-sandbox', '--disable-dev-shm-usage', '--disable-gpu']
```

### Modify Tests
Both test files are well-commented and easy to modify:
- **RealSeleniumTestRunner.php** - Lines 63-484 contain all test methods
- **StandaloneRealTestRunner.php** - Lines 83-481 contain all test methods

---

## ⚖️ Comparison: Selenium vs Standalone

| Feature | Selenium Tests | Standalone Tests |
|---------|---------------|------------------|
| **Setup** | Complex (requires Selenium Server) | Simple (just PHP) |
| **Speed** | Slower (launches browser) | Fast (direct HTTP) |
| **Browser Testing** | ✅ Yes (real browser) | ❌ No |
| **JavaScript Testing** | ✅ Yes | ❌ Limited |
| **API Testing** | ✅ Yes | ✅ Yes |
| **Authentication** | ✅ Can test login flows | ✅ Tests protection |
| **CI/CD Friendly** | ❌ Requires setup | ✅ Very easy |
| **Visual Validation** | ✅ Can validate UI | ❌ HTTP only |
| **Best For** | Complete UI testing | Quick validation, APIs |

---

## 💡 Recommendations

### For Development:
Use **Standalone Tests** for quick validation during development.
```bash
run_standalone_tests.bat
```

### For QA/Testing:
Use **Selenium Tests** for comprehensive testing before releases.
```bash
# Make sure Selenium Server is running first!
run_real_tests.bat
```

### For CI/CD:
Use **Standalone Tests** in automated pipelines (faster, simpler).
```yaml
# Example GitHub Actions
- name: Run Tests
  run: php selenium_tests/php_tests/StandaloneRealTestRunner.php
```

---

## 📞 Support

If you encounter issues:
1. Check the troubleshooting section above
2. Verify your PHP version: `php --version` (requires PHP 7.4+)
3. Ensure XAMPP Apache is running
4. For Selenium tests, verify Selenium Server is accessible at http://localhost:4444

---

## ✨ Key Differences from Mock Tests

### ❌ Old Mock Tests (MockTestRunner.php)
- Generated fake results
- No actual testing performed
- Just for demonstration

### ✅ New Real Tests
- **RealSeleniumTestRunner.php:** Launches real browser, tests actual pages
- **StandaloneRealTestRunner.php:** Makes real HTTP requests, validates responses
- Both test real functionality and generate real results

---

**Happy Testing! 🚀**
