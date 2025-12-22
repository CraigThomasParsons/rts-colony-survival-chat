# Bandcamp Library Downloader

A Python application to download your purchased Bandcamp music library to your ArchLinux ~/Music folder.

## Features

- Downloads all albums and tracks from your Bandcamp collection
- Automatically organizes files in ~/Music/Bandcamp
- Supports authentication via cookies
- Resume capability (skips already downloaded files)
- Progress tracking for large downloads
- Logging for debugging and monitoring

## Requirements

- Python 3.7 or higher
- ArchLinux (or any Linux distribution)
- Active Bandcamp account with purchased music

## Installation

### On ArchLinux

1. Install Python and pip if not already installed:
```bash
sudo pacman -S python python-pip
```

2. Clone this repository or download the script:
```bash
# Note: This Bandcamp downloader is part of a larger project repository
git clone https://github.com/CraigThomasParsons/rts-colony-survival-chat.git
cd rts-colony-survival-chat

# Or download just the necessary files:
# - bandcamp_downloader.py
# - requirements.txt
# - BANDCAMP_README.md
# - cookies.json.example
```

3. Install Python dependencies:
```bash
pip install -r requirements.txt
```

Or install globally:
```bash
sudo pip install -r requirements.txt
```

Or use a virtual environment (recommended):
```bash
python -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

## Setup

### Getting Your Cookies

Since Bandcamp requires authentication to download purchased items, you need to export your browser cookies:

#### Method 1: Using Browser Extension (Easiest)

1. Install a cookie export extension for your browser:
   - **Firefox**: [Cookie Quick Manager](https://addons.mozilla.org/en-US/firefox/addon/cookie-quick-manager/)
   - **Chrome/Brave**: [EditThisCookie](https://chrome.google.com/webstore/detail/editthiscookie/)

2. Log in to Bandcamp in your browser

3. Export cookies for bandcamp.com to a JSON file named `cookies.json`

#### Method 2: Manual Export (Firefox)

1. Open Firefox and log in to Bandcamp
2. Press F12 to open Developer Tools
3. Go to Storage tab → Cookies → https://bandcamp.com
4. Copy the cookie values and create a `cookies.json` file in this format:

```json
[
  {
    "name": "identity",
    "value": "YOUR_IDENTITY_TOKEN",
    "domain": ".bandcamp.com"
  },
  {
    "name": "js_logged_in",
    "value": "1",
    "domain": ".bandcamp.com"
  }
]
```

Save the `cookies.json` file in the same directory as the script.

## Usage

### Basic Usage

Download your collection using your Bandcamp username:

```bash
python bandcamp_downloader.py -u YOUR_USERNAME -c cookies.json
```

### Custom Output Directory

Specify a custom output directory:

```bash
python bandcamp_downloader.py -u YOUR_USERNAME -c cookies.json -o ~/MyMusic/Bandcamp
```

### Verbose Mode

Enable detailed logging:

```bash
python bandcamp_downloader.py -u YOUR_USERNAME -c cookies.json -v
```

### Command-Line Options

```
-u, --username   Your Bandcamp username (required)
-c, --cookies    Path to cookies.json file (required)
-o, --output     Output directory (default: ~/Music/Bandcamp)
-v, --verbose    Enable verbose logging
```

## Example

```bash
# Download collection to default location
python bandcamp_downloader.py -u myusername -c cookies.json

# Download to custom location with verbose output
python bandcamp_downloader.py -u myusername -c cookies.json -o ~/Documents/Music -v
```

## File Organization

Downloaded files are saved to:
```
~/Music/Bandcamp/
├── album-name-1.zip
├── album-name-2.zip
└── track-name-1.zip
```

You can extract the ZIP files to get the actual audio files:
```bash
cd ~/Music/Bandcamp
for file in *.zip; do unzip "$file" -d "${file%.zip}"; done
```

## Troubleshooting

### "No items found in collection"

- Make sure you're logged in to Bandcamp and your cookies are valid
- Check that your cookies.json file is properly formatted
- Try re-exporting your cookies

### "Failed to load cookies"

- Verify the cookies.json file exists and is valid JSON
- Check file permissions: `chmod 644 cookies.json`

### "Permission denied" when saving files

- Ensure you have write permissions to the output directory
- Try: `mkdir -p ~/Music/Bandcamp && chmod 755 ~/Music/Bandcamp`

### SSL/Certificate Errors

If you get SSL errors, you may need to install certificates:
```bash
sudo pacman -S ca-certificates
```

## Security Notes

⚠️ **Important**: Your `cookies.json` file contains authentication credentials. 

- Never commit `cookies.json` to version control
- Keep the file secure with appropriate permissions: `chmod 600 cookies.json`
- The file is already listed in `.gitignore` for safety

## Limitations

- Bandcamp's website structure may change, requiring script updates
- Rate limiting may apply for large collections
- Downloaded files are in the format provided by Bandcamp (typically ZIP with MP3/FLAC)
- Only downloads items you've purchased/collected

## Contributing

Contributions are welcome! Please:
- Fork the repository
- Create a feature branch
- Submit a pull request

## License

This project is provided as-is for personal use. Please respect Bandcamp's Terms of Service.

## Disclaimer

This tool is for downloading music you have legally purchased or collected on Bandcamp. Do not use it to download music you do not own. Respect artists' rights and Bandcamp's Terms of Service.

## Support

If you encounter issues:
1. Check the troubleshooting section above
2. Run with `-v` flag for detailed logging
3. Open an issue on GitHub with the log output

## Acknowledgments

- Built for ArchLinux users who want to maintain local music libraries
- Inspired by the need to backup purchased music collections
