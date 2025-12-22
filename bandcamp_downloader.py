#!/usr/bin/env python3
"""
Bandcamp Library Downloader
Downloads your purchased music collection from Bandcamp to ~/Music folder
"""

import os
import sys
import json
import argparse
import logging
from pathlib import Path
from urllib.parse import urljoin
import requests
from bs4 import BeautifulSoup

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


class BandcampDownloader:
    """Main class for downloading Bandcamp library"""
    
    def __init__(self, cookies_file=None, output_dir=None):
        """
        Initialize the downloader
        
        Args:
            cookies_file: Path to cookies.json file with Bandcamp session
            output_dir: Directory to save downloaded music (default: ~/Music/Bandcamp)
        """
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36'
        })
        
        # Set output directory
        if output_dir:
            self.output_dir = Path(output_dir).expanduser()
        else:
            self.output_dir = Path.home() / 'Music' / 'Bandcamp'
        
        self.output_dir.mkdir(parents=True, exist_ok=True)
        logger.info(f"Output directory: {self.output_dir}")
        
        # Load cookies if provided
        if cookies_file:
            self.load_cookies(cookies_file)
    
    def load_cookies(self, cookies_file):
        """Load cookies from JSON file"""
        try:
            with open(cookies_file, 'r') as f:
                cookies = json.load(f)
                for cookie in cookies:
                    self.session.cookies.set(
                        cookie['name'],
                        cookie['value'],
                        domain=cookie.get('domain', '.bandcamp.com')
                    )
            logger.info(f"Loaded cookies from {cookies_file}")
        except Exception as e:
            logger.error(f"Failed to load cookies: {e}")
            raise
    
    def get_collection(self, username):
        """
        Get user's collection from Bandcamp
        
        Args:
            username: Bandcamp username
            
        Returns:
            List of album/track URLs
        """
        collection_url = f"https://bandcamp.com/{username}"
        logger.info(f"Fetching collection from {collection_url}")
        
        try:
            response = self.session.get(collection_url)
            response.raise_for_status()
            
            soup = BeautifulSoup(response.text, 'html.parser')
            
            # Find collection items
            collection_items = []
            
            # Look for collection grid items
            items = soup.select('.collection-item-container')
            
            for item in items:
                link = item.select_one('a')
                if link and link.get('href'):
                    album_url = link['href']
                    # Make URL absolute if needed
                    if not album_url.startswith('http'):
                        album_url = urljoin('https://bandcamp.com', album_url)
                    collection_items.append(album_url)
            
            logger.info(f"Found {len(collection_items)} items in collection")
            return collection_items
            
        except Exception as e:
            logger.error(f"Failed to fetch collection: {e}")
            return []
    
    def get_download_link(self, item_url):
        """
        Get download link for an album or track
        
        Args:
            item_url: URL of the album or track page
            
        Returns:
            Download URL or None
        """
        try:
            response = self.session.get(item_url)
            response.raise_for_status()
            
            soup = BeautifulSoup(response.text, 'html.parser')
            
            # Look for download button/link
            download_link = soup.select_one('a.download-link, a[href*="download"]')
            
            if download_link and download_link.get('href'):
                download_url = download_link['href']
                if not download_url.startswith('http'):
                    download_url = urljoin(item_url, download_url)
                return download_url
            
            logger.warning(f"No download link found for {item_url}")
            return None
            
        except Exception as e:
            logger.error(f"Failed to get download link for {item_url}: {e}")
            return None
    
    def download_file(self, url, filename):
        """
        Download a file from URL
        
        Args:
            url: Download URL
            filename: Local filename to save to
        """
        try:
            logger.info(f"Downloading {filename}...")
            
            response = self.session.get(url, stream=True)
            response.raise_for_status()
            
            filepath = self.output_dir / filename
            
            # Check if file already exists
            if filepath.exists():
                logger.info(f"File already exists, skipping: {filename}")
                return
            
            total_size = int(response.headers.get('content-length', 0))
            
            with open(filepath, 'wb') as f:
                if total_size == 0:
                    f.write(response.content)
                else:
                    downloaded = 0
                    for chunk in response.iter_content(chunk_size=8192):
                        if chunk:
                            f.write(chunk)
                            downloaded += len(chunk)
                            percent = (downloaded / total_size) * 100
                            print(f"\rProgress: {percent:.1f}%", end='', flush=True)
                    print()  # New line after progress
            
            logger.info(f"Successfully downloaded: {filename}")
            
        except Exception as e:
            logger.error(f"Failed to download {filename}: {e}")
    
    def download_collection(self, username):
        """
        Download entire collection for a user
        
        Args:
            username: Bandcamp username
        """
        logger.info(f"Starting download of collection for user: {username}")
        
        collection = self.get_collection(username)
        
        if not collection:
            logger.warning("No items found in collection")
            return
        
        for i, item_url in enumerate(collection, 1):
            logger.info(f"\nProcessing item {i}/{len(collection)}: {item_url}")
            
            download_url = self.get_download_link(item_url)
            
            if download_url:
                # Extract filename from URL or create one
                filename = f"item_{i}.zip"
                if '/' in item_url:
                    parts = item_url.rstrip('/').split('/')
                    if parts:
                        filename = f"{parts[-1]}.zip"
                
                self.download_file(download_url, filename)
        
        logger.info(f"\nDownload complete! Files saved to: {self.output_dir}")


def main():
    """Main entry point"""
    parser = argparse.ArgumentParser(
        description='Download your Bandcamp music library to ~/Music folder'
    )
    parser.add_argument(
        '-u', '--username',
        required=True,
        help='Your Bandcamp username'
    )
    parser.add_argument(
        '-c', '--cookies',
        help='Path to cookies.json file (required for downloading purchased items)'
    )
    parser.add_argument(
        '-o', '--output',
        help='Output directory (default: ~/Music/Bandcamp)'
    )
    parser.add_argument(
        '-v', '--verbose',
        action='store_true',
        help='Enable verbose logging'
    )
    
    args = parser.parse_args()
    
    if args.verbose:
        logger.setLevel(logging.DEBUG)
    
    try:
        downloader = BandcampDownloader(
            cookies_file=args.cookies,
            output_dir=args.output
        )
        downloader.download_collection(args.username)
        
    except KeyboardInterrupt:
        logger.info("\nDownload interrupted by user")
        sys.exit(1)
    except Exception as e:
        logger.error(f"Fatal error: {e}")
        sys.exit(1)


if __name__ == '__main__':
    main()
