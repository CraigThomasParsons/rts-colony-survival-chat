<?php
namespace App\Helpers\Processing;

use App\Helpers\Coordinates as Coordinate;
use App\Helpers\MongoDatabase\Tile;
/**
 * 
 */
class MountainProcessing
{
    const DEFAULT_MOUNTAIN_LINE = 156;

    protected $mountainCells;
    protected $mountainLine;
    protected $tiles;

    /**
     * Simple constructor
     */
    public function __construct()
    {
    }

    /**
     * Setup defaults and initialize arrays.
     */
    public function init()
    {
        $this->tiles = array();
        $this->mountainCells = array();
        //$this->mountainLine = self::DEFAULT_MOUNTAIN_LINE;

        return $this;
    }

    /**
     * 
     */
    public function createRidges()
    {
        $tileLocations = array();

        foreach ($this->mountainCells as $key => $row) {
            foreach ($row as $key => $cell) {
                $cell->addTileLocations($tileLocations);
            }
        }

        // These tiles should ever never be saved.
        // I'm only doing this so that I can run the algorithm on it.
        // The increasing of the mountain line won't work otherwise.
        // I don't actually want to turn all tiles to Passable land.
        foreach ($this->tiles as $x => $items) {
            foreach ($items as $y => $tile) {
                $this->tiles[$x][$y]->tileTypeId = 1;

                if (isset($tileLocations[$x][$y])) {
                    $this->tiles[$x][$y]->tileTypeId = 2;
                }
            }
        }

        foreach ($tileLocations as $intXaxisCoordinate => $items) {
            foreach ($items as $intYaxisCoordinate => $bit) {

                // Get the tile from the coordinates.
                $tile = $this->fetchTileForRockyChecks($intXaxisCoordinate, $intYaxisCoordinate);

                $topNeighbor    = $this->fetchTileForRockyChecks($intXaxisCoordinate, $intYaxisCoordinate + 1);
                $rightNeighbor  = $this->fetchTileForRockyChecks($intXaxisCoordinate + 1, $intYaxisCoordinate);
                $bottomNeighbor = $this->fetchTileForRockyChecks($intXaxisCoordinate, $intYaxisCoordinate - 1);
                $leftNeighbor   = $this->fetchTileForRockyChecks($intXaxisCoordinate - 1, $intYaxisCoordinate);

                // Diagonal neighbors.
                $topRightNeighbor    = $this->fetchTileForRockyChecks($intXaxisCoordinate + 1, $intYaxisCoordinate + 1);
                $topLeftNeighbor     = $this->fetchTileForRockyChecks($intXaxisCoordinate - 1, $intYaxisCoordinate + 1);
                $bottomRightNeighbor = $this->fetchTileForRockyChecks($intXaxisCoordinate + 1, $intYaxisCoordinate - 1);
                $bottomLeftNeighbor  = $this->fetchTileForRockyChecks($intXaxisCoordinate - 1, $intYaxisCoordinate - 1);

                if ($topNeighbor->notRocky() && $rightNeighbor->notRocky()) {
                    // This is Tile Display Type 'TopRightConvexedCorner-CliffSideTile'
                    $tile->setTileDisplayType('TopRightConvexedCorner-CliffSideTile');
                    $tile->tileTypeId = 4;

                } elseif ($bottomNeighbor->notRocky() && $leftNeighbor->notRocky()) {
                    // This is Tile Display Type  'BottomLeftConvexedCorner-CliffSideTile'
                    $tile->setTileDisplayType('BottomLeftConvexedCorner-CliffSideTile');
                    $tile->tileTypeId = 5;

                } elseif ($topNeighbor->notRocky() && $leftNeighbor->notRocky()) {
                    // This is Tile Display Type 'TopLeftConvexedCorner-CliffSideTile'
                    $tile->setTileDisplayType('TopLeftConvexedCorner-CliffSideTile');
                    $tile->tileTypeId = 6;

                } elseif ($bottomNeighbor->notRocky() && $rightNeighbor->notRocky()) {
                    // This is Tile Display Type 'BottomRightConvexedCorner-CliffSideTile'
                    $tile->setTileDisplayType('BottomRightConvexedCorner-CliffSideTile');
                    $tile->tileTypeId = 7;

                } elseif ($topRightNeighbor->notRocky() && $topNeighbor->isRocky() && $rightNeighbor->isRocky()) {
                    // This is Tile Display Type 'TopRightConcaveCorner-CliffSideTile'
                    $tile->setTileDisplayType('TopRightConcaveCorner-CliffSideTile');
                    $tile->tileTypeId = 8;

                } elseif ($topLeftNeighbor->notRocky() && $topNeighbor->isRocky() && $leftNeighbor->isRocky()) {
                    // This is Tile Display Type 'TopLeftConcaveCorner-CliffSideTile'
                    $tile->setTileDisplayType('TopLeftConcaveCorner-CliffSideTile');
                    $tile->tileTypeId = 9;

                } elseif ($bottomRightNeighbor->notRocky() && $bottomNeighbor->isRocky() && $rightNeighbor->isRocky()) {
                    // This is Tile Display Type 'bottomRightConcaveCorner-CliffSideTile'
                    $tile->setTileDisplayType('bottomRightConcaveCorner-CliffSideTile');
                    $tile->tileTypeId = 10;

                } elseif ($bottomLeftNeighbor->notRocky() && $bottomNeighbor->isRocky() && $leftNeighbor->isRocky()) {
                    // This is Tile Display Type 'bottomLeftConcaveCorner-CliffSideTile'
                    $tile->setTileDisplayType('bottomLeftConcaveCorner-CliffSideTile');
                    $tile->tileTypeId = 11;

                } elseif ($topNeighbor->notRocky()) {
                    // This is Tile Display Type 'topEdge-CliffSideTile'
                    $tile->setTileDisplayType('topEdge-CliffSideTile');
                    $tile->tileTypeId = 12;

                } elseif ($rightNeighbor->notRocky()) {
                    // This is Tile Display Type 'rightEdge-CliffSideTile'
                    $tile->setTileDisplayType('rightEdge-CliffSideTile');
                    $tile->tileTypeId = 13;

                } elseif ($bottomNeighbor->notRocky()) {
                    // This is Tile Display Type 'bottomEdge-CliffSideTile'
                    $tile->setTileDisplayType('bottomEdge-CliffSideTile');
                    $tile->tileTypeId = 14;

                } elseif ($leftNeighbor->notRocky()) {
                    // This is Tile Display Type 'leftEdge-CliffSideTile'
                    $tile->setTileDisplayType('leftEdge-CliffSideTile');
                    $tile->tileTypeId = 15;

                } else {
                    // This would be 'TileDisplayType' => 'inner-CliffSideTile'
                    // type id 2
                }

                $tile->save();
            }
        }
    }

