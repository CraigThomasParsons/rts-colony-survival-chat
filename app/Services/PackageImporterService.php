<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use ZipArchive;

/**
 * PackageImporterService
 *
 * Service to handle importing and processing of package zip files. 
 *
 * Intention:
 * - Scan a directory for new zip files.
 * - Extract the contents of each zip file.
 * - Optionally archive the original zip file.
 */
class PackageImporterService
{
    public function processNewPackages(): void
    {
        $packagesDir = base_path('packages');

        if (!file_exists($packagesDir)) {
            Log::info("📦 packages directory does not exist, skipping.");
            return;
        }

        $files = scandir($packagesDir);

        foreach ($files as $fileName) {

            if (!str_ends_with($fileName, '.zip')) {
                continue;
            }

            // full path to the zip file
            $filePath = "{$packagesDir}/{$fileName}";
            $safeName = pathinfo($fileName, PATHINFO_FILENAME);

            Log::info("📦 Processing package zip: {$fileName}");

            // Check if already imported
            if (file_exists("{$packagesDir}/{$fileName}.imported")) {
                Log::info("📦 Package {$fileName} already imported — skipping.");
                continue;
            }

            // Extract zip
            $zip = new ZipArchive;

            if ($zip->open($filePath) === true) {

                $extractPath = "{$packagesDir}/{$safeName}";
                
                if (!file_exists($extractPath)) {
                    mkdir($extractPath, 0755, true);
                }

                Log::info("📦 Extracting {$fileName} into {$extractPath}...");
                $zip->extractTo($extractPath);
                $zip->close();

                // Mark as imported
                rename(
                    $filePath,
                    "{$packagesDir}/{$fileName}.imported"
                );

                Log::info("📦 Extraction complete. Marked {$fileName} as imported.");

                // Run rsync (no delete)
                $this->syncPackage($extractPath);

            } else {
                Log::error("❌ Failed to open zip file: {$filePath}");
            }
        }
    }

    private function syncPackage(string $packagePath): void
    {
        $destination = base_path();

        $cmd = sprintf(
            'rsync -avr "%s/" "%s/" --exclude vendor --exclude node_modules --exclude .env --exclude packages',
            $packagePath,
            $destination
        );

        Log::info("🔄 Running rsync: {$cmd}");

        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            Log::error("❌ Rsync failed with code {$returnVar}");
        } else {
            Log::info("✅ Rsync completed successfully.");
        }
    }
}

// AI Notes:
// - This service imports packages from zip files.
// - It extracts the contents of the zip files to a directory.
// - It optionally archives the original zip files.
