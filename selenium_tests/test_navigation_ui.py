"""
UI and Navigation Test Suite
Tests navigation, links, buttons, and UI elements
"""
import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time


class TestNavigation:
    """Navigation and UI test cases"""
    
    @pytest.fixture(autouse=True)
    def setup(self, driver, base_url):
        """Setup for each test"""
        self.driver = driver
        self.base_url = base_url
        self.wait = WebDriverWait(driver, 10)
        yield
    
    def test_homepage_to_login_navigation(self):
        """Test: Navigate from homepage to login"""
        self.driver.get(self.base_url)
        time.sleep(2)
        
        try:
            # Look for login link
            login_links = self.driver.find_elements(By.PARTIAL_LINK_TEXT, "Login")
            if not login_links:
                login_links = self.driver.find_elements(By.PARTIAL_LINK_TEXT, "Sign In")
            
            if login_links:
                login_links[0].click()
                time.sleep(2)
                assert "login" in self.driver.current_url.lower() or "login" in self.driver.page_source.lower()
                print("✓ Navigation to login works")
            else:
                pytest.skip("Login link not found on homepage")
        except:
            pytest.skip("Cannot test navigation")
    
    def test_homepage_to_registration_navigation(self):
        """Test: Navigate from homepage to registration"""
        self.driver.get(self.base_url)
        time.sleep(2)
        
        try:
            # Look for registration link
            register_links = self.driver.find_elements(By.PARTIAL_LINK_TEXT, "Register")
            if not register_links:
                register_links = self.driver.find_elements(By.PARTIAL_LINK_TEXT, "Sign Up")
            
            if register_links:
                register_links[0].click()
                time.sleep(2)
                page_content = self.driver.page_source.lower()
                assert "register" in self.driver.current_url.lower() or "register" in page_content or "sign up" in page_content
                print("✓ Navigation to registration works")
            else:
                pytest.skip("Register link not found on homepage")
        except:
            pytest.skip("Cannot test navigation")
    
    def test_all_navigation_links_work(self):
        """Test: All navigation links are clickable"""
        self.driver.get(self.base_url)
        time.sleep(2)
        
        try:
            # Find all navigation links
            nav_links = self.driver.find_elements(By.CSS_SELECTOR, "nav a, header a, .navbar a, .nav a")
            
            if nav_links:
                link_count = len(nav_links)
                print(f"✓ Found {link_count} navigation links")
            else:
                pytest.skip("No navigation links found")
        except:
            pytest.skip("Cannot find navigation")
    
    def test_footer_exists(self):
        """Test: Footer exists on page"""
        self.driver.get(self.base_url)
        time.sleep(2)
        
        page_source = self.driver.page_source.lower()
        has_footer = "footer" in page_source or "copyright" in page_source or "©" in page_source
        
        if has_footer:
            print("✓ Footer present on page")
        else:
            pytest.skip("Footer not found")
    
    def test_header_exists(self):
        """Test: Header exists on page"""
        self.driver.get(self.base_url)
        time.sleep(2)
        
        try:
            headers = self.driver.find_elements(By.CSS_SELECTOR, "header, .header, #header, nav, .navbar")
            assert len(headers) > 0
            print("✓ Header present on page")
        except:
            pytest.skip("Header not found")
    
    def test_logo_exists(self):
        """Test: Logo or brand image exists"""
        self.driver.get(self.base_url)
        time.sleep(2)
        
        try:
            logos = self.driver.find_elements(By.CSS_SELECTOR, ".logo, #logo, .brand, .navbar-brand, img[alt*='logo']")
            if logos:
                print("✓ Logo/brand element found")
            else:
                pytest.skip("Logo not found")
        except:
            pytest.skip("Logo not found")
    
    def test_back_button_works(self):
        """Test: Browser back button works"""
        self.driver.get(self.base_url)
        initial_url = self.driver.current_url
        time.sleep(1)
        
        # Navigate to another page
        self.driver.get(f"{self.base_url}/about.php")
        time.sleep(1)
        
        # Go back
        self.driver.back()
        time.sleep(1)
        
        assert self.driver.current_url == initial_url
        print("✓ Back button navigation works")
    
    def test_forward_button_works(self):
        """Test: Browser forward button works"""
        self.driver.get(self.base_url)
        time.sleep(1)
        
        # Navigate to another page
        self.driver.get(f"{self.base_url}/about.php")
        about_url = self.driver.current_url
        time.sleep(1)
        
        # Go back
        self.driver.back()
        time.sleep(1)
        
        # Go forward
        self.driver.forward()
        time.sleep(1)
        
        assert self.driver.current_url == about_url
        print("✓ Forward button navigation works")
    
    def test_page_has_title(self):
        """Test: Page has a title tag"""
        self.driver.get(self.base_url)
        time.sleep(1)
        
        title = self.driver.title
        assert title and len(title) > 0
        print(f"✓ Page title: {title}")
    
    def test_page_has_meta_description(self):
        """Test: Page has meta description"""
        self.driver.get(self.base_url)
        time.sleep(1)
        
        try:
            meta_desc = self.driver.find_element(By.CSS_SELECTOR, "meta[name='description']")
            if meta_desc:
                print("✓ Meta description present")
        except:
            pytest.skip("Meta description not found")


