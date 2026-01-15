<?php

namespace App\Http\Controllers;

use App\Models\Map;
use App\Helpers\MapDatabase\MapRepository;
use App\Helpers\MapDatabase\MapHelper;
use App\Helpers\Factories\MapGeneratorFactory;
use App\Helpers\MapGenerators\FaultLineAlgorithm;
use App\Helpers\Processing\TreeProcessing;
use App\Helpers\Processing\MountainProcessing;
use App\Helpers\Processing\WaterProcessing;
use App\Helpers\MapDatabase\WaterProcessingMapDatabaseLayer;
use App\Models\MapStatus;
use App\Helpers\ModelHelpers\Map as MapMemory;
use App\Helpers\Processing\CellProcessing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\MapFirstStepGenerator;

class MapController extends Controller
{
    const DEFAULT_HEIGHT_MAP_GENERATOR = 'FaultLine';
    const DEFAULT_HEIGHT_MAP_SIZE = 30;

    /**
     * Cache MapStatus lookups so we only hit the database once per status name.
     */
    protected array $statusCache = [];

    /**
     * Display a listing of the resource.
     * 
     * Returning the index view.t
     * 
     * @return string
     */
    public function index()
    {
        return view('mapgen.index');
    }

    /**
     * Map generation editor hub: shows current map state and provides links
     * to each generation step plus live preview link.
     */
    public function editor(string $mapId)
    {
        $map = \App\Models\Map::findOrFail($mapId);

        $state = $map->state ?? 'Unknown';

        $steps = [
            ['label' => 'Step 1: Init / Height Map', 'url' => url("/Map/step1/$mapId/"), 'key' => 'step1'],
            ['label' => 'Step 2: Tiles From Cells', 'url' => url("/Map/step2/$mapId/"), 'key' => 'step2'],
            ['label' => 'Step 3: Trees First Pass', 'url' => route('mapgen.step3', ['mapId' => $mapId]), 'key' => 'step3'],
            ['label' => 'Preview Tiles', 'url' => route('mapgen.preview', ['mapId' => $mapId]), 'key' => 'preview'],
            ['label' => 'Step 4: Water Processing', 'url' => url("/Map/step4/$mapId/"), 'key' => 'step4'],
            ['label' => 'Step 5: Mountain Processing', 'url' => url("/Map/step5/$mapId/400"), 'key' => 'step5'],
        ];

        $isGenerating = false;

        if ((isset($map->is_generating) == true)) {
            $isGenerating = $map->is_generating;
        }

        return view('mapgen.editor', [
            'map' => $map,
            'mapId' => $mapId,
            'state' => $state,
            'steps' => $steps,
            'isGenerating' => $isGenerating,
        ]);
    }

    /**
     * Should run the height map generator and set up the basic cells.
     *
     * @param string $mapId Primary key of the map.
     *
     * @return view
     */
    public function runFirstStep(string $mapId)
    {
        $generator = new MapFirstStepGenerator();
        $generator->generate($mapId);

        // After step 1 redirect to editor hub for this map.
        return redirect()->route('map.editor', ['mapId' => $mapId]);
    }

    /**
     * Should run the height map generator and set up the basic cells.
     * Then show a preview of the height map.
     *
     * @param string $mapId Primary key of the map.
     *
     * @return view
     */
    public function runFirstStepAndPreview(string $mapId)
    {
        $generator = new MapFirstStepGenerator();
        $generator->generate($mapId);

        $map = \App\Models\Map::findOrFail($mapId);

        // Build a 2D array of cells keyed by Y then X
        $cells = [];
        $size = max((int)($map->coordinateX ?? 32), (int)($map->coordinateY ?? 32));

        $allCells = \App\Models\Cell::where('map_id', $mapId)->get();
        foreach ($allCells as $cell) {
            $y = (int)($cell->mapCoordinateY ?? 0);
            $x = (int)($cell->mapCoordinateX ?? 0);
            $cells[$y][$x] = $cell;
        }

        return view('mapgen.heightmap-preview', [
            'map' => $map,
            'cells' => $cells,
            'size' => $size,
            'nextRoute' => url('/Map/step2/' . $mapId . '/'),
        ]);
    }

