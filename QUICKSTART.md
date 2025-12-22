# Quick Start Guide: Bandcamp Library Downloader

## For ArchLinux Users

### 1. One-Line Installation
```bash
./install_bandcamp.sh
```

### 2. Get Your Cookies

**Using Firefox:**
1. Install [Cookie Quick Manager](https://addons.mozilla.org/en-US/firefox/addon/cookie-quick-manager/)
2. Go to bandcamp.com and log in
3. Click the Cookie Quick Manager icon
4. Search for "bandcamp.com"
5. Export cookies as JSON
6. Save as `cookies.json` in this directory

**Manual Method:**
Create a file named `cookies.json` with this content:
```json
[
  {
    "name": "identity",
    "value": "YOUR_IDENTITY_VALUE_FROM_BROWSER",
    "domain": ".bandcamp.com"
  },
  {
    "name": "js_logged_in", 
    "value": "1",
    "domain": ".bandcamp.com"
  }
]
```

To get your identity token:
1. Open Firefox DevTools (F12)
2. Go to Storage → Cookies → https://bandcamp.com
3. Find the "identity" cookie and copy its value

### 3. Run the Downloader
```bash
python3 bandcamp_downloader.py -u YOUR_BANDCAMP_USERNAME -c cookies.json
```

Replace `YOUR_BANDCAMP_USERNAME` with your actual Bandcamp username.

### 4. Find Your Music
Your downloaded music will be in:
```bash
~/Music/Bandcamp/
```

### 5. Extract the Files
The downloads are ZIP files. Extract them:
```bash
cd ~/Music/Bandcamp
for file in *.zip; do unzip "$file" -d "${file%.zip}"; done
```

## Troubleshooting

**Problem:** "No items found in collection"
- **Solution:** Check your cookies are valid. Try logging out and back in to Bandcamp, then re-export cookies.

**Problem:** "Permission denied"
- **Solution:** Make sure you have write permissions:
  ```bash
  chmod 755 ~/Music/Bandcamp
  ```

**Problem:** Script won't run
- **Solution:** Make it executable:
  ```bash
  chmod +x bandcamp_downloader.py
  ```

## Advanced Usage

### Download to custom location:
```bash
python3 bandcamp_downloader.py -u USERNAME -c cookies.json -o ~/Documents/MyMusic
```

### Verbose logging for debugging:
```bash
python3 bandcamp_downloader.py -u USERNAME -c cookies.json -v
```

### Run tests:
```bash
python3 test_bandcamp.py
```

## Security Reminder

🔒 Keep `cookies.json` private! It contains your login credentials.
- Never share it
- Never commit it to git (already in .gitignore)
- Set restrictive permissions: `chmod 600 cookies.json`

## Need More Help?

See the full documentation: [BANDCAMP_README.md](BANDCAMP_README.md)
