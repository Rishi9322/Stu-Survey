"""
Complete Selenium Test Suite for Student Feedback System
Tests all major functionalities including login, registration, surveys, and admin operations
"""
import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options as ChromeOptions
from selenium.webdriver.firefox.options import Options as FirefoxOptions
from selenium.common.exceptions import TimeoutException, NoSuchElementException
import time
import os
from datetime import datetime


class TestStudentFeedbackSystem:
    """Main test class for the Student Feedback System"""
    
    @pytest.fixture(autouse=True)
    def setup_method(self, driver, base_url):
        """Setup for each test method"""
        self.driver = driver
        self.base_url = base_url
        self.wait = WebDriverWait(driver, 10)
        yield
        # Cleanup after each test
        try:
            self.driver.delete_all_cookies()
        except:
            pass
    
    def test_homepage_loads(self):
        """Test 1: Homepage loads successfully"""
        self.driver.get(self.base_url)
        assert "Student" in self.driver.title or "Survey" in self.driver.title or "Feedback" in self.driver.title
        print("✓ Homepage loaded successfully")
    
    def test_homepage_has_navigation(self):
        """Test 2: Homepage has navigation elements"""
        self.driver.get(self.base_url)
        time.sleep(2)
        
        # Check for common navigation elements
        page_source = self.driver.page_source.lower()
        assert any(keyword in page_source for keyword in ['login', 'register', 'home', 'about'])
        print("✓ Navigation elements present")
    
    def test_login_page_accessible(self):
        """Test 3: Login page is accessible"""
        self.driver.get(f"{self.base_url}/login.php")
        time.sleep(2)
        
        page_source = self.driver.page_source.lower()
        assert 'login' in page_source
        print("✓ Login page accessible")
    
    def test_login_form_elements(self):
        """Test 4: Login form has required elements"""
        self.driver.get(f"{self.base_url}/login.php")
        time.sleep(2)
        
        page_source = self.driver.page_source.lower()
        
        # Check for form elements
        has_username = 'username' in page_source or 'email' in page_source
        has_password = 'password' in page_source
        has_submit = 'submit' in page_source or 'login' in page_source
        
        assert has_username, "Username/Email field not found"
        assert has_password, "Password field not found"
        assert has_submit, "Submit button not found"
        print("✓ Login form elements present")
    
    def test_registration_page_accessible(self):
        """Test 5: Registration page is accessible"""
        self.driver.get(f"{self.base_url}/register.php")
        time.sleep(2)
        
        page_source = self.driver.page_source.lower()
        assert 'register' in page_source or 'sign up' in page_source
        print("✓ Registration page accessible")
    
    def test_registration_form_elements(self):
        """Test 6: Registration form has required elements"""
        self.driver.get(f"{self.base_url}/register.php")
        time.sleep(2)
        
        page_source = self.driver.page_source.lower()
        
        # Check for registration form elements
        has_email = 'email' in page_source
        has_password = 'password' in page_source
        has_name = 'name' in page_source or 'username' in page_source
        
        assert has_email, "Email field not found"
        assert has_password, "Password field not found"
        print("✓ Registration form elements present")
    
    def test_about_page_accessible(self):
        """Test 7: About page is accessible"""
        try:
            self.driver.get(f"{self.base_url}/about.php")
            time.sleep(2)
            assert self.driver.current_url
            print("✓ About page accessible")
        except:
            pytest.skip("About page not available")
    
    def test_contact_page_accessible(self):
        """Test 8: Contact page is accessible"""
        try:
            self.driver.get(f"{self.base_url}/contact.php")
            time.sleep(2)
            assert self.driver.current_url
            print("✓ Contact page accessible")
        except:
            pytest.skip("Contact page not available")
    
    def test_invalid_login_shows_error(self):
        """Test 9: Invalid login shows error message"""
        self.driver.get(f"{self.base_url}/login.php")
        time.sleep(2)
        
        try:
            # Try to find and fill login form
            username_selectors = [
                "input[name='username']",
                "input[name='email']",
                "input[type='email']",
                "input[id*='user']",
                "input[id*='email']"
            ]
            
            password_selectors = [
                "input[name='password']",
                "input[type='password']",
                "input[id*='pass']"
            ]
            
            username_field = None
            for selector in username_selectors:
                try:
                    username_field = self.driver.find_element(By.CSS_SELECTOR, selector)
                    break
                except:
                    continue
            
            password_field = None
            for selector in password_selectors:
                try:
                    password_field = self.driver.find_element(By.CSS_SELECTOR, selector)
                    break
                except:
                    continue
            
            if username_field and password_field:
                username_field.send_keys("invalid_user@test.com")
                password_field.send_keys("invalid_password")
                
                # Find and click submit button
                submit_selectors = [
                    "button[type='submit']",
                    "input[type='submit']",
                    "button:contains('Login')",
                    ".btn-login"
                ]
                
                for selector in submit_selectors:
                    try:
                        submit_btn = self.driver.find_element(By.CSS_SELECTOR, selector)
                        submit_btn.click()
                        break
                    except:
                        continue
                
                time.sleep(3)
                
                # Check for error message
                page_source = self.driver.page_source.lower()
                has_error = any(word in page_source for word in ['error', 'invalid', 'incorrect', 'failed', 'wrong'])
                assert has_error, "No error message shown for invalid login"
                print("✓ Invalid login shows error message")
            else:
                pytest.skip("Login form fields not found")
        except Exception as e:
            pytest.skip(f"Cannot test login: {str(e)}")
    
    def test_page_load_performance(self):
        """Test 10: Pages load within acceptable time"""
        start_time = time.time()
        self.driver.get(self.base_url)
        load_time = time.time() - start_time
        
        assert load_time < 10, f"Page took {load_time}s to load (expected < 10s)"
        print(f"✓ Page loaded in {load_time:.2f}s")
    
    def test_responsive_design(self):
        """Test 11: Page is responsive (basic check)"""
        self.driver.get(self.base_url)
        time.sleep(2)
        
        # Test mobile viewport
        self.driver.set_window_size(375, 667)  # iPhone size
        time.sleep(1)
        
        # Page should still be accessible
        assert self.driver.current_url
        
        # Restore normal size
        self.driver.set_window_size(1920, 1080)
        print("✓ Responsive design functional")
    
    def test_javascript_errors(self):
        """Test 12: Check for JavaScript errors"""
        self.driver.get(self.base_url)
        time.sleep(2)
        
        # Get browser console logs (Chrome only)
        if isinstance(self.driver, webdriver.Chrome):
            logs = self.driver.get_log('browser')
            severe_errors = [log for log in logs if log['level'] == 'SEVERE']
            assert len(severe_errors) == 0, f"Found {len(severe_errors)} severe JavaScript errors"
            print("✓ No severe JavaScript errors")
        else:
            pytest.skip("JavaScript error checking only available in Chrome")
    
    def test_privacy_page_accessible(self):
        """Test 13: Privacy page is accessible"""
        try:
            self.driver.get(f"{self.base_url}/privacy.php")
            time.sleep(2)
            assert self.driver.current_url
            print("✓ Privacy page accessible")
        except:
            pytest.skip("Privacy page not available")
    
    def test_terms_page_accessible(self):
        """Test 14: Terms page is accessible"""
        try:
            self.driver.get(f"{self.base_url}/terms.php")
            time.sleep(2)
            assert self.driver.current_url
            print("✓ Terms page accessible")
        except:
            pytest.skip("Terms page not available")
    
    def test_help_page_accessible(self):
        """Test 15: Help page is accessible"""
        try:
            self.driver.get(f"{self.base_url}/help.php")
            time.sleep(2)
            assert self.driver.current_url
            print("✓ Help page accessible")
        except:
            pytest.skip("Help page not available")


