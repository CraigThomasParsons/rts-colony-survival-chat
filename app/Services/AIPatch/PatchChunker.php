<?php

namespace App\Services\AIPatch;

use Illuminate\Support\Facades\File;

class PatchChunker
{
    /**
     * Create chunks by scanning selected project folders and breaking content into safe sizes.
     */
    public static function createChunks(array $dirs, int $maxLength = 12000)
    {
        $chunks = [];
        $current = '';

        foreach ($dirs as $dir) {
            foreach (File::allFiles($dir) as $file) {
                $content =
                    "FILENAME: {$file->getRealPath()}\n\n" .
                    File::get($file->getRealPath());

                // If current chunk is too large, start a new one
                if (strlen($current) + strlen($content) > $maxLength) {
                    $chunks[] = $current;
                    $current = '';
                }

                $current .= $content . "\n\n";
            }
        }

        if (trim($current) !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }
}
