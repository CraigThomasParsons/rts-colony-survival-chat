<?php

namespace App\Services;

/**
 * UndergroundGenerator
 *
 * Generates a procedural underground layout using a random-walk algorithm
 * plus a handful of deterministic post-processing passes that classify rooms,
 * corridors, diggable rock walls, and resource pockets.
 */
class UndergroundGenerator
{
    /** Width of the underground grid. */
    protected int $width;

    /** Height of the underground grid. */
    protected int $height;

    /** Seed for deterministic random walk. */
    protected int $seed;

    /** Ratio of tiles that become walkable via random walk. */
    protected float $fillRatio;

    /** Number of concurrent walkers carving the cavern. */
    protected int $walkerCount;

    /** Probability the current walker spawns a new branch. */
    protected float $branchChance;

    /** Callback invoked whenever a tile changes (for streaming previews). */
    protected $tileUpdatedCallback = null;

    public function __construct(
        int $width = 48,
        int $height = 48,
        ?int $seed = null,
        float $fillRatio = 0.45,
        int $walkerCount = 3,
        float $branchChance = 0.12,
    ) {
        $this->width = max(16, $width);
        $this->height = max(16, $height);
        $this->seed = $seed ?? random_int(PHP_INT_MIN, PHP_INT_MAX);
        $this->fillRatio = min(0.75, max(0.25, $fillRatio));
        $this->walkerCount = max(1, $walkerCount);
        $this->branchChance = min(0.45, max(0.01, $branchChance));

        mt_srand($this->seed);
    }

    /**
     * Attach a callback that receives ($x, $y, $type) each time a tile changes.
     */
    /**
     * Register a streaming callback that receives ($x, $y, $type) whenever a tile changes.
     */
    public function onTileUpdated(?callable $callback): self
    {
        $this->tileUpdatedCallback = $callback;
        return $this;
    }

    /**
     * Emit tile updates to listening UIs/commands.
     */
    protected function emitTile(int $x, int $y, string $type): void
    {
        if ($this->tileUpdatedCallback) {
            ($this->tileUpdatedCallback)($x, $y, $type);
        }
    }

    /**
     * Generate the entire underground layout description.
     */
    /**
     * Run the full underground generation process.
     */
    public function generate(): array
    {
        $grid = array_fill(0, $this->height, array_fill(0, $this->width, 'solid'));
        $entryPoints = $this->seedEntryPoints();

        foreach ($entryPoints as $entry) {
            $grid[$entry['y']][$entry['x']] = 'floor';
            $this->emitTile($entry['x'], $entry['y'], 'entry');
        }

        $this->randomWalkCarve($grid, $entryPoints);
        $this->markDiggableWalls($grid);

        $rooms = $this->identifyRooms($grid);
        $features = $this->labelFeatureRooms($rooms, $entryPoints);

        $resourceNodes = $this->scatterResources($grid, $rooms);
        $collapsedSections = $this->markCollapsedSections($grid);

        return [
            'meta' => [
                'width' => $this->width,
                'height' => $this->height,
                'seed' => $this->seed,
                'fill_ratio' => $this->fillRatio,
                'walker_count' => $this->walkerCount,
            ],
            'entry_points' => $entryPoints,
            'grid' => $grid,
            'features' => $features,
            'resource_nodes' => $resourceNodes,
            'collapsed_sections' => $collapsedSections,
            'counts' => $this->summarizeCounts($grid, $resourceNodes, $collapsedSections),
        ];
    }

    /**
     * Choose deterministic entry points along edges.
     */
    protected function seedEntryPoints(): array
    {
        return [
            ['x' => 1, 'y' => 1, 'label' => 'main_mineshaft'],
            ['x' => $this->width - 2, 'y' => (int) floor($this->height / 3), 'label' => 'service_tunnel'],
            ['x' => (int) floor($this->width / 4), 'y' => $this->height - 2, 'label' => 'forgotten_shaft'],
        ];
    }

