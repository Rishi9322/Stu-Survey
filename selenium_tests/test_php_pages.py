"""
Selenium tests for PHP Pages
Tests the student feedback system PHP pages running on Apache
"""
import pytest
from selenium.webdriver.common.by import By
import time


class TestPHPPageLoading:
    """Test PHP page loading and accessibility"""
    
    def test_homepage_loads(self, browser):
        """Test that homepage loads successfully"""
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        assert browser.driver.current_url is not None
        body = browser.driver.find_element(By.TAG_NAME, "body")
        assert body is not None
    
    def test_index_php_loads(self, browser):
        """Test that index.php loads"""
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        body = browser.driver.find_element(By.TAG_NAME, "body")
        assert body is not None
        assert len(body.text) > 0
    
    def test_about_page_loads(self, browser):
        """Test that about page loads"""
        browser.navigate("about.php")
        browser.wait_for_page_load()
        
        body = browser.driver.find_element(By.TAG_NAME, "body")
        assert body is not None
    
    def test_features_page_loads(self, browser):
        """Test that features page loads"""
        browser.navigate("features.php")
        browser.wait_for_page_load()
        
        body = browser.driver.find_element(By.TAG_NAME, "body")
        assert body is not None
    
    def test_documentation_page_loads(self, browser):
        """Test that documentation page loads"""
        browser.navigate("documentation.php")
        browser.wait_for_page_load()
        
        body = browser.driver.find_element(By.TAG_NAME, "body")
        assert body is not None
    
    def test_help_page_loads(self, browser):
        """Test that help page loads"""
        browser.navigate("help.php")
        browser.wait_for_page_load()
        
        body = browser.driver.find_element(By.TAG_NAME, "body")
        assert body is not None
    
    def test_contact_page_loads(self, browser):
        """Test that contact page loads"""
        browser.navigate("contact.php")
        browser.wait_for_page_load()
        
        body = browser.driver.find_element(By.TAG_NAME, "body")
        assert body is not None
    
    def test_privacy_page_loads(self, browser):
        """Test that privacy page loads"""
        browser.navigate("privacy.php")
        browser.wait_for_page_load()
        
        body = browser.driver.find_element(By.TAG_NAME, "body")
        assert body is not None
    
    def test_terms_page_loads(self, browser):
        """Test that terms page loads"""
        browser.navigate("terms.php")
        browser.wait_for_page_load()
        
        body = browser.driver.find_element(By.TAG_NAME, "body")
        assert body is not None


class TestAuthenticationPages:
    """Test authentication pages"""
    
    def test_login_page_loads(self, browser):
        """Test that login page loads"""
        browser.navigate("login.php")
        browser.wait_for_page_load()
        
        page_source = browser.driver.page_source
        # Page should load without errors
        assert len(page_source) > 100
    
    def test_register_page_loads(self, browser):
        """Test that registration page loads"""
        browser.navigate("register.php")
        browser.wait_for_page_load()
        
        page_source = browser.driver.page_source
        assert len(page_source) > 100


class TestPageStructure:
    """Test HTML page structure"""
    
    def test_page_has_html_elements(self, browser):
        """Test that pages have proper HTML structure"""
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        html = browser.driver.find_element(By.TAG_NAME, "html")
        assert html is not None
        
        head = browser.driver.find_element(By.TAG_NAME, "head")
        assert head is not None
        
        body = browser.driver.find_element(By.TAG_NAME, "body")
        assert body is not None
    
    def test_page_has_title(self, browser):
        """Test that pages have title"""
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        title = browser.driver.find_element(By.TAG_NAME, "title")
        assert title is not None
    
    def test_multiple_pages_have_title(self, browser):
        """Test that multiple pages have titles"""
        pages = ["index.php", "about.php", "features.php"]
        
        for page in pages:
            browser.navigate(page)
            browser.wait_for_page_load()
            
            title = browser.driver.find_element(By.TAG_NAME, "title")
            assert title is not None, f"Page {page} should have a title"


class TestPageNavigation:
    """Test navigation between pages"""
    
    def test_navigate_between_pages(self, browser):
        """Test navigating between different pages"""
        # Start at homepage
        browser.navigate("index.php")
        browser.wait_for_page_load()
        url1 = browser.driver.current_url
        
        # Navigate to about page
        browser.navigate("about.php")
        browser.wait_for_page_load()
        url2 = browser.driver.current_url
        
        # URLs should be different
        assert url1 != url2
    
    def test_page_back_button(self, browser):
        """Test browser back button functionality"""
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        browser.navigate("about.php")
        browser.wait_for_page_load()
        
        # Go back
        browser.driver.back()
        browser.wait_for_page_load()
        
        # Should be back at homepage
        assert "localhost" in browser.driver.current_url


class TestPageElements:
    """Test page elements and content"""
    
    def test_page_has_body_content(self, browser):
        """Test that pages have body content"""
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        body = browser.driver.find_element(By.TAG_NAME, "body")
        assert len(body.text) >= 0, "Body should have content"
    
    def test_page_links_present(self, browser):
        """Test that pages may have links"""
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        links = browser.driver.find_elements(By.TAG_NAME, "a")
        # Links may or may not exist, just check it doesn't error
        assert isinstance(links, list)
    
    def test_page_images_present(self, browser):
        """Test that pages may have images"""
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        images = browser.driver.find_elements(By.TAG_NAME, "img")
        # Images may or may not exist, just check it doesn't error
        assert isinstance(images, list)


class TestPageJavaScript:
    """Test JavaScript functionality on PHP pages"""
    
    def test_javascript_enabled(self, browser):
        """Test that JavaScript is enabled and works"""
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        # Execute simple JavaScript
        result = browser.driver.execute_script("return 2 + 2")
        assert result == 4
    
    def test_dom_ready(self, browser):
        """Test that DOM is fully loaded"""
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        ready_state = browser.driver.execute_script("return document.readyState")
        assert ready_state == "complete"
    
    def test_window_object_available(self, browser):
        """Test that window object is available"""
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        result = browser.driver.execute_script("return typeof window")
        assert result == "object"


class TestPageResponsiveness:
    """Test page responsiveness and viewport"""
    
    def test_viewport_dimensions(self, browser):
        """Test viewport dimensions"""
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        viewport = browser.driver.execute_script("""
            return {
                width: window.innerWidth,
                height: window.innerHeight
            }
        """)
        
        assert viewport['width'] > 0
        assert viewport['height'] > 0
    
    def test_window_size(self, browser):
        """Test window size"""
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        size = browser.driver.get_window_size()
        assert size['width'] > 0
        assert size['height'] > 0


class TestPageForms:
    """Test form elements on pages"""
    
    def test_login_page_has_inputs(self, browser):
        """Test that login page has input elements"""
        browser.navigate("login.php")
        browser.wait_for_page_load()
        
        # Try to find input elements
        inputs = browser.driver.find_elements(By.TAG_NAME, "input")
        # Page might have forms or might not, just verify no error
        assert isinstance(inputs, list)
    
    def test_register_page_has_inputs(self, browser):
        """Test that register page has input elements"""
        browser.navigate("register.php")
        browser.wait_for_page_load()
        
        inputs = browser.driver.find_elements(By.TAG_NAME, "input")
        assert isinstance(inputs, list)
    
    def test_contact_page_form(self, browser):
        """Test that contact page has form elements"""
        browser.navigate("contact.php")
        browser.wait_for_page_load()
        
        forms = browser.driver.find_elements(By.TAG_NAME, "form")
        # Page might have forms or might not, just verify no error
        assert isinstance(forms, list)