    /**
     * This will run the tile step.
     * This will simply take the cells in the map
     * and change the child tiles to what the parent tile is.
     *
     * @param string $mapId Primary key of the map.
     *
     * @return void
     */
    public function runSecondStep(string $mapId)
    {
        $map = Map::findOrFail($mapId);

        // Tile creation started.
        $this->updateMapStatus($map, MapStatus::TILE_PROCESSING_STARTED);

        // All the cells in the current Map.
        $cells = MapRepository::findAllCells($mapId);

        // The reversed x and y made it easier to check if a row existed before iterating over it in the view.
        $tiles = MapRepository::findAllTilesReversedAxis($mapId);
        if ($tiles === false) {
            Log::warning("runSecondStep: MapRepository::findAllTilesReversedAxis returned false for {$mapId} before fallback");
        }

        // Fallback: if tiles are missing, build basic tiles from cells and re-fetch
        if ($tiles === false && is_array($cells) && count($cells) > 0) {
            Log::warning("runSecondStep: No tiles found for map {$mapId}. Creating tiles from cells.");

            // Flatten cell grid and create corresponding tile rows
            foreach ($cells as $x => $row) {
                foreach ($row as $y => $cell) {
                    // Insert tile directly to match MapRepository expectations
                    DB::table('tile')->insert([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'map_id' => $mapId,
                        'cell_id' => $cell->id ?? $cell->getId(),
                        'coordinateX' => $x,
                        'coordinateY' => $y,
                        'mapCoordinateX' => $x,
                        'mapCoordinateY' => $y,
                        'name' => $cell->name ?? 'Tile',
                        'description' => 'Auto-created from cells',
                        'tileType_id' => $cell->cellType_id ?? 1,
                    ]);
                }
            }

            // Re-fetch tiles after creating
            $tiles = MapRepository::findAllTilesReversedAxis($mapId);
            if ($tiles === false) {
                Log::error("runSecondStep: Still no tiles after fallback creation for {$mapId}");
                // Bail safely to avoid foreach on false
                $this->updateMapStatus($map, MapStatus::TILE_PROCESSING_STOPPED, 'step3');
                return redirect()->route('map.editor', ['mapId' => $mapId]);
            }
        }

        foreach ($tiles as $doesntMatter => $something) {
            foreach ($something as $doesntMatterEither => $tile) {

                $currentCell = $cells[$tile->getCellX()][$tile->getCellY()];

                if ($currentCell->name == 'Passable Land') {

                    $tile->name        = 'inner-Land';
                    $tile->description = 'Passable';
                    $tile->tileTypeId  = 1;
                } else if ($currentCell->name == 'Trees') {

                    $tile->name        = 'inner-Tree';
                    $tile->description = 'The default tree tile';
                    $tile->tileTypeId  = 29;
                } else if ($currentCell->name == 'Water') {

                    $tile->name        = 'inner-WaterTile';
                    $tile->description = 'The Inside Water Tile.';
                    $tile->tileTypeId  = 3;
                } else {

                    // Anything else becomes a Rock Tile.
                    $tile->name        = 'inner-Rock';
                    $tile->description = 'Rocky area.';
                    $tile->tileTypeId  = 2;
                }
                $tile->height = $currentCell->height;
                $tile->save();
            }
        }

        // Running this right after in prep for tree algorithms.
        $mapRecord = $map;

        // I might delete this section because it makes no sense.
        // There should not be trees at this point.
        $treeCells = MapRepository::findAllTreeCells($mapId);

        if ($treeCells !== false) {
            // Passing in tiles just doesn't actually matter at this point.
            // I won't be using the tiles in the hole punching process.
            $mapLoader = new MapHelper($mapRecord->id, $tiles, $treeCells);
            $mapLoader->holePuncher($mapId);
        }

        // Mark tile processing completed using canonical status constant and point to tree step.
        $this->updateMapStatus($map, MapStatus::TILE_PROCESSING_STOPPED, 'step3');
        return redirect()->route('map.editor', ['mapId' => $mapId]);
    }

