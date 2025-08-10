#!/usr/bin/env python3
"""
PressGuard Dashboard Screenshot Capture Script

This script helps capture screenshots of the PressGuard dashboards
for use in the README and documentation.

Requirements:
- Python 3.6+
- selenium (pip install selenium)
- ChromeDriver (download from https://chromedriver.chromium.org/)

Usage:
    python capture-screenshots.py
"""

import os
import time
import subprocess
import sys
from pathlib import Path

def check_dependencies():
    """Check if required dependencies are installed."""
    try:
        import selenium
        print("✓ Selenium is installed")
    except ImportError:
        print("✗ Selenium is not installed. Install with: pip install selenium")
        return False
    
    return True

def start_dashboard_server():
    """Start the dashboard server if not already running."""
    try:
        # Check if server is already running
        import requests
        response = requests.get("http://localhost:8000", timeout=2)
        if response.status_code == 200:
            print("✓ Dashboard server is already running")
            return True
    except:
        pass
    
    # Start the server
    print("Starting dashboard server...")
    server_script = Path(__file__).parent.parent / "serve-dashboard.py"
    
    if not server_script.exists():
        print(f"✗ Server script not found: {server_script}")
        return False
    
    try:
        process = subprocess.Popen([sys.executable, str(server_script)], 
                                 stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        time.sleep(3)  # Wait for server to start
        print("✓ Dashboard server started")
        return True
    except Exception as e:
        print(f"✗ Failed to start server: {e}")
        return False

def capture_screenshots():
    """Capture screenshots of both dashboards."""
    try:
        from selenium import webdriver
        from selenium.webdriver.chrome.options import Options
        from selenium.webdriver.common.by import By
        from selenium.webdriver.support.ui import WebDriverWait
        from selenium.webdriver.support import expected_conditions as EC
    except ImportError:
        print("✗ Selenium not available")
        return False
    
    # Setup Chrome options
    chrome_options = Options()
    chrome_options.add_argument("--headless")  # Run in headless mode
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_argument("--window-size=1200,800")
    
    try:
        driver = webdriver.Chrome(options=chrome_options)
    except Exception as e:
        print(f"✗ Failed to start Chrome driver: {e}")
        print("Make sure ChromeDriver is installed and in your PATH")
        return False
    
    try:
        # Capture user dashboard
        print("Capturing user dashboard screenshot...")
        driver.get("http://localhost:8000/dashboard/user-dashboard.html")
        
        # Wait for page to load
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME, "container"))
        )
        
        # Take screenshot
        driver.save_screenshot("dashboard/user-dashboard.png")
        print("✓ User dashboard screenshot saved")
        
        # Capture admin dashboard
        print("Capturing admin dashboard screenshot...")
        driver.get("http://localhost:8000/dashboard/index.html")
        
        # Wait for page to load
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME, "container"))
        )
        
        # Take screenshot
        driver.save_screenshot("dashboard/admin-dashboard.png")
        print("✓ Admin dashboard screenshot saved")
        
        return True
        
    except Exception as e:
        print(f"✗ Failed to capture screenshots: {e}")
        return False
    finally:
        driver.quit()

def main():
    """Main function to capture dashboard screenshots."""
    print("PressGuard Dashboard Screenshot Capture")
    print("=" * 40)
    
    # Check dependencies
    if not check_dependencies():
        return False
    
    # Start server
    if not start_dashboard_server():
        return False
    
    # Capture screenshots
    if not capture_screenshots():
        return False
    
    print("\n✓ Screenshots captured successfully!")
    print("Files created:")
    print("  - dashboard/user-dashboard.png")
    print("  - dashboard/admin-dashboard.png")
    print("\nThese screenshots can now be used in the README.md file.")
    
    return True

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
