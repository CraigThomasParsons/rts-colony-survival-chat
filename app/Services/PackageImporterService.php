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

        foreach (glob(base_path($this->packageDir . '/*.zip')) as $zipFile) {
            $packageName = basename($zipFile);
            Log::info("📦 [PackageImporter] Found package: {$packageName}");

            $extractPath = base_path($this->packageDir . '/' . pathinfo($packageName, PATHINFO_FILENAME));

            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0777, true);
            }

            //Log::info("📦 [PackageImporter] Extracted {$packageName} → {$extractPath}");
                // // Extract zip
                // $zip = new ZipArchive;
                // if ($zip->open($file) === true) {

                //     $extractPath = "{$packagesDir}/{$safeName}";
                    
                //     if (!file_exists($extractPath)) {
                //         mkdir($extractPath, 0755, true);
                //     }

                //     $zip->extractTo($extractPath);
                //     $zip->close();

                //     $this->info("📦 Extracted to {$extractPath}");

                //     // ────────────────────────────────────────────
                //     // 🔥 RSYNC into project (safe mode)
                //     // ────────────────────────────────────────────
                //     $this->info("🔄 Applying package via rsync…");

                //     $exclude = [
                //         'vendor',
                //         'node_modules',
                //         '.env',
                //         '.git',
                //         'packages',
                //     ];

                //     $excludeArgs = '';
                //     foreach ($exclude as $ex) {
                //         $excludeArgs .= " --exclude={$ex}";
                //     }

                //     $cmd = "rsync -av --delete {$excludeArgs} {$extractPath}/ /var/www/html/";

                //     exec($cmd, $output, $result);

                //     if ($result !== 0) {
                //         $this->error("❌ rsync failed with code {$result}");
                //     } else {
                //         $this->info("✅ rsync applied successfully");
                //     }
                // }

            // Extract zip
            $zip = new ZipArchive;
            if ($zip->open($file) === true) {

                $extractPath = "{$packagesDir}/{$safeName}";
                
                if (!file_exists($extractPath)) {
                    mkdir($extractPath, 0755, true);
                }

                $zip->extractTo($extractPath);
                $zip->close();

                $this->info("📦 Extracted to {$extractPath}");

                // ────────────────────────────────────────────
                // 🔥 RSYNC into project (safe mode)
                // ────────────────────────────────────────────
                $this->info("🔄 Applying package via rsync…");

                $exclude = [
                    'vendor',
                    'node_modules',
                    '.env',
                    '.git',
                    'packages',
                ];

                $excludeArgs = '';
                foreach ($exclude as $ex) {
                    $excludeArgs .= " --exclude={$ex}";
                }

                $cmd = "rsync -av --delete {$excludeArgs} {$extractPath}/ /var/www/html/";

                exec($cmd, $output, $result);

                if ($result !== 0) {
                    $this->error("❌ rsync failed with code {$result}");
                } else {
                    $this->info("✅ rsync applied successfully");
                }
            }

            // Optional: archive old zip
            rename($zipFile, $zipFile . '.imported');
        }

        return $processed;
    }
}

// AI Notes:
// - This service imports packages from zip files.
// - It extracts the contents of the zip files to a directory.
// - It optionally archives the original zip files.