    /**
     * Preview the current map tiles without running processing logic.
     * Provides a button to proceed to the next step.
     */
    public function preview(string $mapId)
    {
        $map = \App\Models\Map::findOrFail($mapId);

        // Build a 2D array of tiles keyed by Y then X
        $tiles = [];
        $size = max((int)($map->coordinateX ?? 32), (int)($map->coordinateY ?? 32));

        $allTiles = \App\Models\Tile::where('map_id', $mapId)->get();
        foreach ($allTiles as $tile) {
            $y = (int)($tile->mapCoordinateY ?? 0);
            $x = (int)($tile->mapCoordinateX ?? 0);
            $tiles[$y][$x] = $tile;
        }

        return view('mapgen.preview', [
            'map' => $map,
            'tiles' => $tiles,
            'size' => $size,
            'nextRoute' => url('/Map/step4/' . $mapId . '/'),
        ]);
    }

    public function runThirdStep(string $mapId)
    {
        // Create the tree processing class, which is the whole point of this step in the process.
        $size = SELF::DEFAULT_HEIGHT_MAP_SIZE;

        $map = Map::findOrFail($mapId);

        $tiles = MapRepository::findAllTiles($mapId);
        if ($tiles === false) {
            Log::error("runThirdStep: Tiles array is false for {$mapId}; TreeProcessing cannot proceed.");
            $this->updateMapStatus($map, MapStatus::TREE_FIRST_STEP, 'preview');
            return redirect()->route('map.editor', ['mapId' => $mapId]);
        }
        $allCells = MapRepository::findAllCells($mapId);
        if ($allCells === false) {
            Log::error("runThirdStep: Cells array is false for {$mapId}; TreeProcessing cannot proceed.");
            $this->updateMapStatus($map, MapStatus::TREE_FIRST_STEP, 'preview');
            return redirect()->route('map.editor', ['mapId' => $mapId]);
        }

        // Tree creation started.
        $this->updateMapStatus($map, MapStatus::TREE_FIRST_STEP, 'treeStepSecond');

        $mapLoader = new MapHelper($map->id, $tiles, $allCells);

        // Using this to process the tiles we need and start the work of randomizing tree tiles.
        $treeProcessing = new TreeProcessing($mapLoader);
        $treeProcessing->setMapLoader($mapLoader)->setIterations(20)->runJohnConwaysGameOfLife();
        $treeCells = MapRepository::findAllTreeCells($mapId);

        // Invert What we just did.
        foreach ($tiles as $doesntMatter => $something) {
            foreach ($something as $doesntMatterEither => $tile) {

                if ($tile->tileTypeId == 29) {

                    $tile->name        = 'inner-Land';
                    $tile->description = 'Passable';
                    $tile->tileTypeId  = 1;
                } else if ($tile->tileTypeId == 1) {

                    $tile->name        = 'inner-Tree';
                    $tile->description = 'The default tree tile';
                    $tile->tileTypeId  = 29;
                }

                $tile->save();
            }
        }
        $mapLoader->killAllTreesInCell($mapId);
        return redirect()->route('map.editor', ['mapId' => $mapId]);
    }

