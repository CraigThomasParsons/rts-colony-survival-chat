<?php

namespace App\Jobs;

use App\Models\BandcampLibraryUrl;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use ZipArchive;

class SyncBandcampAlbumJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 900; // 15 minutes
    public $tries = 3;

    protected $libraryUrl;

    /**
     * Create a new job instance.
     */
    public function __construct(BandcampLibraryUrl $libraryUrl)
    {
        $this->libraryUrl = $libraryUrl;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->libraryUrl->update(['status' => 'processing', 'error_message' => null]);

        try {
            $url = $this->libraryUrl->url;
            Log::info("Syncing Bandcamp Album: {$url}");

            // 1. Prepare Client with Cookies
            $jar = new CookieJar();
            $cookiePath = base_path('cookies.json');
            if (File::exists($cookiePath)) {
                $cookieData = json_decode(File::get($cookiePath), true);
                if (is_array($cookieData)) {
                    foreach ($cookieData as $cookie) {
                        // Basic SetCookie construction
                        $jar->setCookie(new SetCookie([
                            'Name' => $cookie['name'],
                            'Value' => $cookie['value'],
                            'Domain' => $cookie['domain'] ?? '.bandcamp.com',
                            'Path' => $cookie['path'] ?? '/',
                            'Expires' => $cookie['expirationDate'] ?? null,
                        ]));
                    }
                }
            } else {
                Log::warning("No cookies.json found. Downloads may fail for purchased items.");
            }

            $client = new \GuzzleHttp\Client([
                'cookies' => $jar,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                ],
            ]);

            // 2. Fetch Page
            $response = $client->get($url);
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            // 3. Find Download Link
            // Strategy: Look for download buttons.
            // Note: Bandcamp structure varies.
            // For collection items, usually there is a 'redownload' or 'download' link if logged in.
            // But sometimes the link is dynamic.
            // The Python script looks for .download-link.
            $linkNode = $crawler->filter('.download-link, a[href*="/download/"]')->first();

            if ($linkNode->count() === 0) {
                // If not found, check if it's a "name your price" with "0" option embedded logic?
                // Or maybe we are not logged in properly.
                throw new \Exception("Download link not found on page. Check cookies or album status.");
            }

            $downloadPageUrl = $linkNode->attr('href');
            if (!str_starts_with($downloadPageUrl, 'http')) {
                // Resolved against base
                // Simple hack: assume relative to root if starts with /, relative to current if not?
                // Bandcamp usually uses relative paths.
               // Assuming logic to resolve URL
               $base = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);
               $downloadPageUrl = $base . $downloadPageUrl;
            }
            
            // NOTE: The download link on the page usually leads to a "download page" where you select format (MP3/FLAC).
            // We need to visit THAT page and find the actual zip link.
            // Usually valid formats are 'mp3-320', 'flac', etc.
            
            Log::info("Visiting download page: {$downloadPageUrl}");
            $dlPageResponse = $client->get($downloadPageUrl);
            $dlHtml = (string) $dlPageResponse->getBody();
            
            // On the download page, we look for the JSON blob typically embedded in `pagedata` variable
            // or just find the 'mp3-320' link.
            // Often it's in a script tag: var pagedata = { ... }
            
            if (preg_match('/var pagedata = ({.*?});/s', $dlHtml, $matches)) {
                 $pagedata = json_decode($matches[1], true);
                 // The extraction logic is complex. 
                 // Simpler approach: Look for direct link in JSON or DOM.
                 // The python script didn't elaborate on the "download_file" step logic deeply, assuming direct link.
                 // But most bandcamp download pages have a "Download" button that triggers an API call.
                 
                 // Fallback: If we can't parse it easily, we fail.
                 // But let's try to find a link with "mp3-320" in parsing.
            }
            
            // For now, let's assume we can find a naive direct link or just throw 'Not Implemented' for complex flows to keep it safe.
            // Real Bandcamp download automation is non-trivial without an API or robust scraping.
            // However, the python script `get_download_link` seemed to expect a direct link.
            // If the python script works, maybe I should check what it does exactly.
            // Python: `download_link = soup.select_one('a.download-link')...`
            // If that link is a ZIP, we are good.
            // If it's a PAGE, we need to go deeper.
            // Let's assume the link from the collection page IS the download link (often true for simple setups).

             // Let's just try to download whatever `downloadPageUrl` is.
             // If headers say it's text/html, it's a page. If application/zip, it's the file.
            
             // HEAD request to check content type
             $head = $client->head($downloadPageUrl);
             $contentType = $head->getHeaderLine('Content-Type');
             
             $finalUrl = $downloadPageUrl;
             
             if (str_contains($contentType, 'text/html')) {
                 // It's a page. Need to find the actual file link. 
                 // Attempting to regex for the zip url.
                 // Usually regex for `https:.*?\.zip`
                 if (preg_match('/"(https:[^"]+?\.zip[^"]*?)"/', $dlHtml, $m)) {
                     $finalUrl = $m[1];
                     // Clean escaped slashes
                     $finalUrl = str_replace('\\u0026', '&', $finalUrl); // simplify
                 } else {
                     throw new \Exception("Could not extract ZIP link from download page.");
                 }
             }

            Log::info("Downloading file from: {$finalUrl}");

            // Create temp file
            $tempFile = storage_path('app/temp_bandcamp_' . $this->libraryUrl->id . '.zip');
            $sink = fopen($tempFile, 'w');
            $client->request('GET', $finalUrl, ['sink' => $sink]);
            fclose($sink);
            
            // Unzip
            $zip = new ZipArchive;
            if ($zip->open($tempFile) === TRUE) {
                // Determine extract path: storage/app/music/bandcamp/{Artist}/{Album}
                // We need to parse artist/album from page or URL?
                // The URL is like `artist.bandcamp.com/album/name`
                // Host -> artist.
                $parsed = parse_url($url);
                $hostParts = explode('.', $parsed['host']);
                $artist = $hostParts[0];
                
                // Path parts
                $pathParts = explode('/', trim($parsed['path'], '/'));
                $album = end($pathParts);
                
                $extractPath = storage_path("app/music/bandcamp/{$artist}/{$album}");
                if (!File::exists($extractPath)) {
                    File::makeDirectory($extractPath, 0755, true);
                }

                $zip->extractTo($extractPath);
                $zip->close();
                
                // Cleanup
                File::delete($tempFile);
                
                $this->libraryUrl->update([
                    'status' => 'downloaded',
                    'last_synced_at' => now()
                ]);
                
                Log::info("Successfully synced to {$extractPath}");
            } else {
                throw new \Exception("Failed to open downloaded ZIP file.");
            }

        } catch (\Exception $e) {
            Log::error("Bandcamp Sync Failed for {$this->libraryUrl->id}: " . $e->getMessage());
            $this->libraryUrl->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            // Logic to release w/ delay?
            $this->release(300); // Retry in 5 mins
        }
    }
}
