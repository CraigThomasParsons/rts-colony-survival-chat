<?php

namespace App\Console\Commands;

use App\Models\BandcampLibraryUrl;
use App\Jobs\SyncBandcampAlbumJob;
use Illuminate\Console\Command;

class SyncBandcampCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:bandcamp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch jobs to sync pending Bandcamp albums';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pending = BandcampLibraryUrl::where('status', 'pending')
            ->orderBy('id')
            ->limit(1) // Processing one at a time to respect rate limits
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No pending items to sync.');
            return;
        }

        foreach ($pending as $item) {
            SyncBandcampAlbumJob::dispatch($item);
            $this->info("Dispatched sync job for: {$item->url}");
        }
    }
}
