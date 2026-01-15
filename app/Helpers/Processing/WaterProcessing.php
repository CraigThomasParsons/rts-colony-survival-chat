<?php

namespace App\Helpers\Processing;

use App\Helpers\MapDatabase\WaterProcessingMapDatabaseLayer;
use App\Helpers\ModelHelpers\Tile;

/**
 * Should go into further details with water tiles.
 * This will label the edges.
 * 
 * So for example if its an edge where water meets land:
 * A calculation to see if its a corner or edge.
 * Then label that tile accordingly.
 */
class WaterProcessing
{
    const WATER_ID = 3;

    protected $map;
    protected $waterTileLocations;
    protected $waterProcessingDatabaseLayer;

    /**
     * Constructor - no dependencies, use setters instead.
     */
    public function __construct()
    {
        // Intentionally empty - use setter injection
    }

    /**
     * Sets the value of waterProcessingDatabaseLayer.
     *
     * @param mixed $waterProcessingDatabaseLayer the water processing database layer
     *
     * @return self
     */
    public function setWaterProcessingDatabaseLayer($waterProcessingDatabaseLayer)
    {
        $this->waterProcessingDatabaseLayer = $waterProcessingDatabaseLayer;

        return $this;
    }

    /**
     * Figures out where the water tiles meets land and
     * then determines which tile should be used.
     *
     * @return void
     */
    public function waterTiles()
    {
        $arrArrTiles = $this->waterProcessingDatabaseLayer->getArrTiles();

        if (is_array($this->waterTileLocations)) {
            foreach ($this->waterTileLocations as $intXaxisCoordinate => $row) {
                foreach ($row as $intYaxisCoordinate => $discard) {
                    if(isset($arrArrTiles[$intXaxisCoordinate][$intYaxisCoordinate])) {
                        $tile = $arrArrTiles[$intXaxisCoordinate][$intYaxisCoordinate];
                    }

                    // Make sure that we are only changing water tiles.
                    if ($tile->getStrTypeId() == self::WATER_ID) {

                        $topNeighbor    = $this->fetchTileForWaterChecks($intXaxisCoordinate, $intYaxisCoordinate + 1);
                        $rightNeighbor  = $this->fetchTileForWaterChecks($intXaxisCoordinate + 1, $intYaxisCoordinate);
                        $bottomNeighbor = $this->fetchTileForWaterChecks($intXaxisCoordinate, $intYaxisCoordinate - 1);
                        $leftNeighbor   = $this->fetchTileForWaterChecks($intXaxisCoordinate - 1, $intYaxisCoordinate);

                        $right = $intXaxisCoordinate + 1;
                        $left = $intXaxisCoordinate - 1;
                        $up = $intYaxisCoordinate + 1;
                        $down = $intYaxisCoordinate - 1;

                        // Diagonal neighbors.
                        $topRightNeighbor    = $this->fetchTileForWaterChecks($right, $up);
                        $topLeftNeighbor     = $this->fetchTileForWaterChecks($left, $up);
                        $bottomRightNeighbor = $this->fetchTileForWaterChecks($right, $down);
                        $bottomLeftNeighbor  = $this->fetchTileForWaterChecks($left, $down);

                        // Check for convex corners.
                        if ($topNeighbor->notWater() && $rightNeighbor->notWater()) {
                            $tile->setTileDisplayType('TopRightConvexedCorner-WaterTile');
                            $tile->tileTypeId = 16;

                        } elseif ($bottomNeighbor->notWater() && $leftNeighbor->notWater()) {
                            $tile->setTileDisplayType('BottomLeftConvexedCorner-WaterTile');
                            $tile->tileTypeId = 17;

                        } elseif ($topNeighbor->notWater() && $leftNeighbor->notWater()) {
                            $tile->setTileDisplayType('TopLeftConvexedCorner-WaterTile');
                            $tile->tileTypeId = 18;

                        } elseif ($bottomNeighbor->notWater() && $rightNeighbor->notWater()) {
                            $tile->setTileDisplayType('BottomRightConvexedCorner-WaterTile');
                            $tile->tileTypeId = 19;

                        } elseif ($topRightNeighbor->notWater() && $topNeighbor->isWater() && $rightNeighbor->isWater()) {
                            $tile->setTileDisplayType('TopRightConcaveCorner-WaterTile');
                            $tile->tileTypeId = 20;

                        } elseif ($topLeftNeighbor->notWater() && $topNeighbor->isWater() && $leftNeighbor->isWater()) {
                            $tile->setTileDisplayType('TopLeftConcaveCorner-WaterTile');
                            $tile->tileTypeId = 21;

                        } elseif ($bottomRightNeighbor->notWater() && $bottomNeighbor->isWater() && $rightNeighbor->isWater()) {
                            $tile->setTileDisplayType('bottomRightConcaveCorner-WaterTile');
                            $tile->tileTypeId = 22;

                        } elseif ($bottomLeftNeighbor->notWater() && $bottomNeighbor->isWater() && $leftNeighbor->isWater()) {
                            $tile->setTileDisplayType('bottomLeftConcaveCorner-WaterTile');
                            $tile->tileTypeId = 23;

                        } elseif ($topNeighbor->notWater()) {
                            $tile->setTileDisplayType('topEdge-WaterTile');
                            $tile->tileTypeId = 24;

                        } elseif ($rightNeighbor->notWater()) {
                            $tile->setTileDisplayType('rightEdge-WaterTile');
                            $tile->tileTypeId = 25;

                        } elseif ($bottomNeighbor->notWater()) {
                            $tile->setTileDisplayType('bottomEdge-WaterTile');
                            $tile->tileTypeId = 26;

                        } elseif ($leftNeighbor->notWater()) {
                            $tile->setTileDisplayType('leftEdge-WaterTile');
                            $tile->tileTypeId = 27;

                        } else {
                            $tile->setTileDisplayType('inner-WaterTile');
                            $tile->tileTypeId = 3;
                        }
                        $tile->save();
                    }
                }
            }
        }
        return $this;
    }

    /**
     * If a tile isn't available return a Water Tile object.
     *
     */
    protected function fetchTileForWaterChecks($intXaxisCoordinate, $intYaxisCoordinate)
    {
        // Get the tile to see if it isn't a waters.
        $arrArrTiles = $this->waterProcessingDatabaseLayer->getArrTiles();

        if (isset($arrArrTiles[$intXaxisCoordinate][$intYaxisCoordinate])) {
            $tileNeighbor = $arrArrTiles[$intXaxisCoordinate][$intYaxisCoordinate];

        } else {
            // Consider this a water tile if the tile isn't available.
            $tileNeighbor = new Tile($intXaxisCoordinate, $intYaxisCoordinate);
            $tileNeighbor->setStrType('Water');
        }

        return $tileNeighbor;
    }

    /**
     * Gets the value of map.
     *
     * @return mixed
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Sets the value of map.
     *
     * @param mixed $map the map
     *
     * @return self
     */
    public function setMap($map)
    {
        $this->map = $map;

        return $this;
    }

    /**
     * Gets the value of waterTileLocations.
     *
     * @return mixed
     */
    public function getWaterTileLocations()
    {
        return $this->waterTileLocations;
    }

    /**
     * Sets the value of waterTileLocations.
     *
     * @param mixed $waterTileLocations the water tile locations
     *
     * @return self
     */
    public function setWaterTileLocations($waterTileLocations)
    {
        $this->waterTileLocations = $waterTileLocations;

        return $this;
    }
}
