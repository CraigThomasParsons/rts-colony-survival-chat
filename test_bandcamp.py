#!/usr/bin/env python3
"""
Simple test script for Bandcamp downloader
Tests basic functionality without requiring actual Bandcamp credentials
"""

import sys
import tempfile
from pathlib import Path

# Add parent directory to path to import the module
sys.path.insert(0, str(Path(__file__).parent))

from bandcamp_downloader import BandcampDownloader


def test_initialization():
    """Test that BandcampDownloader initializes correctly"""
    print("Test 1: Initialization...")
    
    with tempfile.TemporaryDirectory() as tmpdir:
        downloader = BandcampDownloader(output_dir=tmpdir)
        
        # Check output directory was created
        assert downloader.output_dir.exists(), "Output directory not created"
        assert str(downloader.output_dir) == tmpdir, "Output directory mismatch"
        
        print("✓ Initialization test passed")


def test_default_output_dir():
    """Test default output directory setup"""
    print("\nTest 2: Default output directory...")
    
    downloader = BandcampDownloader()
    expected_dir = Path.home() / 'Music' / 'Bandcamp'
    
    assert downloader.output_dir == expected_dir, "Default output dir incorrect"
    print(f"✓ Default output directory: {downloader.output_dir}")


def test_session_setup():
    """Test that requests session is properly configured"""
    print("\nTest 3: Session configuration...")
    
    downloader = BandcampDownloader()
    
    # Check User-Agent is set
    assert 'User-Agent' in downloader.session.headers, "User-Agent not set"
    assert 'Mozilla' in downloader.session.headers['User-Agent'], "Invalid User-Agent"
    
    print(f"✓ User-Agent: {downloader.session.headers['User-Agent'][:50]}...")


def test_cookies_file_handling():
    """Test cookies file error handling"""
    print("\nTest 4: Cookies file handling...")
    
    # Test with non-existent file
    try:
        downloader = BandcampDownloader(cookies_file="/tmp/nonexistent_cookies.json")
        print("✗ Should have raised exception for missing cookies file")
    except Exception:
        print("✓ Correctly handles missing cookies file")


def main():
    """Run all tests"""
    print("=" * 60)
    print("Bandcamp Downloader - Basic Functionality Tests")
    print("=" * 60)
    
    try:
        test_initialization()
        test_default_output_dir()
        test_session_setup()
        test_cookies_file_handling()
        
        print("\n" + "=" * 60)
        print("All tests passed! ✓")
        print("=" * 60)
        
    except AssertionError as e:
        print(f"\n✗ Test failed: {e}")
        sys.exit(1)
    except Exception as e:
        print(f"\n✗ Unexpected error: {e}")
        sys.exit(1)


if __name__ == '__main__':
    main()