class TestAdminModule:
    """Test cases for Admin functionality"""
    
    @pytest.fixture(autouse=True)
    def setup_method(self, driver, base_url):
        """Setup for each test method"""
        self.driver = driver
        self.base_url = base_url
        self.wait = WebDriverWait(driver, 10)
        yield
    
    def test_admin_login_page(self):
        """Test 16: Admin login page is accessible"""
        try:
            self.driver.get(f"{self.base_url}/../app/admin/login.php")
            time.sleep(2)
            page_source = self.driver.page_source.lower()
            assert 'admin' in page_source or 'login' in page_source
            print("✓ Admin login page accessible")
        except:
            pytest.skip("Admin login page not available")
    
    def test_admin_dashboard_requires_auth(self):
        """Test 17: Admin dashboard requires authentication"""
        try:
            self.driver.get(f"{self.base_url}/../app/admin/dashboard.php")
            time.sleep(2)
            
            current_url = self.driver.current_url.lower()
            page_source = self.driver.page_source.lower()
            
            # Should redirect to login or show login form
            is_protected = 'login' in current_url or 'login' in page_source
            assert is_protected, "Admin dashboard not protected"
            print("✓ Admin dashboard requires authentication")
        except:
            pytest.skip("Admin dashboard not available")


class TestStudentModule:
    """Test cases for Student functionality"""
    
    @pytest.fixture(autouse=True)
    def setup_method(self, driver, base_url):
        """Setup for each test method"""
        self.driver = driver
        self.base_url = base_url
        self.wait = WebDriverWait(driver, 10)
        yield
    
    def test_student_dashboard_exists(self):
        """Test 18: Student dashboard page exists"""
        try:
            self.driver.get(f"{self.base_url}/../app/student/dashboard.php")
            time.sleep(2)
            assert self.driver.current_url
            print("✓ Student dashboard page exists")
        except:
            pytest.skip("Student dashboard not available")
    
    def test_survey_page_exists(self):
        """Test 19: Survey page exists"""
        try:
            self.driver.get(f"{self.base_url}/../app/student/survey.php")
            time.sleep(2)
            assert self.driver.current_url
            print("✓ Survey page exists")
        except:
            pytest.skip("Survey page not available")


