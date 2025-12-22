#!/bin/bash
# Installation script for Bandcamp Library Downloader on ArchLinux

set -e

echo "======================================"
echo "Bandcamp Library Downloader Setup"
echo "======================================"
echo ""

# Check if running on Arch-based system
if ! command -v pacman &> /dev/null; then
    echo "Warning: This script is designed for ArchLinux."
    echo "You can still proceed, but you may need to install dependencies manually."
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Install Python and pip if needed
echo "Checking Python installation..."
if ! command -v python3 &> /dev/null; then
    echo "Installing Python..."
    sudo pacman -S --noconfirm python python-pip
else
    echo "Python is already installed: $(python3 --version)"
fi

# Create Music directory
echo ""
echo "Setting up ~/Music/Bandcamp directory..."
mkdir -p "$HOME/Music/Bandcamp"
echo "Created: $HOME/Music/Bandcamp"

# Install Python dependencies
echo ""
echo "Installing Python dependencies..."
pip install --user -r requirements.txt

# Make script executable
chmod +x bandcamp_downloader.py

echo ""
echo "======================================"
echo "Installation Complete!"
echo "======================================"
echo ""
echo "Next steps:"
echo "1. Export your Bandcamp cookies to cookies.json"
echo "   (See BANDCAMP_README.md for instructions)"
echo ""
echo "2. Run the downloader:"
echo "   python3 bandcamp_downloader.py -u YOUR_USERNAME -c cookies.json"
echo ""
echo "For more information, see BANDCAMP_README.md"
echo ""
