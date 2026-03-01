"""
Master Test Runner for Selenium Test Suite
Executes all test files and generates comprehensive HTML reports
"""
import subprocess
import sys
import os
from datetime import datetime
import time


class MasterTestRunner:
    """Orchestrates all Selenium test executions"""
    
    def __init__(self):
        self.start_time = datetime.now()
        self.test_files = [
            "test_complete_suite.py",
            "test_authentication.py",
            "test_navigation_ui.py",
            "test_database.py",
            "test_integration.py"
        ]
        self.results = []
        
        # Create reports directory if it doesn't exist
        if not os.path.exists("reports"):
            os.makedirs("reports")
    
    def print_header(self):
        """Print test suite header"""
        print("=" * 80)
        print("SELENIUM TEST SUITE - MASTER TEST RUNNER")
        print("Student Feedback System - Comprehensive Testing")
        print("=" * 80)
        print(f"Start Time: {self.start_time.strftime('%Y-%m-%d %H:%M:%S')}")
        print(f"Python Version: {sys.version}")
        print("=" * 80)
        print()
    
    def run_test_file(self, test_file):
        """Run a single test file"""
        if not os.path.exists(test_file):
            print(f"⚠ SKIP: {test_file} (file not found)")
            return {"file": test_file, "status": "skipped", "reason": "File not found"}
        
        print(f"\n{'=' * 80}")
        print(f"Running: {test_file}")
        print('=' * 80)
        
        # Generate report filename
        report_name = test_file.replace("test_", "").replace(".py", "")
        report_file = f"reports/{report_name}_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.html"
        
        # Run pytest with HTML report
        cmd = [
            "pytest",
            test_file,
            "-v",
            "--html=" + report_file,
            "--self-contained-html",
            "--tb=short"
        ]
        
        try:
            result = subprocess.run(
                cmd,
                capture_output=False,
                text=True,
                timeout=300  # 5 minutes timeout per test file
            )
            
            status = "passed" if result.returncode == 0 else "failed"
            
            return {
                "file": test_file,
                "status": status,
                "return_code": result.returncode,
                "report": report_file
            }
        
        except subprocess.TimeoutExpired:
            print(f"\n⏱ TIMEOUT: {test_file} exceeded 5 minutes")
            return {
                "file": test_file,
                "status": "timeout",
                "reason": "Exceeded 5 minute timeout"
            }
        
        except Exception as e:
            print(f"\n❌ ERROR: {test_file} - {str(e)}")
            return {
                "file": test_file,
                "status": "error",
                "error": str(e)
            }
    
    def run_all_tests(self):
        """Run all test files sequentially"""
        self.print_header()
        
        for test_file in self.test_files:
            result = self.run_test_file(test_file)
            self.results.append(result)
            time.sleep(2)  # Brief pause between tests
        
        self.print_summary()
        self.generate_master_report()
    
    def print_summary(self):
        """Print test execution summary"""
        end_time = datetime.now()
        duration = end_time - self.start_time
        
        print("\n")
        print("=" * 80)
        print("TEST EXECUTION SUMMARY")
        print("=" * 80)
        
        passed = sum(1 for r in self.results if r.get("status") == "passed")
        failed = sum(1 for r in self.results if r.get("status") == "failed")
        skipped = sum(1 for r in self.results if r.get("status") == "skipped")
        errors = sum(1 for r in self.results if r.get("status") == "error")
        timeout = sum(1 for r in self.results if r.get("status") == "timeout")
        
        total = len(self.results)
        
        print(f"Total Test Files: {total}")
        print(f"✓ Passed: {passed}")
        print(f"✗ Failed: {failed}")
        print(f"⊘ Skipped: {skipped}")
        print(f"⚠ Errors: {errors}")
        print(f"⏱ Timeout: {timeout}")
        print()
        print(f"Duration: {duration}")
        print(f"End Time: {end_time.strftime('%Y-%m-%d %H:%M:%S')}")
        print("=" * 80)
        
        # Print individual results
        print("\nDetailed Results:")
        print("-" * 80)
        for result in self.results:
            status_icon = {
                "passed": "✓",
                "failed": "✗",
                "skipped": "⊘",
                "error": "⚠",
                "timeout": "⏱"
            }.get(result["status"], "?")
            
            print(f"{status_icon} {result['file']}: {result['status'].upper()}")
            
            if "report" in result:
                print(f"  Report: {result['report']}")
            if "reason" in result:
                print(f"  Reason: {result['reason']}")
            if "error" in result:
                print(f"  Error: {result['error']}")
        
        print("=" * 80)
        print()
        print("✨ All test reports are available in the 'reports/' directory")
        print()
    
    def generate_master_report(self):
        """Generate a master HTML report combining all results"""
        report_path = f"reports/master_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.html"
        
        html_content = f"""<!DOCTYPE html>
<html>
<head>
    <title>Selenium Test Suite - Master Report</title>
    <style>
        body {{
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }}
        .container {{
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }}
        h1 {{
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }}
        .summary {{
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }}
        .summary-card {{
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            color: white;
        }}
        .passed {{ background: #4CAF50; }}
        .failed {{ background: #f44336; }}
        .skipped {{ background: #ff9800; }}
        .error {{ background: #9c27b0; }}
        .timeout {{ background: #607d8b; }}
        .summary-card h2 {{
            margin: 0;
            font-size: 36px;
        }}
        .summary-card p {{
            margin: 5px 0 0 0;
        }}
        table {{
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }}
        th, td {{
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }}
        th {{
            background: #4CAF50;
            color: white;
        }}
        tr:hover {{
            background: #f5f5f5;
        }}
        .status-pass {{ color: #4CAF50; font-weight: bold; }}
        .status-fail {{ color: #f44336; font-weight: bold; }}
        .status-skip {{ color: #ff9800; font-weight: bold; }}
        .status-error {{ color: #9c27b0; font-weight: bold; }}
        .status-timeout {{ color: #607d8b; font-weight: bold; }}
        .footer {{
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
        }}
        a {{
            color: #2196F3;
            text-decoration: none;
        }}
        a:hover {{
            text-decoration: underline;
        }}
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Selenium Test Suite - Master Report</h1>
        
        <p><strong>Project:</strong> Student Feedback System</p>
        <p><strong>Start Time:</strong> {self.start_time.strftime('%Y-%m-%d %H:%M:%S')}</p>
        <p><strong>Duration:</strong> {datetime.now() - self.start_time}</p>
        
        <div class="summary">
            <div class="summary-card passed">
                <h2>{sum(1 for r in self.results if r.get('status') == 'passed')}</h2>
                <p>Passed</p>
            </div>
            <div class="summary-card failed">
                <h2>{sum(1 for r in self.results if r.get('status') == 'failed')}</h2>
                <p>Failed</p>
            </div>
            <div class="summary-card skipped">
                <h2>{sum(1 for r in self.results if r.get('status') == 'skipped')}</h2>
                <p>Skipped</p>
            </div>
            <div class="summary-card error">
                <h2>{sum(1 for r in self.results if r.get('status') == 'error')}</h2>
                <p>Errors</p>
            </div>
            <div class="summary-card timeout">
                <h2>{sum(1 for r in self.results if r.get('status') == 'timeout')}</h2>
                <p>Timeout</p>
            </div>
        </div>
        
        <h2>Test File Results</h2>
        <table>
            <thead>
                <tr>
                    <th>Test File</th>
                    <th>Status</th>
                    <th>Report</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
"""
        
        for result in self.results:
            status = result.get("status", "unknown")
            status_class = f"status-{status[:4]}"
            
            report_link = ""
            if "report" in result:
                report_filename = os.path.basename(result["report"])
                report_link = f'<a href="{report_filename}" target="_blank">View Report</a>'
            
            notes = result.get("reason", result.get("error", "-"))
            
            html_content += f"""
                <tr>
                    <td>{result['file']}</td>
                    <td class="{status_class}">{status.upper()}</td>
                    <td>{report_link}</td>
                    <td>{notes}</td>
                </tr>
"""
        
        html_content += f"""
            </tbody>
        </table>
        
        <div class="footer">
            <p>Generated by Selenium Test Suite Master Runner</p>
            <p>© {datetime.now().year} Student Feedback System</p>
        </div>
    </div>
</body>
</html>
"""
        
        with open(report_path, 'w', encoding='utf-8') as f:
            f.write(html_content)
        
        print(f"📊 Master report generated: {report_path}")
        
        # Try to open the report
        try:
            if sys.platform == 'win32':
                os.startfile(report_path)
            elif sys.platform == 'darwin':
                subprocess.run(['open', report_path])
            else:
                subprocess.run(['xdg-open', report_path])
        except:
            pass


def main():
    """Main entry point"""
    runner = MasterTestRunner()
    runner.run_all_tests()
    
    # Return exit code based on results
    failed = sum(1 for r in runner.results if r.get("status") in ["failed", "error"])
    sys.exit(1 if failed > 0 else 0)


if __name__ == "__main__":
    main()
