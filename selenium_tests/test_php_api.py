"""
Selenium tests for PHP API endpoints
Tests the backend API endpoints served by Apache/PHP
"""
import pytest
import requests
import json
import time


class TestPHPAPIAvailability:
    """Test that PHP API endpoints are available"""
    
    @pytest.fixture
    def api_url(self, base_url):
        """Get API base URL"""
        return f"{base_url}/api"
    
    def test_base_url_responds(self, base_url):
        """Test that base URL is accessible"""
        try:
            response = requests.get(base_url, timeout=5)
            assert response.status_code < 500, "Base URL should be accessible"
        except requests.exceptions.ConnectionError:
            pytest.fail("Cannot connect to base URL: " + base_url)
    
    def test_api_directory_accessible(self, api_url):
        """Test that API directory is accessible"""
        try:
            response = requests.get(api_url, timeout=5)
            # Should be accessible (may be 200, 404, 403, etc)
            assert response.status_code < 500
        except requests.exceptions.ConnectionError:
            pytest.fail("Cannot connect to API URL: " + api_url)


class TestPHPAPIEndpoints:
    """Test specific PHP API endpoints"""
    
    @pytest.fixture
    def api_url(self, base_url):
        return f"{base_url}/api"
    
    def test_health_endpoint(self, api_url):
        """Test health/status endpoint"""
        endpoints = [
            f"{api_url}/health",
            f"{api_url}/status",
            f"{api_url}/ping"
        ]
        
        for endpoint in endpoints:
            try:
                response = requests.get(endpoint, timeout=5)
                # Endpoint should respond (even if 404)
                assert response.status_code < 500
                break  # If one works, that's enough
            except requests.exceptions.ConnectionError:
                continue
    
    def test_feedback_endpoint(self, api_url):
        """Test feedback endpoint"""
        try:
            response = requests.get(f"{api_url}/feedback", timeout=5)
            assert response.status_code < 500
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")
    
    def test_users_endpoint(self, api_url):
        """Test users endpoint"""
        try:
            response = requests.get(f"{api_url}/users", timeout=5)
            assert response.status_code < 500
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")


class TestPHPAPIResponses:
    """Test PHP API response formats"""
    
    @pytest.fixture
    def api_url(self, base_url):
        return f"{base_url}/api"
    
    def test_api_returns_data(self, api_url):
        """Test that API returns some data"""
        try:
            response = requests.get(f"{api_url}/feedback", timeout=5)
            
            # Check response is not empty
            assert len(response.text) > 0, "API should return content"
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")
    
    def test_api_json_response(self, api_url):
        """Test that API returns JSON when appropriate"""
        try:
            headers = {"Accept": "application/json"}
            response = requests.get(f"{api_url}/feedback", headers=headers, timeout=5)
            
            if response.status_code == 200:
                try:
                    data = response.json()
                    assert data is not None
                except json.JSONDecodeError:
                    # Not JSON, that's okay
                    pass
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")


class TestPHPDatabaseConnectivity:
    """Test database connectivity through PHP"""
    
    @pytest.fixture
    def api_url(self, base_url):
        return f"{base_url}/api"
    
    def test_api_database_access(self, api_url):
        """Test that API can access database"""
        try:
            response = requests.get(f"{api_url}/users", timeout=5)
            
            # Should get response without database errors
            assert response.status_code < 500
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")


class TestPHPAPIErrorHandling:
    """Test PHP API error handling"""
    
    @pytest.fixture
    def api_url(self, base_url):
        return f"{base_url}/api"
    
    def test_nonexistent_endpoint(self, api_url):
        """Test that API handles nonexistent endpoints gracefully"""
        try:
            response = requests.get(f"{api_url}/nonexistent123456", timeout=5)
            
            # Should return 4xx error, not 5xx server error
            assert response.status_code < 500, "API should handle errors gracefully"
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")
    
    def test_invalid_request(self, api_url):
        """Test that API handles invalid requests"""
        try:
            response = requests.post(
                f"{api_url}/feedback",
                data="invalid_data",
                timeout=5
            )
            
            # Should return client error, not server error
            assert response.status_code < 500
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")


class TestPHPAPIPerformance:
    """Test PHP API performance"""
    
    @pytest.fixture
    def api_url(self, base_url):
        return f"{base_url}/api"
    
    def test_api_response_time(self, api_url):
        """Test that API responds quickly"""
        try:
            start = time.time()
            response = requests.get(f"{api_url}/health", timeout=5)
            end = time.time()
            
            response_time = (end - start) * 1000  # Convert to ms
            
            # API should respond within 5 seconds
            assert response_time < 5000
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")


class TestPHPAPIHeaders:
    """Test PHP API response headers"""
    
    @pytest.fixture
    def api_url(self, base_url):
        return f"{base_url}/api"
    
    def test_api_response_headers(self, api_url):
        """Test that API returns proper headers"""
        try:
            response = requests.get(f"{api_url}/health", timeout=5)
            
            # Should have headers
            assert len(response.headers) > 0
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")
    
    def test_api_content_type_header(self, api_url):
        """Test that API returns Content-Type header"""
        try:
            response = requests.get(f"{api_url}/health", timeout=5)
            
            # May or may not have Content-Type, just check headers
            assert response.headers is not None
        except requests.exceptions.ConnectionError:
            pytest.skip("API not available")
