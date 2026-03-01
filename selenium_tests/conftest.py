"""
Pytest configuration and fixtures for Selenium tests
"""
import pytest
import os
from selenium import webdriver
from selenium.webdriver.support.wait import WebDriverWait
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from dotenv import load_dotenv
import time

# Load environment variables
env_path = os.path.join(os.path.dirname(__file__), '..', '.env')
if not os.path.exists(env_path):
    env_path = os.path.join(os.path.dirname(__file__), '.env')
load_dotenv(env_path)


class SeleniumBase:
    """Base class with common Selenium utilities"""
    
    def __init__(self, driver, base_url):
        self.driver = driver
        self.base_url = base_url
        self.wait = WebDriverWait(driver, 10)
    
    def navigate(self, path=""):
        """Navigate to a URL"""
        if not path:
            url = self.base_url
        elif path.startswith("http"):
            url = path
        elif path.startswith("/"):
            url = f"{self.base_url}{path}"
        else:
            url = f"{self.base_url}/{path}"
        self.driver.get(url)
        time.sleep(1)  # Wait for page to load
    
    def find_element(self, by, value):
        """Find element with explicit wait"""
        return self.wait.until(EC.presence_of_element_located((by, value)))
    
    def click_element(self, by, value):
        """Click element with explicit wait"""
        element = self.wait.until(EC.element_to_be_clickable((by, value)))
        element.click()
    
    def send_keys(self, by, value, text):
        """Send keys to element"""
        element = self.find_element(by, value)
        element.clear()
        element.send_keys(text)
    
    def wait_for_text(self, by, value, text, timeout=10):
        """Wait for element to contain specific text"""
        WebDriverWait(self.driver, timeout).until(
            EC.text_to_be_present_in_element((by, value), text)
        )
    
    def get_text(self, by, value):
        """Get element text"""
        return self.find_element(by, value).text
    
    def is_element_visible(self, by, value, timeout=5):
        """Check if element is visible"""
        try:
            WebDriverWait(self.driver, timeout).until(
                EC.visibility_of_element_located((by, value))
            )
            return True
        except:
            return False
    
    def wait_for_page_load(self, timeout=10):
        """Wait for page to fully load"""
        WebDriverWait(self.driver, timeout).until(
            lambda driver: driver.execute_script("return document.readyState") == "complete"
        )


@pytest.fixture(scope="session")
def base_url():
    """Get base URL from environment or use default"""
    return os.getenv("BASE_URL", "http://localhost")


@pytest.fixture(scope="session")
def php_url():
    """Get PHP API URL"""
    return os.getenv("PHP_API_URL", "http://localhost")


@pytest.fixture(scope="session")
def db_config():
    """Get database configuration"""
    return {
        'host': os.getenv("DB_HOST", "localhost"),
        'port': int(os.getenv("DB_PORT", "3306")),
        'user': os.getenv("DB_USER", "root"),
        'password': os.getenv("DB_PASSWORD", ""),
        'database': os.getenv("DB_NAME", "student_feedback")
    }


@pytest.fixture
def driver():
    """Create and cleanup Selenium WebDriver"""
    from selenium.webdriver.chrome.service import Service
    from webdriver_manager.chrome import ChromeDriverManager

    options = webdriver.ChromeOptions()
    headless = os.getenv("HEADLESS", "true").lower() == "true"

    if headless:
        options.add_argument("--headless=new")

    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--disable-gpu")
    options.add_argument("--disable-extensions")
    options.add_argument("--disable-popup-blocking")
    options.add_argument("--window-size=1920,1080")
    options.add_argument("--log-level=3")
    options.add_experimental_option("excludeSwitches", ["enable-logging"])

    service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=options)

    # Set implicit wait
    implicit_wait = int(os.getenv("IMPLICIT_WAIT", "10"))
    driver.implicitly_wait(implicit_wait)

    # Set page load timeout
    page_load_timeout = int(os.getenv("PAGE_LOAD_TIMEOUT", "30"))
    driver.set_page_load_timeout(page_load_timeout)

    yield driver

    driver.quit()


@pytest.fixture
def browser(driver, base_url):
    """Create browser helper with base utilities"""
    return SeleniumBase(driver, base_url)


@pytest.fixture
def php_browser(driver, php_url):
    """Create PHP browser helper"""
    return SeleniumBase(driver, php_url)