    /**
     * This will run the tree processing algorithm
     * with the second step settings.
     *
     * @param string $mapId Primary key of the map.
     *
     * @return void Does a redirect
     */
    public function runTreeStepTwo(string $mapId)
    {
        // Create the tree processing class, which is the whole point of this step in the process.
        $size = SELF::DEFAULT_HEIGHT_MAP_SIZE;
        $map = Map::findOrFail($mapId);
        $mapRecord = $map;

        // All the cells in the current Map.
        $cells = MapRepository::findAllCells($mapId);

        // All the tiles in the current map.
        $tiles     = MapRepository::findAllTiles($mapId);
        $treeCells = MapRepository::findAllTreeCells($mapId);

        $mapLoader = new MapHelper($mapRecord->id, $tiles, $cells);
        $mapLoader->holePuncher($mapId);

        //$map->state = "running second step in tree algorithm";

        // Tree creation started.
        $this->updateMapStatus($map, MapStatus::TREE_2ND_COMPLETED, 'treeStepThree');

        // Using this to process the tiles we need and start the work of randomizing tree tiles.
        $treeProcessing = new TreeProcessing($mapLoader);

        // Setting the amount of iterations to run when runJohnConwaysGameOfLife is called.
        $treeProcessing->setMapLoader($mapLoader)->setIterations(5);

        // Inverts the process. Life equals death.
        $treeProcessing->setBoolInvertSave(true);

        // This run should take a pretty long time.
        $treeProcessing->runJohnConwaysGameOfLife();

        // Purge Orphans will purge any tree tiles out on its own.
        // I ran the purgeOrphans twice, createLifeGrid is called twice as well.
        // I may have to get createLifeGrid to be cached.
        $treeProcessing->purgeOrphans(5);
        $treeProcessing->purgeOrphans(5);
        $treeProcessing->purgeOrphans(7);

        foreach ($tiles as $doesntMatter => $something) {
            foreach ($something as $doesntMatterEither => $tile) {

                $currentCell = $cells[$tile->getCellX()][$tile->getCellY()];

                if ($currentCell->name == 'Trees') {

                    $tile->name        = 'inner-Tree';
                    $tile->description = 'The default tree tile';
                    $tile->tileTypeId  = 29;
                } else if ($currentCell->name == 'Water') {

                    $tile->name        = 'inner-WaterTile';
                    $tile->description = 'The Inside Water Tile.';
                    $tile->tileTypeId  = 3;
                } else if ($currentCell->name == 'Impassable Rocks') {

                    // Anything else becomes a Rock Tile.
                    $tile->name        = 'inner-Rock';
                    $tile->description = 'Rocky area.';
                    $tile->tileTypeId  = 2;
                }
                $tile->save();
            }
        }
        return redirect()->route('map.editor', ['mapId' => $mapId]);
    }


    /**
     * This will run the last tree processing algorithm
     * with the second step settings.
     *
     * @param string $mapId Primary key of the map.
     *
     * @return void Does a redirect
     */
    public function runTreeStepThree(string $mapId)
    {
        // Create the tree processing class, which is the whole point of this step in the process.
        $size = SELF::DEFAULT_HEIGHT_MAP_SIZE;
        $map = Map::findOrFail($mapId);
        $mapRecord = $map;

        // All the tiles in the current map.
        $tiles     = MapRepository::findAllTiles($mapId);
        $treeCells = MapRepository::findAllTreeCells($mapId);
        // Missing previously: need cells collection for mapping tile->cell
        $cells     = MapRepository::findAllCells($mapId);

        $mapLoader = new MapHelper($mapRecord->id, $tiles, $treeCells);
        $mapLoader->holePuncher($mapId);

        // Tree creation started.
        $this->updateMapStatus($map, MapStatus::TREE_3RD_STARTED, 'step4');

        // Using this to process the tiles we need and start the work of randomizing tree tiles.
        $treeProcessing = new TreeProcessing($mapLoader);

        // Setting the amount of iterations to run when runJohnConwaysGameOfLife is called.
        $treeProcessing->setMapLoader($mapLoader)->setIterations(2);

        // Inverts the process. Life equals death.
        $treeProcessing->setBoolInvertSave(true);

        // This run should take a pretty long time.
        $treeProcessing->runJohnConwaysGameOfLife();

        // Purge Orphans will purge any tree tiles out on its own.
        // I ran the purgeOrphans twice, createLifeGrid is called twice as well.
        // I may have to get createLifeGrid to be cached.
        $treeProcessing->purgeOrphans(7);

        foreach ($tiles as $doesntMatter => $something) {
            foreach ($something as $doesntMatterEither => $tile) {

                $currentCell = $cells[$tile->getCellX()][$tile->getCellY()];

                if ($currentCell->name == 'Trees') {

                    $tile->name        = 'inner-Tree';
                    $tile->description = 'The default tree tile';
                    $tile->tileTypeId  = 29;
                } else if ($currentCell->name == 'Water') {

                    $tile->name        = 'inner-WaterTile';
                    $tile->description = 'The Inside Water Tile.';
                    $tile->tileTypeId  = 3;
                } else if ($currentCell->name == 'Impassable Rocks') {

                    // Anything else becomes a Rock Tile.
                    $tile->name        = 'inner-Rock';
                    $tile->description = 'Rocky area.';
                    $tile->tileTypeId  = 2;
                }
                $tile->save();
            }
        }
        return redirect()->route('map.editor', ['mapId' => $mapId]);
    }

