#!/bin/bash
# Mac/Linux script to run Selenium tests

echo "================================"
echo "Student Feedback System - Selenium Tests"
echo "================================"
echo ""

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo "Error: Python 3 is not installed"
    exit 1
fi

# Install requirements if needed
echo "Installing dependencies..."
pip3 install -q -r requirements.txt

echo ""
echo "================================"
echo "Running Tests"
echo "================================"
echo ""

# Run pytest with HTML report
pytest -v --html=reports/test_report.html --self-contained-html

echo ""
echo "================================"
echo "Test Execution Complete"
echo "================================"
echo "Report generated: reports/test_report.html"

# Open report in default browser
if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    xdg-open reports/test_report.html
elif [[ "$OSTYPE" == "darwin"* ]]; then
    open reports/test_report.html
fi
