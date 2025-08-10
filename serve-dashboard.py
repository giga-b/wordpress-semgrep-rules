#!/usr/bin/env python3
"""
Simple HTTP server to serve the WordPress Semgrep Rules Dashboard
"""

import http.server
import socketserver
import os
import sys
from pathlib import Path

def main():
    # Change to the dashboard directory
    dashboard_dir = Path(__file__).parent / "dashboard"
    
    if not dashboard_dir.exists():
        print(f"Error: Dashboard directory not found at {dashboard_dir}")
        sys.exit(1)
    
    os.chdir(dashboard_dir)
    
    PORT = 8080
    
    # Create the server
    Handler = http.server.SimpleHTTPRequestHandler
    
    with socketserver.TCPServer(("", PORT), Handler) as httpd:
        print(f"🚀 WordPress Semgrep Rules Dashboard")
        print(f"📊 Serving at: http://localhost:{PORT}")
        print(f"📁 Directory: {dashboard_dir.absolute()}")
        print(f"🛑 Press Ctrl+C to stop the server")
        print("-" * 50)
        
        try:
            httpd.serve_forever()
        except KeyboardInterrupt:
            print("\n🛑 Server stopped.")
            httpd.shutdown()

if __name__ == "__main__":
    main()
