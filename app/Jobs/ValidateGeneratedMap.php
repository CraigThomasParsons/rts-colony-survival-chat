<?php

namespace App\Jobs;

use App\Models\Map;
use App\Services\MapValidator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ValidateGeneratedMap implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $mapId;

    public function __construct(string $mapId)
    {
        $this->mapId = $mapId;
    }

    public function handle(MapValidator $validator): void
    {
        $map = Map::find($this->mapId);
        if (!$map) {
            return;
        }

        // Mark map as validating while we perform checks.
        $map->status = 'validating';
        $map->save();

        $result = $validator->validate($map);
        $map->validated_at = Carbon::now();

        if (($result['ok'] ?? false) === true) {
            $map->status = 'ready';
            $map->validation_errors = null;
        } else {
            $map->status = 'failed';
            $map->validation_errors = $result['errors'] ?? ['validation failed'];
        }

        // Always clear generating lock at the end of the pipeline.
        $map->is_generating = false;
        $map->save();
    }
}
