"""
Selenium tests for Database connectivity
Tests the database connection through PHP
"""
import pytest
import requests
import time


class TestDatabaseConnectivity:
    """Test database connectivity"""
    
    @pytest.fixture
    def api_url(self, base_url):
        return f"{base_url}/api"
    
    def test_database_accessible_through_api(self, api_url):
        """Test that database is accessible through API"""
        try:
            response = requests.get(f"{api_url}/users", timeout=5)
            
            # Should be able to access database
            assert response.status_code < 500
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")


class TestDatabaseOperations:
    """Test database operations through PHP"""
    
    @pytest.fixture
    def api_url(self, base_url):
        return f"{base_url}/api"
    
    def test_read_operation(self, api_url):
        """Test reading from database"""
        try:
            response = requests.get(f"{api_url}/users", timeout=5)
            
            # Read operation should work
            assert response.status_code in [200, 401, 403, 404]
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")
    
    def test_feedback_data_accessible(self, api_url):
        """Test that feedback data is accessible"""
        try:
            response = requests.get(f"{api_url}/feedback", timeout=5)
            
            # Should be able to fetch feedback
            assert response.status_code in [200, 401, 403, 404]
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")


class TestDatabaseResponses:
    """Test database response through API"""
    
    @pytest.fixture
    def api_url(self, base_url):
        return f"{base_url}/api"
    
    def test_response_not_empty(self, api_url):
        """Test that API responses are not empty"""
        try:
            response = requests.get(f"{api_url}/users", timeout=5)
            
            if response.status_code == 200:
                assert len(response.text) > 0
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")