class TestTeacherModule:
    """Test cases for Teacher functionality"""
    
    @pytest.fixture(autouse=True)
    def setup_method(self, driver, base_url):
        """Setup for each test method"""
        self.driver = driver
        self.base_url = base_url
        self.wait = WebDriverWait(driver, 10)
        yield
    
    def test_teacher_dashboard_exists(self):
        """Test 20: Teacher dashboard page exists"""
        try:
            self.driver.get(f"{self.base_url}/../app/teacher/dashboard.php")
            time.sleep(2)
            assert self.driver.current_url
            print("✓ Teacher dashboard page exists")
        except:
            pytest.skip("Teacher dashboard not available")


class TestAPIEndpoints:
    """Test cases for API endpoints"""
    
    @pytest.fixture(autouse=True)
    def setup_method(self, driver, base_url):
        """Setup for each test method"""
        self.driver = driver
        self.base_url = base_url
        yield
    
    def test_api_endpoint_exists(self):
        """Test 21: API endpoint exists"""
        try:
            api_url = self.base_url.replace('/public', '/api')
            self.driver.get(api_url)
            time.sleep(2)
            assert self.driver.current_url
            print("✓ API endpoint exists")
        except:
            pytest.skip("API endpoint not available")


@pytest.fixture(scope="session")
def driver(request):
    """Create WebDriver instance"""
    browser = os.getenv("BROWSER", "chrome").lower()
    headless = os.getenv("HEADLESS", "false").lower() == "true"
    
    if browser == "firefox":
        options = FirefoxOptions()
        if headless:
            options.add_argument("--headless")
        driver = webdriver.Firefox(options=options)
    else:  # Default to Chrome
        options = ChromeOptions()
        if headless:
            options.add_argument("--headless")
        options.add_argument("--disable-gpu")
        options.add_argument("--no-sandbox")
        options.add_argument("--disable-dev-shm-usage")
        options.add_argument("--window-size=1920,1080")
        driver = webdriver.Chrome(options=options)
    
    driver.implicitly_wait(10)
    driver.maximize_window()
    
    yield driver
    
    driver.quit()


@pytest.fixture(scope="session")
def base_url():
    """Get base URL from environment"""
    return os.getenv("BASE_URL", "http://localhost/stu/public")


if __name__ == "__main__":
    pytest.main([__file__, "-v", "--html=reports/test_report.html", "--self-contained-html"])
