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
    protected string $packageDir;

    public function __construct()
    {
        // Default directory for packages relative to the project base path.
        $this->packageDir = 'packages';
    }

    public function processNewPackages(): array
    {
        $processed = [];
        $packagesDir = base_path($this->packageDir);

        if (!is_dir($packagesDir)) {
            Log::warning("📦 [PackageImporter] Packages directory {$packagesDir} does not exist.");
            return $processed;
        }

        foreach (glob($packagesDir . '/*.zip') as $zipFile) {
            $packageName = basename($zipFile);
            $safeName = pathinfo($packageName, PATHINFO_FILENAME);
            $extractPath = "{$packagesDir}/{$safeName}";

            Log::info("📦 [PackageImporter] Processing {$packageName}");

            File::deleteDirectory($extractPath);
            File::makeDirectory($extractPath, 0755, true);

            $zip = new ZipArchive();
            if ($zip->open($zipFile) !== true) {
                Log::error("❌ [PackageImporter] Unable to open {$packageName}");
                continue;
            }

            if (!$zip->extractTo($extractPath)) {
                Log::error("❌ [PackageImporter] Failed extracting {$packageName}");
                $zip->close();
                continue;
            }

            $zip->close();
            $processed[] = [
                'package' => $packageName,
                'path' => $extractPath,
            ];

            $archiveName = $zipFile . '.imported';
            if (File::exists($archiveName)) {
                File::delete($archiveName);
            }

            File::move($zipFile, $archiveName);
        }

        return $processed;
    }
}

// AI Notes:
// - This service imports packages from zip files.
// - It extracts the contents of the zip files to a directory.
// - It optionally archives the original zip files.
