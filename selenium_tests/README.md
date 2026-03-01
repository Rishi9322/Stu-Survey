# Selenium Testing Suite

Comprehensive Selenium testing suite for the Student Feedback System, including tests for:
- React Frontend (port 5173)
- Node.js Backend API (port 5000)
- PHP Public Pages (port 80)

## Setup

### 1. Install Dependencies

```bash
# Windows
pip install -r requirements.txt

# Mac/Linux
pip3 install -r requirements.txt
```

### 2. Configure Environment

Copy `.env.example` to `.env` and update with your configuration:

```bash
cp .env.example .env
```

Edit `.env` with your specific URLs and credentials.

### 3. Start Services

Make sure all services are running:

```bash
# Frontend (from stu_react directory)
npm run dev

# Backend (from stu_react/backend directory)
npm start

# PHP Server (from stu directory)
php -S localhost:80
# or use XAMPP
```

## Running Tests

### Run All Tests
```bash
pytest
```

### Run Specific Test File
```bash
pytest test_frontend.py
pytest test_backend.py
pytest test_public_pages.py
```

### Run Tests by Marker
```bash
# Frontend tests only
pytest -m frontend

# Backend tests only
pytest -m backend

# Authentication tests
pytest -m auth

# Performance tests
pytest -m performance
```

### Run with Specific Options
```bash
# Verbose output
pytest -v

# Show print statements
pytest -s

# Stop on first failure
pytest -x

# Run in parallel (requires pytest-xdist)
pytest -n auto

# Generate HTML report
pytest --html=reports/test_report.html --self-contained-html
```

### Run Tests in Headless Mode
```bash
# Edit conftest.py and uncomment the headless option
# Or set environment variable:
# HEADLESS=true pytest
```

## Test Structure

### conftest.py
Contains pytest fixtures and base Selenium utilities:
- `driver`: WebDriver instance
- `browser`: SeleniumBase helper with common utilities
- `frontend_browser`: Helper for frontend tests
- `backend_browser`: Helper for backend tests
- Base URLs from environment

### test_frontend.py
Tests for the React frontend application:
- Page load and navigation
- Form interactions
- API integration
- Console errors
- Performance metrics
- LocalStorage/SessionStorage

### test_backend.py
Tests for the backend API:
- API endpoints
- Authentication endpoints
- Data endpoints
- Database connectivity
- Response times
- CORS configuration

### test_public_pages.py
Tests for public PHP pages:
- Page accessibility
- Authentication pages
- Navigation and links
- Page content
- Responsiveness
- Element loading

## Report Generation

Tests generate HTML reports in `reports/` directory:
- `test_report.html`: Complete test results with screenshots (if configured)
- `test.log`: Detailed debug log

View the HTML report in a browser:
```bash
# Windows
start reports/test_report.html

# Mac
open reports/test_report.html

# Linux
xdg-open reports/test_report.html
```

## Configuration Options

### WebDriver Options
Edit `conftest.py` to customize:
- Browser headless mode
- Window size
- Implicit/explicit waits
- Download directories

### Test Timeouts
Modify `pytest.ini`:
```ini
timeout = 300  # seconds
```

### Logging
Configure logging level in `.env`:
```
LOG_LEVEL=DEBUG  # INFO, DEBUG, ERROR
```

## Troubleshooting

### WebDriver Issues
```bash
# Update chromedriver
pip install --upgrade webdriver-manager
```

### Port Already in Use
```bash
# Find process on port
netstat -ano | findstr :5173  # Windows
lsof -i :5173  # Mac/Linux
```

### Connection Refused
1. Check if services are running
2. Verify URLs in `.env`
3. Check firewall settings

### Test Timeouts
- Increase timeout in `pytest.ini`
- Increase explicit waits in fixtures
- Check system performance

## CI/CD Integration

### GitHub Actions Example
```yaml
name: Selenium Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: actions/setup-python@v2
        with:
          python-version: '3.11'
      - run: pip install -r selenium_tests/requirements.txt
      - run: pytest selenium_tests/ --html=reports/test_report.html
      - uses: actions/upload-artifact@v2
        with:
          name: test-results
          path: reports/
```

## Best Practices

1. **Always use explicit waits** for elements
2. **Wait for API responses** before assertions
3. **Test one feature per test** for clarity
4. **Use descriptive test names** explaining what is tested
5. **Clean up resources** in fixtures (use yield)
6. **Mock external APIs** when possible
7. **Run tests in order** of importance
8. **Check for flaky tests** and stabilize them

## Example Test Cases

### Testing Form Submission
```python
def test_form_submission(browser):
    browser.navigate("/form")
    browser.send_keys(By.ID, "email", "test@example.com")
    browser.send_keys(By.ID, "password", "password123")
    browser.click_element(By.ID, "submit-btn")
    browser.wait_for_text(By.ID, "success-msg", "Success")
```

### Testing API Call
```python
def test_api_call(backend_url):
    response = requests.get(f"{backend_url}/api/endpoint")
    assert response.status_code == 200
    data = response.json()
    assert "key" in data
```

### Testing Element Visibility
```python
def test_visibility(browser):
    browser.navigate("/page")
    assert browser.is_element_visible(By.ID, "element-id")
```

## Contributing

1. Add new tests to appropriate test file
2. Follow naming convention: `test_<feature>`
3. Use descriptive docstrings
4. Run all tests before submitting
5. Update this README if adding new features

## Resources

- [Selenium Documentation](https://www.selenium.dev/documentation/)
- [Pytest Documentation](https://docs.pytest.org/)
- [Python WebDriver API](https://selenium-python.readthedocs.io/)
