<?php

namespace App\Console\Commands;

use App\Models\MusicTrack;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportMusicCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'music:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import local music files into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = storage_path('app/music/bandcamp');

        if (!File::exists($path)) {
            $this->error("Music directory not found: {$path}");
            // Create it just in case to avoid future errors? No, just warn.
            return;
        }

        $this->info("Scanning {$path}...");
        $files = File::allFiles($path);
        $count = 0;

        foreach ($files as $file) {
            // Expected structure: storage/app/music/bandcamp/{Artist}/{Album}/{File}
            // getRelativePath() returns e.g. "Artist/Album"
            $relativePath = $file->getRelativePath();
            $parts = explode(DIRECTORY_SEPARATOR, $relativePath);

            // Default fallback
            $artist = 'Unknown Artist';
            $album = 'Unknown Album';

            if (count($parts) >= 1) $artist = $parts[0];
            if (count($parts) >= 2) $album = $parts[1];

            $extension = strtolower($file->getExtension());
            if (!in_array($extension, ['mp3', 'wav', 'flac', 'ogg', 'm4a'])) {
                continue;
            }

            $title = $file->getFilenameWithoutExtension();
            // Path relative to storage/app for serving?
            // Actually, we usually serve via a route or symlink.
            // Let's store relative to storage/app.
            $internalPath = 'music/bandcamp/' . $file->getRelativePathname();

            MusicTrack::updateOrCreate(
                ['file_path' => $internalPath],
                [
                    'artist' => $artist,
                    'album' => $album,
                    'title' => $title,
                    // 'duration' => 0, // Defaults to 0
                ]
            );
            $count++;
        }

        $this->info("Imported {$count} tracks.");
    }
}