    /**
     * This will run the water processing algorithm.
     * 
     * @param integer $mapId Primary key of the map.
     *
     * @return view
     */
    public function runMapLoad($mapId)
    {
        $size = SELF::DEFAULT_HEIGHT_MAP_SIZE;

        // The reversed x and y made it easier to check if a row existed before iterating over it in the view.
        $map = Map::findOrFail($mapId);
        $cells = MapRepository::findAllCells($mapId);
        $tiles = MapRepository::findAllTilesReversedAxis($mapId);

        $arrTemplateDependencies = array(
            'size' => $size,
            'cells' => $cells,
            'tiles' => $tiles,
            'mapId' => $mapId
        );

        if (!empty($map->next_step)) {
            $arrTemplateDependencies['next'] = 'mapgen.' . $map->next_step;
        }

        // echo "Going to run second step on MapId".$mapId;
        return view('mapgen.mapload', $arrTemplateDependencies);
    }


    /**
     * This will run the water processing algorithm.
     * 
     * @param integer $mapId Primary key of the map.
     *
     * @return view
     */
    public function runFourthStep($mapId)
    {
        $size = 38;
        $map = Map::findOrFail($mapId);
        $waterTileLocations = MapRepository::findAllWaterTileCoordinates($mapId);

        // Initializing dependencies.
        // Use the correct database layer helper for water processing
        $waterProcessingDatabaseLayer = new WaterProcessingMapDatabaseLayer($mapId);
        $waterProcessingDatabaseLayer->setMapId($mapId);

        $mapMemory = new MapMemory();

        // Load the map and reset the size.
        $mapMemory->setDatabaseRecord($map)->setSize($size);

        // Water Processing setup.
        $WaterProcessor = new WaterProcessing();
        $WaterProcessor->setWaterProcessingDatabaseLayer($waterProcessingDatabaseLayer)
            ->setWaterTileLocations($waterTileLocations)
            ->setMap($mapMemory);

        //echo "Going to run third step on MapId" . $mapId;
        //return Redirect::to('/Map/load/' . $mapId);
        $WaterProcessor->waterTiles();

        // Mark trees + water pipeline complete and guide the editor toward the mountain step.
        $this->updateMapStatus($map, MapStatus::TREE_GEN_COMPLETED, 'step5');
        return redirect()->route('map.editor', ['mapId' => $mapId]);
    }

    /**
     * This will run the mountain tile processor.
     *
     * @param integer $mapId Primary key of the map.
     *
     * @return view
     */
    public function runLastStep($mapId, $mountainLine)
    {
        $map = Map::findOrFail($mapId);

        // I would like to write something that can trace each groupings of mountain cells.
        // Then record the cell count, if the count is less that 5 then leave it alone.
        // It will be hard to write something that can trace the outside of each mountain.
        //http://www.geeksforgeeks.org/find-number-of-islands/

        // This means you that I'll have to run the cell processor over and over again.
        echo "Going to run the last step on MapId" . $mapId . '
        ';

        // Mountain cell and tile processor in one.
        // Use  db to grab all the cells found that are higher than the mountain line.
        // Loop through the tiles in all of these cells and start determining the tile types at the edges.
        // You'll need to establish the tile locations by the cells.
        $mountainProcessor = new MountainProcessing();
        $mountains = MapRepository::findAllMountainCells($mapId, $mountainLine);

        if ($mountains) {
            $mountainProcessor->init()
                ->setTiles(MapRepository::findAllTiles($mapId))
                ->setMountainCells($mountains)
                ->setMountainLine($mountainLine)
                ->createRidges();
        }

        // In the future I want to create four maps and stitch them together using WaveFunctionCollapse.
        return redirect()->route('map.editor', ['mapId' => $mapId]);
    }

