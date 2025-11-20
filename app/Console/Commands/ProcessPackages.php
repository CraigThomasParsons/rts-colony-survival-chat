<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PackageImporterService;

class ProcessPackages extends Command
{
    protected $signature = 'packages:process';
    protected $description = 'Import and extract package zip files from /packages';

    public function handle(PackageImporterService $importer)
    {
        $processed = $importer->processNewPackages();

        if (empty($processed)) {
            $this->info("No new packages found.");
            return;
        }

        foreach ($processed as $pkg) {
            $this->info("Processed: " . $pkg['package'] . " → " . $pkg['path']);
        }
    }
}
