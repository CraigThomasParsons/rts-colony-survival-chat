<?php

namespace App\Services\AIPatch;

class PatchMerger
{
    /**
     * Merge all collected patch parts into a single unified diff.
     * 
     * Future improvements:
     * - detect duplicate hunks
     * - handle context overlap
     * - unify headers
     */
    public static function merge(string $text)
    {
        return $text;
    }
}