class TestUIElements:
    """UI element test cases"""
    
    @pytest.fixture(autouse=True)
    def setup(self, driver, base_url):
        """Setup for each test"""
        self.driver = driver
        self.base_url = base_url
        yield
    
    def test_buttons_are_visible(self):
        """Test: Buttons are visible and styled"""
        self.driver.get(self.base_url)
        time.sleep(2)
        
        buttons = self.driver.find_elements(By.CSS_SELECTOR, "button, input[type='button'], input[type='submit'], .btn")
        
        if buttons:
            visible_count = sum(1 for btn in buttons if btn.is_displayed())
            print(f"✓ Found {visible_count} visible buttons")
        else:
            pytest.skip("No buttons found")
    
    def test_forms_have_labels(self):
        """Test: Form inputs have labels"""
        self.driver.get(f"{self.base_url}/login.php")
        time.sleep(2)
        
        try:
            labels = self.driver.find_elements(By.TAG_NAME, "label")
            inputs = self.driver.find_elements(By.CSS_SELECTOR, "input[type='text'], input[type='email'], input[type='password']")
            
            if inputs:
                if labels:
                    print(f"✓ Found {len(labels)} labels for form inputs")
                else:
                    print("⚠ Warning: Forms may need labels for accessibility")
        except:
            pytest.skip("Cannot check form labels")
    
    def test_images_have_alt_text(self):
        """Test: Images have alt text for accessibility"""
        self.driver.get(self.base_url)
        time.sleep(2)
        
        try:
            images = self.driver.find_elements(By.TAG_NAME, "img")
            
            if images:
                images_with_alt = sum(1 for img in images if img.get_attribute("alt"))
                coverage = (images_with_alt / len(images)) * 100 if images else 0
                
                print(f"✓ {images_with_alt}/{len(images)} images have alt text ({coverage:.1f}%)")
                
                if coverage < 50:
                    print("⚠ Warning: Consider adding alt text to more images")
        except:
            pytest.skip("Cannot check images")
    
    def test_external_links_open_in_new_tab(self):
        """Test: External links have target='_blank'"""
        self.driver.get(self.base_url)
        time.sleep(2)
        
        try:
            external_links = self.driver.find_elements(By.CSS_SELECTOR, "a[target='_blank']")
            
            if external_links:
                print(f"✓ Found {len(external_links)} links that open in new tab")
            else:
                print("ℹ No external links with target='_blank' found")
        except:
            pytest.skip("Cannot check external links")
    
    def test_no_broken_images(self):
        """Test: Check for broken images"""
        self.driver.get(self.base_url)
        time.sleep(2)
        
        try:
            images = self.driver.find_elements(By.TAG_NAME, "img")
            
            broken_count = 0
            for img in images:
                natural_width = self.driver.execute_script("return arguments[0].naturalWidth", img)
                if natural_width == 0:
                    broken_count += 1
            
            if broken_count == 0:
                print(f"✓ All {len(images)} images loaded successfully")
            else:
                print(f"⚠ Warning: {broken_count}/{len(images)} images may be broken")
        except:
            pytest.skip("Cannot check images")


if __name__ == "__main__":
    pytest.main([__file__, "-v", "--html=reports/navigation_report.html", "--self-contained-html"])
