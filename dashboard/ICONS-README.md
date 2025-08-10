# PressGuard Icons

This directory contains various icon sizes for the PressGuard WordPress Security Scanner project.

## Available Icons

### Original Favicon
- `favicon.svg` - Original 32x32 favicon used in the dashboard
- `favicon.ico` - ICO format favicon

### VS Code Extension Icons
- `icon-128.svg` - 128x128 SVG icon for VS Code extensions
- `icon-256.svg` - 256x256 SVG icon for high-DPI displays
- `icon-128.png` - 128x128 PNG icon (generated from SVG)
- `icon-256.png` - 256x256 PNG icon (generated from SVG)

## Icon Design

The PressGuard icon features:
- **Shield design** - Represents security and protection
- **Gradient colors** - Purple to blue gradient (#667eea to #764ba2)
- **Checkmark** - Indicates security verification and validation
- **Enhanced effects** - Shadows and glow effects for better visibility

## Usage

### VS Code Extension
For your VS Code extension, use:
- `icon-128.png` as the main icon in `package.json`
- `icon-256.png` for high-resolution displays

### Website/Web Application
- Use `icon-128.svg` or `icon-256.svg` for scalable icons
- Use `favicon.ico` for browser favicon

### Documentation
- Use any size for documentation, marketing materials, etc.

## Converting SVG to PNG

If you need PNG versions, use the provided conversion script:

```bash
# Install cairosvg (recommended)
pip install cairosvg

# Run the conversion script
python convert-icons.py
```

The script will automatically detect available conversion tools:
- **cairosvg** (Python library) - Recommended
- **ImageMagick** - Command-line tool
- **Inkscape** - Vector graphics editor

## Icon Specifications

### VS Code Extension Requirements
- **Main icon**: 128x128 pixels (PNG format)
- **High-DPI**: 256x256 pixels (PNG format)
- **Transparent background**: Recommended
- **File size**: Under 1MB

### Web Usage
- **Favicon**: 16x16, 32x32, or 64x64 pixels
- **App icon**: 192x192 or 512x512 pixels
- **Format**: ICO, PNG, or SVG

## Customization

To modify the icon design:
1. Edit the SVG files directly
2. Adjust colors in the gradient definitions
3. Modify the shield or checkmark paths
4. Run the conversion script to generate new PNG files

## Color Palette

- **Primary gradient**: #667eea (light purple) to #764ba2 (dark purple)
- **Accent color**: White (#ffffff)
- **Shadow**: Black with 30% opacity
- **Background**: Transparent or white with 10% opacity