    /**
     * If a tile isn't available return a Rocky Tile object.
     * The reason for this, is because on the edge of the map we don't know what is past that line.
     *
     */
    public function fetchTileForRockyChecks($intXaxisCoordinate, $intYaxisCoordinate)
    {
        // Get the tile to see if it isn't a the rock zone.
        if (isset($this->tiles[$intXaxisCoordinate][$intYaxisCoordinate])) {
            $tileNeighbor = $this->tiles[$intXaxisCoordinate][$intYaxisCoordinate];

        } else {
            // Consider this a Rocky tile if the tile isn't available.
            $tileNeighbor = new Tile($intXaxisCoordinate, $intYaxisCoordinate);
            $tileNeighbor->setStrType('Impassable Rocks');
            $tileNeighbor->name = 'Impassable Rocks';
            $tileNeighbor->tileTypeId = 2;
        }

        return $tileNeighbor;
    }

    /**
     * Gets the value of mountainLine.
     *
     * @return mixed
     */
    public function getMountainLine()
    {
        return $this->mountainLine;
    }

    /**
     * Sets the value of mountainLine.
     *
     * @param mixed $mountainLine the mountain line
     *
     * @return self
     */
    public function setMountainLine($mountainLine)
    {
        $this->mountainLine = $mountainLine;

        return $this;
    }

    /**
     * Gets the value of mountainCells.
     *
     * @return mixed
     */
    public function getMountainCells()
    {
        return $this->mountainCells;
    }

    /**
     * Sets the value of mountainCells.
     *
     * @param mixed $mountainCells the mountain cells
     *
     * @return self
     */
    public function setMountainCells($mountainCells)
    {
        $this->mountainCells = $mountainCells;

        return $this;
    }

    /**
     * Gets the value of tiles.
     *
     * @return mixed
     */
    public function getTiles()
    {
        return $this->tiles;
    }

    /**
     * Sets the value of tiles.
     *
     * @param mixed $tiles the tiles
     *
     * @return self
     */
    public function setTiles($tiles)
    {
        $this->tiles = $tiles;

        return $this;
    }
}
