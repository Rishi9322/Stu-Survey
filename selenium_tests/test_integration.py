"""
Integration tests for the entire system
"""
import pytest
import requests
from selenium.webdriver.common.by import By
import time


class TestSystemIntegration:
    """Test system integration"""
    
    def test_web_server_running(self, base_url):
        """Test that web server is running"""
        try:
            response = requests.get(base_url, timeout=5)
            assert response.status_code < 500
        except requests.exceptions.ConnectionError:
            pytest.fail("Web server is not running")
    
    def test_multiple_pages_load(self, browser):
        """Test that multiple pages can be loaded"""
        pages = [
            "index.php",
            "about.php",
            "features.php"
        ]
        
        for page in pages:
            browser.navigate(page)
            browser.wait_for_page_load()
            
            body = browser.driver.find_element(By.TAG_NAME, "body")
            assert body is not None, f"Page {page} should load"
    
    def test_page_transitions(self, browser):
        """Test transitioning between pages"""
        # Load first page
        browser.navigate("index.php")
        browser.wait_for_page_load()
        url1 = browser.driver.current_url
        
        # Load second page
        browser.navigate("about.php")
        browser.wait_for_page_load()
        url2 = browser.driver.current_url
        
        # Load third page
        browser.navigate("features.php")
        browser.wait_for_page_load()
        url3 = browser.driver.current_url
        
        # All URLs should be different
        assert url1 != url2
        assert url2 != url3
        assert url1 != url3


class TestEndToEndScenarios:
    """Test complete end-to-end scenarios"""
    
    def test_homepage_navigation_scenario(self, browser):
        """Test homepage navigation scenario"""
        # Start at homepage
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        # Verify page loaded
        body = browser.driver.find_element(By.TAG_NAME, "body")
        assert body is not None
        
        # Verify current URL is correct
        assert "localhost" in browser.driver.current_url
    
    def test_page_content_scenario(self, browser):
        """Test that pages have content"""
        pages = ["index.php", "about.php"]
        
        for page in pages:
            browser.navigate(page)
            browser.wait_for_page_load()
            
            # Check for content
            html = browser.driver.page_source
            assert len(html) > 100, f"Page {page} should have content"
    
    def test_browser_navigation_scenario(self, browser):
        """Test browser navigation buttons"""
        # Navigate to page 1
        browser.navigate("index.php")
        browser.wait_for_page_load()
        
        # Navigate to page 2
        browser.navigate("about.php")
        browser.wait_for_page_load()
        
        # Go back
        browser.driver.back()
        browser.wait_for_page_load()
        
        # Should be back at page 1
        assert "localhost" in browser.driver.current_url


class TestSystemAvailability:
    """Test system availability"""
    
    def test_system_is_accessible(self, base_url):
        """Test that system is accessible"""
        try:
            response = requests.get(base_url, timeout=5)
            assert True
        except requests.exceptions.ConnectionError:
            pytest.fail("System is not accessible")
    
    def test_multiple_endpoints_accessible(self, base_url):
        """Test multiple endpoints are accessible"""
        endpoints = [
            "index.php",
            "login.php",
            "register.php"
        ]
        
        for endpoint in endpoints:
            try:
                response = requests.get(f"{base_url}/{endpoint}", timeout=5)
                assert response.status_code < 500
            except requests.exceptions.ConnectionError:
                pytest.fail(f"Endpoint {endpoint} not accessible")
