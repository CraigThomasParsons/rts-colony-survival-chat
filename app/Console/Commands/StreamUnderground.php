<?php

namespace App\Console\Commands;

use App\Services\UndergroundGenerator;
use Illuminate\Console\Command;

class StreamUnderground extends Command
{
    protected $signature = 'map:stream-underground
        {--width=48 : Grid width}
        {--height=48 : Grid height}
        {--seed= : Optional deterministic seed}
        {--delay=30000 : Microsecond delay between frames}
        {--fill=0.45 : Random walk fill ratio}
        {--walkers=3 : Number of walkers}
        {--branch=0.12 : Chance of walker branching}';

    protected $description = 'Preview an underground layout while it is being generated (ASCII stream)';

    /**
     * Stream the random-walk generator as it carves the cavern.
     */
    public function handle(): int
    {
        $width  = max(16, (int) $this->option('width'));
        $height = max(16, (int) $this->option('height'));
        $delay  = max(0, (int) $this->option('delay'));

        $grid = array_fill(0, $height, array_fill(0, $width, ' '));

        $render = function () use (&$grid) {
            $this->output->write("\033[H");
            foreach ($grid as $row) {
                $this->line(implode('', $row));
            }
        };

        $charFor = function (string $type): string {
            return match (true) {
                $type === 'floor' => '.',
                $type === 'diggable' => '#',
                $type === 'collapsed' => 'X',
                $type === 'entry' => 'E',
                str_starts_with($type, 'resource:stone_pile') => 'o',
                str_starts_with($type, 'resource:gold_vein') => '$',
                str_starts_with($type, 'resource:gem_cluster') => '*',
                str_starts_with($type, 'resource:rare_crystal') => '*',
                default => ' ',
            };
        };

        $this->output->write("\033[2J\033[H\033[?25l");

        $generator = (new UndergroundGenerator(
            width: $width,
            height: $height,
            seed: $this->option('seed') !== null ? (int) $this->option('seed') : null,
            fillRatio: (float) $this->option('fill'),
            walkerCount: (int) $this->option('walkers'),
            branchChance: (float) $this->option('branch'),
        ))->onTileUpdated(function ($x, $y, $type) use (&$grid, $charFor, $render, $delay) {
            if (!isset($grid[$y][$x])) {
                return;
            }

            $grid[$y][$x] = $charFor($type);
            $render();

            if ($delay > 0) {
                usleep($delay);
            }
        });

        $payload = $generator->generate();

        $this->output->write("\033[?25h");
        $this->line('');
        $this->info('Preview complete!');
        $counts = $payload['counts'];
        $this->line(sprintf(
            'Floors: %d | Diggable walls: %d | Collapsed: %d | Resources: %d',
            $counts['floor'] ?? 0,
            $counts['diggable'] ?? 0,
            $counts['collapsed'] ?? 0,
            $counts['resources'] ?? 0
        ));
        $this->line('Entry points: ' . implode(', ', array_map(
            fn ($p) => "{$p['label']}({$p['x']},{$p['y']})",
            $payload['entry_points']
        )));

        return Command::SUCCESS;
    }
}
