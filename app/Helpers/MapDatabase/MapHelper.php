<?php
namespace App\Helpers\MapDatabase;

use App\Helpers\MapDatabase\Map as MapRecord;
use App\Helpers\MapDatabase\Tile as TileRecord;
use App\Helpers\MapDatabase\Cell as CellRecord;
use App\Helpers\MapDatabase\TileType;
use App\Helpers\Coordinates;

/**
 * This isn't going to be a complete Maploader
 * Just enough functions to be used by the cell processing classes.
 */
class MapHelper
{
    protected $arrTiles;
    protected $intMapPrimaryKey;

    /**
     * Simply just stores the tiles and cells
     * May eventually do more.
     *
     * @param integer $intMapPrimaryKey
     * @param array   $tiles
     * @param array   $cells
     */
    public function __construct($intMapPrimaryKey = 1, $tiles = null, $cells = null)
    {
        $this->intMapPrimaryKey = 1;

        $this->arrTiles = $tiles;
        $this->arrCells = $cells;
    }

    /**
     * Gets the value of arrTiles.
     *
     * @return mixed
     */
    public function getArrTiles()
    {
        return $this->arrTiles;
    }

    /**
     * Sets the value of arrTiles.
     *
     * @param mixed $arrTiles the arr tiles
     *
     * @return self
     */
    public function setArrTiles($arrTiles)
    {
        $this->arrTiles = $arrTiles;

        return $this;
    }

    /**
     * Check if the tile Isset in the member array variable arrTiles
     *
     * @param integer $intMapXAxis
     * @param integer $intMapYAxis
     *
     * @return boolean
     */
    public function tileIsset($intMapXAxis, $intMapYAxis)
    {
        return isset($this->arrTiles[$intMapXAxis][$intMapYAxis]);
    }

    /**
     * Return a tile from member array variable arrTiles
     *
     * @param integer $intMapXAxis
     * @param integer $intMapYAxis
     *
     * @return boolean
     */
    public function getTile($intMapXAxis, $intMapYAxis)
    {
        return $this->arrTiles[$intMapXAxis][$intMapYAxis];
    }

    /**
     * Goes through each cell and kills one tree tile.
     * This is usefull for the John Conway's game of life.
     *
     * @param integer $mapId Primary Key of the Map table
     *
     * @return void
     */
    public function holePuncher($mapId)
    {
        $arrTileSelectors = array(
            0,
            1,
            2,
            3
        );

        $arrCoordinates = array(
            new Coordinates(0, 0),
            new Coordinates(0, 1),
            new Coordinates(1, 1),
            new Coordinates(1, 0)
        );

        // Foreach cell SET tile `name` = 'Passable Land', and `tileTypeId` = '1'.
        foreach ($this->arrCells as $doesntMatter => $something) {
            foreach ($something as $doesntMatterEither => $cell) {

                $randomInteger = array_rand($arrTileSelectors, 1);

                $mapCoordinateX = (($cell->getX() * 2) + $arrCoordinates[$randomInteger]->getXAxis());
                $mapCoordinateY = (($cell->getY() * 2) + $arrCoordinates[$randomInteger]->getYAxis());

                $tile = TileRepository::findByMapCoordinates($mapId, $mapCoordinateX, $mapCoordinateY);
                $tile->name = 'Passable Land';
                $tile->tileTypeId = '1';
                $tile->setCellId($cell->getId());
                $tile->save();
            }
        }
    }

    /**
     * Goes through each cell cell and kills all tree tiles.
     *
     * @param integer $mapId Primary Key of the Map table
     *
     * @return void
     */
    public function killAllTreesInCell($mapId)
    {
        $arrCoordinates = array(
            new Coordinates(0, 0),
            new Coordinates(0, 1),
            new Coordinates(1, 1),
            new Coordinates(1, 0)
        );

        // foreach cell SET tile `name` = 'Passable Land', and `tileTypeId` = '1'.
        foreach ($this->arrCells as $doesntMatter => $something) {
            foreach ($something as $doesntMatterEither => $cell) {
                foreach ($arrCoordinates as $index => $selected) {

                    $mapCoordinateX = (($cell->getX() * 2) + $selected->getXAxis());
                    $mapCoordinateY = (($cell->getY() * 2) + $selected->getYAxis());

                    $tile = TileRepository::findByMapCoordinates($mapId, $mapCoordinateX, $mapCoordinateY);
                    $tile->name = 'Passable Land';
                    $tile->tileTypeId = '1';
                    $tile->setCellId($cell->getId());
                    $tile->save();
                }
            }
        }
    }
}
