<?php

namespace App\Console\Commands;

use App\Models\BandcampLibraryUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BandcampIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bandcamp:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index Bandcamp URLs from bandcamp-owned.txt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = base_path('bandcamp-owned.txt');

        if (!File::exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $count = 0;

        $this->info("Found " . count($lines) . " lines. Processing...");

        foreach ($lines as $line) {
            $url = trim($line);
            // removing HTML entities just in case
            $url = html_entity_decode($url);

            if (empty($url)) {
                continue;
            }

            $record = BandcampLibraryUrl::firstOrCreate(
                ['url' => $url],
                ['status' => 'pending']
            );

            if ($record->wasRecentlyCreated) {
                $count++;
            }
        }

        $this->info("Indexed {$count} new URLs.");
        return 0;
    }
}
