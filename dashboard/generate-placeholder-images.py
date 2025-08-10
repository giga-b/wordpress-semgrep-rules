#!/usr/bin/env python3
"""
Generate placeholder dashboard images for PressGuard
"""

from PIL import Image, ImageDraw, ImageFont
import os

def create_placeholder_image(filename, title, description, width=1200, height=800):
    """Create a placeholder dashboard image"""
    
    # Create image with gradient background
    image = Image.new('RGB', (width, height), color='#f5f5f5')
    draw = ImageDraw.Draw(image)
    
    # Create gradient background
    for y in range(height):
        r = int(102 + (y / height) * 30)  # 102 to 132
        g = int(126 + (y / height) * 30)  # 126 to 156
        b = int(234 + (y / height) * 30)  # 234 to 264
        color = (r, g, b)
        draw.line([(0, y), (width, y)], fill=color)
    
    # Add header bar
    header_height = 100
    draw.rectangle([0, 0, width, header_height], fill='#667eea')
    
    # Add title text
    try:
        # Try to use a system font
        font_large = ImageFont.truetype("arial.ttf", 36)
        font_medium = ImageFont.truetype("arial.ttf", 24)
        font_small = ImageFont.truetype("arial.ttf", 16)
    except:
        # Fallback to default font
        font_large = ImageFont.load_default()
        font_medium = ImageFont.load_default()
        font_small = ImageFont.load_default()
    
    # Draw title
    draw.text((width//2, 30), title, fill='white', font=font_large, anchor='mm')
    
    # Add content area
    content_y = header_height + 50
    draw.rectangle([50, content_y, width-50, height-50], fill='white', outline='#ddd')
    
    # Add description
    draw.text((width//2, content_y + 50), description, fill='#333', font=font_medium, anchor='mm')
    
    # Add mock dashboard elements
    # Metrics cards
    card_width = 200
    card_height = 120
    card_spacing = 30
    start_x = 100
    
    metrics = [
        ("Total Scans", "1,030", "#28a745"),
        ("Findings", "4,480", "#dc3545"),
        ("Precision", "85.1%", "#ffc107"),
        ("F1 Score", "0.87", "#17a2b8")
    ]
    
    for i, (label, value, color) in enumerate(metrics):
        x = start_x + i * (card_width + card_spacing)
        y = content_y + 150
        
        # Card background
        draw.rectangle([x, y, x + card_width, y + card_height], fill='#f8f9fa', outline='#e1e5e9')
        
        # Value
        draw.text((x + card_width//2, y + 30), value, fill=color, font=font_large, anchor='mm')
        
        # Label
        draw.text((x + card_width//2, y + 80), label, fill='#666', font=font_small, anchor='mm')
    
    # Add footer
    footer_y = height - 60
    draw.rectangle([0, footer_y, width, height], fill='#f8f9fa')
    draw.text((width//2, footer_y + 20), f"PressGuard Dashboard - {title}", fill='#666', font=font_small, anchor='mm')
    
    # Save image
    image.save(filename, 'PNG')
    print(f"✓ Created placeholder image: {filename}")

def main():
    """Generate placeholder dashboard images"""
    print("PressGuard Dashboard Placeholder Image Generator")
    print("=" * 50)
    
    # Create dashboard directory if it doesn't exist
    os.makedirs('dashboard', exist_ok=True)
    
    # Generate user dashboard placeholder
    create_placeholder_image(
        'dashboard/user-dashboard.png',
        'PressGuard User Dashboard',
        'Interactive security scan results with detailed findings and fix suggestions'
    )
    
    # Generate admin dashboard placeholder
    create_placeholder_image(
        'dashboard/admin-dashboard.png',
        'PressGuard Admin Dashboard',
        'Comprehensive metrics dashboard showing scan performance and trend analysis'
    )
    
    print("\n✓ All placeholder images generated successfully!")
    print("Note: These are placeholder images. For actual screenshots, install selenium and run:")
    print("pip install selenium")
    print("python dashboard/capture-screenshots.py")

if __name__ == "__main__":
    main()
