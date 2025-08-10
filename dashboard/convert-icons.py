#!/usr/bin/env python3
"""
Icon conversion script for PressGuard VS Code extension
Converts SVG icons to PNG format for use in VS Code extensions
"""

import os
import sys
import subprocess

def check_dependencies():
    """Check if required tools are available"""
    try:
        # Try to import cairosvg for SVG to PNG conversion
        import cairosvg
        return "cairosvg"
    except ImportError:
        pass
    
    try:
        # Check if ImageMagick is available
        result = subprocess.run(["magick", "--version"], capture_output=True, text=True)
        if result.returncode == 0:
            return "imagemagick"
    except FileNotFoundError:
        pass
    
    try:
        # Check if Inkscape is available
        result = subprocess.run(["inkscape", "--version"], capture_output=True, text=True)
        if result.returncode == 0:
            return "inkscape"
    except FileNotFoundError:
        pass
    
    return None

def convert_with_cairosvg(svg_file, png_file, size):
    """Convert SVG to PNG using cairosvg"""
    import cairosvg
    
    cairosvg.svg2png(
        url=svg_file,
        write_to=png_file,
        output_width=size,
        output_height=size
    )
    print(f"✓ Converted {svg_file} to {png_file} ({size}x{size})")

def convert_with_imagemagick(svg_file, png_file, size):
    """Convert SVG to PNG using ImageMagick"""
    cmd = [
        "magick", 
        svg_file, 
        "-resize", f"{size}x{size}", 
        "-background", "transparent",
        png_file
    ]
    subprocess.run(cmd, check=True)
    print(f"✓ Converted {svg_file} to {png_file} ({size}x{size})")

def convert_with_inkscape(svg_file, png_file, size):
    """Convert SVG to PNG using Inkscape"""
    cmd = [
        "inkscape",
        "--export-type=png",
        f"--export-filename={png_file}",
        f"--export-width={size}",
        f"--export-height={size}",
        svg_file
    ]
    subprocess.run(cmd, check=True)
    print(f"✓ Converted {svg_file} to {png_file} ({size}x{size})")

def main():
    """Main conversion function"""
    print("PressGuard Icon Converter")
    print("=" * 30)
    
    # Check for conversion tools
    tool = check_dependencies()
    if not tool:
        print("❌ No conversion tool found!")
        print("Please install one of the following:")
        print("  - cairosvg: pip install cairosvg")
        print("  - ImageMagick: https://imagemagick.org/")
        print("  - Inkscape: https://inkscape.org/")
        return
    
    print(f"✓ Using {tool} for conversion")
    
    # Define icon sizes and files
    icons = [
        ("icon-128.svg", "icon-128.png", 128),
        ("icon-256.svg", "icon-256.png", 256)
    ]
    
    # Convert each icon
    for svg_file, png_file, size in icons:
        if not os.path.exists(svg_file):
            print(f"⚠️  {svg_file} not found, skipping...")
            continue
        
        try:
            if tool == "cairosvg":
                convert_with_cairosvg(svg_file, png_file, size)
            elif tool == "imagemagick":
                convert_with_imagemagick(svg_file, png_file, size)
            elif tool == "inkscape":
                convert_with_inkscape(svg_file, png_file, size)
        except Exception as e:
            print(f"❌ Error converting {svg_file}: {e}")
    
    print("\n🎉 Icon conversion complete!")
    print("\nFor VS Code extension, use:")
    print("  - icon-128.png for the main icon")
    print("  - icon-256.png for high-DPI displays")

if __name__ == "__main__":
    main()
