<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Map;

/**
 * Job: FinalizeMapGeneration
 * Clears the is_generating flag when the chained generation pipeline finishes successfully.
 */
class FinalizeMapGeneration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $mapId;

    public function __construct(string $mapId)
    {
        $this->mapId = $mapId;
    }

    public function handle(): void
    {
        $map = Map::find($this->mapId);
        if ($map) {
            $map->is_generating = false;
            $map->save();
        }
    }
}
