<?php
namespace App\Helpers\Processing;

use Generator\helpers\ModelHelpers\Tile;

/**
 * Todos:
 * 1)
 * Bitwise operations to find and set setTilePropertiesOfMapAndSave.
 * Use an array of arrTileDisplayTypes using binary as a key.
 */
class TileProcessing
{
    protected $map;

    protected $arrPossibleCliffTiles;

    /**
     * Simple constructor
     *
     * @param map $map
     */
    public function __construct($map)
    {
        $this->map = $map;

        $this->arrPossibleCliffTiles = array();
    }

    /**
     * If a tile isn't available return a Water Tile object.
     *
     */
    public function fetchTileForWaterChecks($intXaxisCoordinate, $intYaxisCoordinate)
    {
        // Get the tile to see if it isn't a waters.
        $arrArrTiles = $this->map->getArrTiles();

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
     * If a tile isn't available return a Rocky Tile object.
     *
     */
    public function fetchTileForRockyChecks($intXaxisCoordinate, $intYaxisCoordinate)
    {
        // Get the tile to see if it isn't a water.
        $arrArrTiles = $this->map->getArrTiles();

        if (isset($arrArrTiles[$intXaxisCoordinate][$intYaxisCoordinate])) {
            $tileNeighbor = $arrArrTiles[$intXaxisCoordinate][$intYaxisCoordinate];

        } else {
            // Consider this a Rocky tile if the tile isn't available.
            $tileNeighbor = new Tile($intXaxisCoordinate, $intYaxisCoordinate);
            $tileNeighbor->setStrType('Impassable Rocks');
        }

        return $tileNeighbor;
    }

    /**
     * clear Possible Cliff Tiles
     *
     * @return this
     */
    public function clearArrPossibleCliffTiles()
    {
        $this->arrPossibleCliffTiles = array();

        return $this;
    }

    /**
     * mountain Tiles
     * Go through the mountain tiles and find out if where the cliff sides are going to be.
     *
     * @return void
     */
    public function mountainTiles()
    {
        foreach ($this->arrPossibleCliffTiles as $Coord) {

            $intXaxisCoordinate = $Coord->getXAxis();
            $intYaxisCoordinate = $Coord->getYAxis();

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
                $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                    'TileDisplayType' => 'TopRightConvexedCorner-CliffSideTile'
                ));
            } elseif ($bottomNeighbor->notRocky() && $leftNeighbor->notRocky()) {
                $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                    'TileDisplayType' => 'BottomLeftConvexedCorner-CliffSideTile'
                ));
            } elseif ($topNeighbor->notRocky() && $leftNeighbor->notRocky()) {
                $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                    'TileDisplayType' => 'TopLeftConvexedCorner-CliffSideTile'
                ));
            } elseif ($bottomNeighbor->notRocky() && $rightNeighbor->notRocky()) {
                $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                    'TileDisplayType' => 'BottomRightConvexedCorner-CliffSideTile'
                ));
            } elseif ($topRightNeighbor->notRocky() && $topNeighbor->isRocky() && $rightNeighbor->isRocky()) {
                $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                    'TileDisplayType' => 'TopRightConcaveCorner-CliffSideTile'
                ));
            } elseif ($topLeftNeighbor->notRocky() && $topNeighbor->isRocky() && $leftNeighbor->isRocky()) {
                $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                    'TileDisplayType' => 'TopLeftConcaveCorner-CliffSideTile'
                ));
            } elseif ($bottomRightNeighbor->notRocky() && $bottomNeighbor->isRocky() && $rightNeighbor->isRocky()) {
                $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                    'TileDisplayType' => 'bottomRightConcaveCorner-CliffSideTile'
                ));
            } elseif ($bottomLeftNeighbor->notRocky() && $bottomNeighbor->isRocky() && $leftNeighbor->isRocky()) {
                $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                    'TileDisplayType' => 'bottomLeftConcaveCorner-CliffSideTile'
                ));
            } elseif ($topNeighbor->notRocky()) {
                $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                    'TileDisplayType' => 'topEdge-CliffSideTile'
                ));
            } elseif ($rightNeighbor->notRocky()) {
                $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                    'TileDisplayType' => 'rightEdge-CliffSideTile'
                ));
            } elseif ($bottomNeighbor->notRocky()) {
                $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                    'TileDisplayType' => 'bottomEdge-CliffSideTile'
                ));
            } elseif ($leftNeighbor->notRocky()) {
                $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                    'TileDisplayType' => 'leftEdge-CliffSideTile'
                ));
            } else {
                //$this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                //    'TileDisplayType' => 'inner-CliffSideTile'
                //));
            }

            //echo 'this has happened at X:'.$intXaxisCoordinate.' Y:'.$intYaxisCoordinate.'<br>';
            //echo 'cell X:'.$cellX.' Y:'.$cellY.'<br>';
            //echo '<pre>'.print_r($this->map->getCell($cellX, $cellY), true).'</pre>';

            //echo 'globeCoords ';
            /*
            echo 'X:'.$intXaxisCoordinate.' Y:'.$intYaxisCoordinate.'<br>';

            echo '<pre>'.print_r($tile, true).'</pre>';
            echo '<pre>top Neighbor '.print_r($topNeighbor, true).'</pre>';
            echo '<pre>right neighbor '.print_r($rightNeighbor, true).'</pre>';
            echo '<pre>bottom neightbor '.print_r($bottomNeighbor, true).'</pre>';
            echo '<pre>left neighbor '.print_r($leftNeighbor, true).'</pre>';*/

        }

        return $this;
    }

    /**
     * [landTiles description]
     *
     * @return [type]
     */
    public function landTiles()
    {
        $arrArrTiles = $this->map->getArrTiles();

        $count = 0;

        foreach ($arrArrTiles as $intXaxisCoordinate => $row) {
            foreach ($row as $intYaxisCoordinate => $tile) {
                if ($tile->getStrType() == 'Passable Land') {
                    $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                        'TileDisplayType' => 'inner-Land'
                    ));
                } elseif ($tile->getStrType() == 'Trees') {
                    $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                        'TileDisplayType' => 'inner-Tree'
                    ));
                }
            }
        }
    }

    /**
     * Figures out where the water tiles meets land
     * then determines which tile should be used.
     *
     * @return void
     */
    public function waterTiles()
    {
        $arrArrTiles = $this->map->getArrTiles();

        foreach ($arrArrTiles as $intXaxisCoordinate => $row) {
            foreach ($row as $intYaxisCoordinate => $tile) {
                if ($tile->getStrType() == 'Water') {

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

                    // Sometimes $tile is an array, need to find out why.
                    if (is_object($tile)) {

                        // The complexity has gone way up here,
                        // may need to turn if statements into a loop with a behaviour pattern.
                        // Check for convex corners.
                        if ($topNeighbor->notWater() && $rightNeighbor->notWater()) {

                            // Top right tile of this cell is a top right corner.
                            // I was having difficulties making sure that the tiles in each cell were being updated.
                            // So I wrote a function that "setTilePropertiesOfMapAndSave" calculates what the cell coordinates
                            // should be and then sets the tile's properties through the cell.
                            $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                                'TileDisplayType' => 'TopRightConvexedCorner-WaterTile'
                            ));
                        } elseif ($bottomNeighbor->notWater() && $leftNeighbor->notWater()) {
                            $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                                'TileDisplayType' => 'BottomLeftConvexedCorner-WaterTile'
                            ));
                        } elseif ($topNeighbor->notWater() && $leftNeighbor->notWater()) {
                            $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                                'TileDisplayType' => 'TopLeftConvexedCorner-WaterTile'
                            ));
                        } elseif ($bottomNeighbor->notWater() && $rightNeighbor->notWater()) {
                            $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                                'TileDisplayType' => 'BottomRightConvexedCorner-WaterTile'
                            ));
                        } elseif ($topRightNeighbor->notWater() && $topNeighbor->isWater() && $rightNeighbor->isWater()) {
                            $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                                'TileDisplayType' => 'TopRightConcaveCorner-WaterTile'
                            ));
                        } elseif ($topLeftNeighbor->notWater() && $topNeighbor->isWater() && $leftNeighbor->isWater()) {
                            $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                                'TileDisplayType' => 'TopLeftConcaveCorner-WaterTile'
                            ));
                        } elseif ($bottomRightNeighbor->notWater() && $bottomNeighbor->isWater() && $rightNeighbor->isWater()) {
                            $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                                'TileDisplayType' => 'bottomRightConcaveCorner-WaterTile'
                            ));
                        } elseif ($bottomLeftNeighbor->notWater() && $bottomNeighbor->isWater() && $leftNeighbor->isWater()) {
                            $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                                'TileDisplayType' => 'bottomLeftConcaveCorner-WaterTile'
                            ));
                        } elseif ($topNeighbor->notWater()) {
                            $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                                'TileDisplayType' => 'topEdge-WaterTile'
                            ));
                        } elseif ($rightNeighbor->notWater()) {
                            $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                                'TileDisplayType' => 'rightEdge-WaterTile'
                            ));
                        } elseif ($bottomNeighbor->notWater()) {
                            $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                                'TileDisplayType' => 'bottomEdge-WaterTile'
                            ));
                        } elseif ($leftNeighbor->notWater()) {
                            $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                                'TileDisplayType' => 'leftEdge-WaterTile'
                            ));
                        } else {
                            $this->map->setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, array(
                                'TileDisplayType' => 'inner-WaterTile'
                            ));
                        }
                    }
                }
            }
        }

        return $this;
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
     * Gets the value of arrPossibleCliffTiles.
     *
     * @return mixed
     */
    public function getArrPossibleCliffTiles()
    {
        return $this->arrPossibleCliffTiles;
    }

    /**
     * Sets the value of arrPossibleCliffTiles.
     *
     * @param mixed $arrPossibleCliffTiles The array of possible cliff tiles
     *
     * @return self
     */
    public function setArrPossibleCliffTiles($arrPossibleCliffTiles)
    {
        $this->arrPossibleCliffTiles = $arrPossibleCliffTiles;

        return $this;
    }

    /**
     * Adds a value of arrPossibleCliffTiles.
     *
     * @param mixed $mxdPossibleCliffTileValue
     *
     * @return self
     */
    public function addArrPossibleCliffTiles($mxdPossibleCliffTileValue)
    {
        $this->arrPossibleCliffTiles[] = $mxdPossibleCliffTileValue;

        return $this;
    }
}