    /**
     * Perform a multi-walker "drunkard" random walk to carve the base cavern.
     */
    protected function randomWalkCarve(array &$grid, array $entryPoints): void
    {
        $targetCarved = (int) round($this->width * $this->height * $this->fillRatio);
        $carved = 0;

        $walkers = [];
        foreach ($entryPoints as $entry) {
            $walkers[] = ['x' => $entry['x'], 'y' => $entry['y']];
        }

        while (count($walkers) < $this->walkerCount) {
            $walkers[] = [
                'x' => mt_rand(1, $this->width - 2),
                'y' => mt_rand(1, $this->height - 2),
            ];
        }

        $maxIterations = $targetCarved * 20;
        $iterations = 0;

        while ($carved < $targetCarved && $iterations < $maxIterations) {
            foreach ($walkers as &$walker) {
                if ($grid[$walker['y']][$walker['x']] === 'solid') {
                    $grid[$walker['y']][$walker['x']] = 'floor';
                    $carved++;
                    $this->emitTile($walker['x'], $walker['y'], 'floor');
                }

                if (mt_rand() / mt_getrandmax() < $this->branchChance) {
                    $walkers[] = ['x' => $walker['x'], 'y' => $walker['y']];
                }

                $direction = mt_rand(0, 3);
                if ($direction === 0) {
                    $walker['y'] = max(1, $walker['y'] - 1);
                } elseif ($direction === 1) {
                    $walker['y'] = min($this->height - 2, $walker['y'] + 1);
                } elseif ($direction === 2) {
                    $walker['x'] = max(1, $walker['x'] - 1);
                } else {
                    $walker['x'] = min($this->width - 2, $walker['x'] + 1);
                }
            }

            $iterations++;
        }
    }

    /**
     * Any solid tile adjacent to a floor becomes a diggable wall.
     */
    protected function markDiggableWalls(array &$grid): void
    {
        for ($y = 1; $y < $this->height - 1; $y++) {
            for ($x = 1; $x < $this->width - 1; $x++) {
                if ($grid[$y][$x] !== 'solid') {
                    continue;
                }

                if ($this->hasAdjacentFloor($grid, $x, $y)) {
                    $grid[$y][$x] = 'diggable';
                    $this->emitTile($x, $y, 'diggable');
                }
            }
        }
    }

    protected function hasAdjacentFloor(array $grid, int $x, int $y): bool
    {
        $neighbors = [[1,0],[-1,0],[0,1],[0,-1]];
        foreach ($neighbors as [$dx, $dy]) {
            $nx = $x + $dx;
            $ny = $y + $dy;
            if ($nx < 0 || $ny < 0 || $nx >= $this->width || $ny >= $this->height) {
                continue;
            }
            if ($grid[$ny][$nx] === 'floor') {
                return true;
            }
        }
        return false;
    }