    /**
     * Create a new Map record (basic defaults) and immediately redirect to step1 processing.
     */
    public function generateAndStepOne(Request $request)
    {
        $width = (int) $request->input('width', self::DEFAULT_HEIGHT_MAP_SIZE);
        $height = (int) $request->input('height', self::DEFAULT_HEIGHT_MAP_SIZE);

        // Create basic map row.
        $map = new Map();
        $map->name = 'Map ' . date('Ymd-His');
        $map->description = 'Auto-generated';
        $map->coordinateX = $width;
        $map->coordinateY = $height;
        $map->save();

        return redirect()->to("/Map/step1/{$map->id}/");
    }

    /**
     * Lightweight JSON status endpoint used by the map editor polling logic.
     * Returns current state string, is_generating boolean, and inferred nextRoute if available.
     */
    public function status($mapId)
    {
        $map = Map::find($mapId);
        if (!$map) {
            return response()->json(['error' => 'Map not found'], 404);
        }

        $state = $map->state ?? 'Unknown';

        $mapping = [
            MapStatus::CREATED_EMPTY => url("/Map/step1/{$mapId}/"),
            MapStatus::CELL_PROCESSING_STARTED => url("/Map/step1/{$mapId}/"),
            MapStatus::CELL_PROCESSING_FINNISHED => url("/Map/step2/{$mapId}/"),
            MapStatus::TILE_PROCESSING_STARTED => url("/Map/step2/{$mapId}/"),
            MapStatus::TILE_PROCESSING_STOPPED => route('mapgen.step3', ['mapId' => $mapId]),
            MapStatus::TREE_FIRST_STEP => url("/Map/treeStep2/{$mapId}/"),
            MapStatus::TREE_2ND_COMPLETED => url("/Map/treeStep3/{$mapId}/"),
            MapStatus::TREE_3RD_STARTED => url("/Map/step4/{$mapId}/"),
            MapStatus::TREE_GEN_COMPLETED => url("/Map/step4/{$mapId}/"),
        ];
        $nextRoute = $mapping[$state] ?? null;

        return response()->json([
            'mapId' => $mapId,
            'state' => $state,
            'is_generating' => (bool) ($map->is_generating ?? false),
            'nextRoute' => $nextRoute,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     * I think we have what we need to show the map.
     */
    public function show(Map $map)
    {
        //
    }

    /**
     * Show the form for editing the Maps
     * - Maybe can do this now with livewire.
     */
    public function edit(Map $map)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Map $map)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Map $map)
    {
        //
    }

    /**
     * Centralized helper for keeping the state, status id, next step, and generation lock in sync.
     */
    protected function updateMapStatus(Map $map, string $statusName, ?string $nextStep = null, ?bool $isGenerating = null): Map
    {
        $map->state = $statusName;
        $map->mapstatuses_id = $this->resolveStatusId($statusName);

        if ($nextStep !== null) {
            $map->next_step = $nextStep;
        }

        if ($isGenerating !== null) {
            $map->is_generating = $isGenerating;
        }

        $map->save();

        return $map;
    }

    /**
     * Cache status id lookups to avoid hammering the database during sequential pipeline steps.
     */
    protected function resolveStatusId(string $statusName): ?int
    {
        if (!array_key_exists($statusName, $this->statusCache)) {
            $this->statusCache[$statusName] = MapStatus::firstWhere('name', $statusName)?->id;
        }

        return $this->statusCache[$statusName];
    }
}
