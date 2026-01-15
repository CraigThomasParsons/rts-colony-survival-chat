<?php

namespace App\Console\Commands;

use App\Services\UndergroundGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateUnderground extends Command
{
    protected $signature = 'map:5underground
        {--width=48 : Grid width}
        {--height=48 : Grid height}
        {--seed= : Optional seed for deterministic output}
        {--fill=0.45 : Fill ratio for the random walk}
        {--walkers=3 : Number of random walkers}
        {--branch=0.12 : Chance (0-1) of spawning a new walker}
        {--output=underground : Storage folder for json output}';

    protected $description = 'Phase 5: generate the underground layer using a random-walk carving algorithm';

    public function handle(): int
    {
        $generator = new UndergroundGenerator(
            width: (int) $this->option('width'),
            height: (int) $this->option('height'),
            seed: $this->option('seed') !== null ? (int) $this->option('seed') : null,
            fillRatio: (float) $this->option('fill'),
            walkerCount: (int) $this->option('walkers'),
            branchChance: (float) $this->option('branch'),
        );

        $payload = $generator->generate();

        $disk = Storage::disk('local');
        $folder = trim($this->option('output'), '/');
        $disk->makeDirectory($folder);

        $filename = sprintf(
            '%s/underground_%s_%s.json',
            $folder,
            Str::slug($payload['meta']['seed']),
            now()->format('Ymd_His')
        );

        $disk->put($filename, json_encode($payload, JSON_PRETTY_PRINT));

        $counts = $payload['counts'];

        $this->info('Underground layout generated successfully.');
        $this->line(sprintf(
            '- Floors: %d | Diggable walls: %d | Collapsed sections: %d',
            $counts['floor'] ?? 0,
            $counts['diggable'] ?? 0,
            $counts['collapsed'] ?? 0
        ));
        $this->line(sprintf(
            '- Resource nodes: %d (stone piles + ore veins)',
            $counts['resources'] ?? 0
        ));
        $this->line('- Entry points: ' . implode(', ', array_map(
            fn($p) => "{$p['label']}({$p['x']},{$p['y']})",
            $payload['entry_points']
        )));
        $this->line("Layout saved to storage/app/{$filename}");

        return Command::SUCCESS;
    }
}