    /**
     * Flood-fill floor tiles into contiguous rooms.
     */
    protected function identifyRooms(array $grid): array
    {
        $visited = array_fill(0, $this->height, array_fill(0, $this->width, false));
        $rooms = [];

        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                if ($grid[$y][$x] !== 'floor' || $visited[$y][$x]) {
                    continue;
                }

                $queue = [[$x, $y]];
                $visited[$y][$x] = true;
                $tiles = [];

                while ($queue) {
                    [$cx, $cy] = array_shift($queue);
                    $tiles[] = ['x' => $cx, 'y' => $cy];

                    foreach ([[1,0],[-1,0],[0,1],[0,-1]] as [$dx, $dy]) {
                        $nx = $cx + $dx;
                        $ny = $cy + $dy;
                        if ($nx < 0 || $ny < 0 || $nx >= $this->width || $ny >= $this->height) {
                            continue;
                        }
                        if ($grid[$ny][$nx] !== 'floor' || $visited[$ny][$nx]) {
                            continue;
                        }
                        $visited[$ny][$nx] = true;
                        $queue[] = [$nx, $ny];
                    }
                }

                $rooms[] = [
                    'size' => count($tiles),
                    'tiles' => $tiles,
                ];
            }
        }

        usort($rooms, fn($a, $b) => $b['size'] <=> $a['size']);

        return $rooms;
    }

    /**
     * Map the largest contiguous areas to named feature rooms.
     */
    protected function labelFeatureRooms(array $rooms, array $entryPoints): array
    {
        $features = [];
        $labels = [
            'staging_chamber',
            'enemy_keep',
            'banner_hall',
            'lower_outpost',
        ];

        foreach ($rooms as $index => $room) {
            $label = $labels[$index] ?? 'cavern';
            $features[] = [
                'type' => $label,
                'size' => $room['size'],
                'tiles' => $room['tiles'],
            ];
        }

        $features[] = [
            'type' => 'entry_zones',
            'tiles' => $entryPoints,
        ];

        return $features;
    }

    /**
     * Drop stone piles and ore/gem clusters following corridor/room rules.
     */
    protected function scatterResources(array &$grid, array $rooms): array
    {
        $resources = [];

        for ($y = 1; $y < $this->height - 1; $y++) {
            for ($x = 1; $x < $this->width - 1; $x++) {
                if ($grid[$y][$x] !== 'floor') {
                    continue;
                }

                $floorNeighbors = 0;
                foreach ([[1,0],[-1,0],[0,1],[0,-1]] as [$dx, $dy]) {
                    if ($grid[$y + $dy][$x + $dx] === 'floor') {
                        $floorNeighbors++;
                    }
                }

                if ($floorNeighbors <= 2 && mt_rand(0, 100) < 30) {
                    $resources[] = [
                        'type' => 'stone_pile',
                        'x' => $x,
                        'y' => $y,
                        'amount' => mt_rand(10, 25),
                    ];
                    $this->emitTile($x, $y, 'resource:stone_pile');
                }
            }
        }

        $veinTypes = ['gold_vein', 'gem_cluster', 'rare_crystal'];
        foreach (array_slice($rooms, 0, count($veinTypes)) as $index => $room) {
            if (empty($room['tiles'])) {
                continue;
            }

            $center = $room['tiles'][array_rand($room['tiles'])];
            $clusterSize = mt_rand(5, 12);
            $veinTiles = $this->growCluster($room['tiles'], $center, $clusterSize);

            foreach ($veinTiles as $tile) {
                $resources[] = [
                    'type' => $veinTypes[$index],
                    'x' => $tile['x'],
                    'y' => $tile['y'],
                    'amount' => mt_rand(60, 120),
                ];
                $this->emitTile($tile['x'], $tile['y'], 'resource:' . $veinTypes[$index]);
            }
        }

        return $resources;
    }

    protected function growCluster(array $roomTiles, array $center, int $limit): array
    {
        $cluster = [];
        $queue = [$center];
        $visited = [];
        $key = fn($x, $y) => "{$x}-{$y}";

        $roomLookup = [];
        foreach ($roomTiles as $tile) {
            $roomLookup[$key($tile['x'], $tile['y'])] = true;
        }

        while ($queue && count($cluster) < $limit) {
            $tile = array_shift($queue);
            $tileKey = $key($tile['x'], $tile['y']);

            if (!isset($roomLookup[$tileKey]) || isset($visited[$tileKey])) {
                continue;
            }

            $visited[$tileKey] = true;
            $cluster[] = $tile;

            foreach ([[1,0],[-1,0],[0,1],[0,-1]] as [$dx, $dy]) {
                $queue[] = ['x' => $tile['x'] + $dx, 'y' => $tile['y'] + $dy];
            }
        }

        return $cluster;
    }

    /**
     * Promote a handful of diggable walls to collapsed obstacles that must be dug out.
     */
    protected function markCollapsedSections(array &$grid): array
    {
        $candidates = [];
        for ($y = 1; $y < $this->height - 1; $y++) {
            for ($x = 1; $x < $this->width - 1; $x++) {
                if ($grid[$y][$x] === 'diggable') {
                    $candidates[] = ['x' => $x, 'y' => $y];
                }
            }
        }

        shuffle($candidates);
        $collapsed = array_slice($candidates, 0, min(10, count($candidates)));

        foreach ($collapsed as $tile) {
            $grid[$tile['y']][$tile['x']] = 'collapsed';
            $this->emitTile($tile['x'], $tile['y'], 'collapsed');
        }

        return $collapsed;
    }

    protected function summarizeCounts(array $grid, array $resources, array $collapsed): array
    {
        $counts = [
            'floor' => 0,
            'diggable' => 0,
            'collapsed' => count($collapsed),
            'solid' => 0,
            'resources' => count($resources),
        ];

        foreach ($grid as $row) {
            foreach ($row as $tile) {
                $counts[$tile] = ($counts[$tile] ?? 0) + 1;
            }
        }

        return $counts;
    }
}
