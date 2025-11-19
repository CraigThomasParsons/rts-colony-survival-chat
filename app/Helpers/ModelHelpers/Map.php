<?php
namespace App\Helpers\ModelHelpers;

use App\Helpers\Base\BaseMapGenerator;
use App\Helpers\MapDatabase\Cell as CellRecord;
use App\Helpers\MapDatabase\CellRepository;
use App\Helpers\MapDatabase\Tile as TileRecord;
use App\Helpers\MapDatabase\TileRepository;
use App\Helpers\MapDatabase\TileType;
use App\Helpers\ModelHelpers\Cell;

/**
 * Represents 1 single square of a Map with all the cells and tiles.
 * The member variable databaseRecord in the class represents the one database record.
 *
 * Create a Map class in the model folder
 * to be used exclusively for loading and saving the Map record.
 *
 * Note to self: The trick to getting the array-access to work the way I want it to.
 * Is to have a MapColumn object.
 *
 * All the findByCoordinates or FindBymapCoordinate functions should be refactored to not return
 * a new object if fails.
 */
class Map implements \arrayaccess
{
    /**
     * Represents the Map record of table map App\Helpers\MongoDatabase\Map
     *
     * @var $databaseRecord
     */
    public $databaseRecord;

    protected $intXaxisCoordinate;
    protected $intYaxisCoordinate;
    protected $arrCells;
    protected $arrTiles;
    protected $size;
    protected $mapId;

    /**
     * The Map's x, and y coordinates are setup in the constructor.
     * Size is optional.
     *
     * @param integer $intXaxisCoordinate The coordinate on the x axis.
     * @param integer $intYaxisCoordinate The coordinate on the y axis.
     * @param integer $size               The size of one dimension of the cell array.
     */
    public function __construct($intXaxisCoordinate = 0, $intYaxisCoordinate = 0, $size = null, $databaseRecord = null)
    {
        // Size is optional.
        if (is_null($size)) {
            // Defaults are set in the BaseMapGenerator class.
            $this->size = BaseMapGenerator::DEFAULT_GRID_SIZE;
        } else {
            $this->size = $size;
        }

        $this->arrCells = array();
        $this->arrTiles = array();

        $this->setIntXaxisCoordinate($intXaxisCoordinate);
        $this->setIntYaxisCoordinate($intYaxisCoordinate);

        $this->databaseRecord = $databaseRecord;
    }

    /**
     * Sets up the tile array and the cell array.
     *
     * @return void
     */
    public function initialize()
    {
        // Enabling Garbage collection.
        gc_enable();

        // Create cells and tiles.
        for ($intYaxisCoordinate = 0; $intYaxisCoordinate < $this->size; $intYaxisCoordinate += 1) {
            for ($intXaxisCoordinate = 0; $intXaxisCoordinate < $this->size; $intXaxisCoordinate += 1) {
                $cell = new Cell($intXaxisCoordinate, $intYaxisCoordinate);
                $arrTmpTiles = array();

                // Four tiles per cell.
                $tileBottomLeft = new Tile(0, 0);
                $tileTopLeft = new Tile(0, 1);
                $tileBottomRight = new Tile(1, 0);
                $tileTopRight = new Tile(1, 1);

                $arrTmpTiles[0][0] = $tileBottomLeft->setStrDescription('Bottom Left');
                $arrTmpTiles[0][1] = $tileTopLeft->setStrDescription('Top Left');
                $arrTmpTiles[1][0] = $tileBottomRight->setStrDescription('Bottom Right');
                $arrTmpTiles[1][1] = $tileTopRight->setStrDescription('Top Right');

                $this->arrCells[$intXaxisCoordinate][$intYaxisCoordinate] = $cell->setArrTmpTiles($arrTmpTiles);

                // Loop through each of these tiles.
                foreach ($arrTmpTiles as $row) {
                    foreach ($row as $tile) {
                        // Adding the tiles to arrTiles.
                        $tileX = (2 * $intXaxisCoordinate) + $tile->getIntXaxisCoordinate();
                        $tileY = (2 * $intYaxisCoordinate) + $tile->getIntYaxisCoordinate();
                        $this->setTile($tileX, $tileY, $tile);
                    }
                }
            }
        }
        // Need to write createInitialCellsInDatabase()`
        $this->createInitialTilesInDatabase();

        // Trying to force some garbage collection.
        gc_collect_cycles();
    }

    /**
     * Display a simple Map preview.
     *
     * @return string
     */
    public function printHTML()
    {
        echo '<table border="0" cellpadding="0" cellspacing="0" class="Preview">';

        for ($intYaxisCoordinate = $this->size; $intYaxisCoordinate > -1; $intYaxisCoordinate--) {
            echo '<tr>';
            for ($intXaxisCoordinate = 0; $intXaxisCoordinate < $this->size; $intXaxisCoordinate += 1) {
                if (isset($this->arrCells[$intXaxisCoordinate][$intYaxisCoordinate])) {
                    $cell = $this->arrCells[$intXaxisCoordinate][$intYaxisCoordinate];
                    echo $cell->getString();
                }
            }
            echo '</tr>';
        }

        echo '</table>';
    }

    /**
     * Sets a tile's properties.
     *
     * @param integer $intXaxisCoordinate  The x coordinate of the cell.
     * @param integer $intYaxisCoordinate  The y coordinate of the cell.
     * @param integer $intXaxisOffsetCoord The x offset to get the tile. According to the cell not the map.
     * @param integer $intYaxisOffsetCoord The y offset to get the tile. According to the cell not the map.
     * @param array   $arrNewValues
     *
     * @return void
     */
    public function setTilePropertiesOfCell(
        $intXaxisCoordinate,
        $intYaxisCoordinate,
        $intXaxisOffsetCoord,
        $intYaxisOffsetCoord,
        $arrNewValues
    ) {
        foreach ($arrNewValues as $strProperty => $mxdValue) {
            $strSetter = 'set' . $strProperty;
            $this
                ->arrCells[$intXaxisCoordinate][$intYaxisCoordinate]
                ->arrTmpTiles[$intXaxisOffsetCoord][$intYaxisOffsetCoord]
                ->$strSetter($mxdValue);
        }
    }

    /**
     * Sets a tile's properties and saves a tile in the database.
     *
     * @param tile    $tile               The tile object we will be changing indirectly through
     *                                    the map's arrTempTiles array.
     * @param integer $intXaxisCoordinate The x coordinate of the tile in the tile array of the Map.
     * @param integer $intYaxisCoordinate The y coordinate of the tile in the tile array of the Map.
     * @param array   $arrNewValues
     *
     * @return void
     */
    public function setTilePropertiesOfMapAndSave($tile, $intXaxisCoordinate, $intYaxisCoordinate, $arrNewValues)
    {
        // if (isset($arrNewValues['TileDisplayType'])) {
        //    $strTileDisplayType = $arrNewValues['TileDisplayType'];
        //    $tileType           = TileType::findByName($strTileDisplayType);
        // }

        // If the tile record exists already, it'll return that one. Otherwise it'll return a new TileRecord.
        $tileRecord = TileRecord::findByMapCoordinates(
            $this->getMapPrimaryKey(),
            $intXaxisCoordinate,
            $intYaxisCoordinate
        );

        $tileRecord->map_id = $this->getMapPrimaryKey();

        $intCellXaxisCoordinate = $tile->getCellIntXaxisCoordinate($intXaxisCoordinate);
        $intCellYaxisCoordinate = $tile->getCellIntYaxisCoordinate($intYaxisCoordinate);

        $cellRecord = CellRecord::findByCoordinates(
            $tileRecord->map_id,
            $intCellXaxisCoordinate,
            $intCellYaxisCoordinate
        );

        $intXaxisOffsetCoord = $tile->getIntXaxisCoordinate();
        $intYaxisOffsetCoord = $tile->getIntYaxisCoordinate();

        foreach ($arrNewValues as $strProperty => $mxdValue) {
            $strSetter = 'set' . $strProperty;
            $this
                ->arrCells[$intCellXaxisCoordinate][$intCellYaxisCoordinate]
                ->arrTmpTiles[$intXaxisOffsetCoord][$intYaxisOffsetCoord]
                ->$strSetter($mxdValue);
        }

        // Setting foreign keys.
        if ($cellRecord) {
            $tileRecord->cell_id = $cellRecord->id;
        }

        if ($tileType) {
            $tileRecord->tileTypeId = $tileType->id;
        } else {
            // Typetype 1 is the default.
            $tileRecord->tileTypeId = 1;
        }

        $tileRecord->name = $tile->getStrType();
        $tileRecord->description = $tile->getStrDescription();
        $tileRecord->coordinateX = $tile->getIntXaxisCoordinate();
        $tileRecord->coordinateY = $tile->getIntYaxisCoordinate();

        $tileRecord->mapCoordinateX = $intXaxisCoordinate;
        $tileRecord->mapCoordinateY = $intYaxisCoordinate;

        $tileRecord->save();
    }

    /**
     * Create Initial Tiles In Database
     * The purpose of this function is put place holders in the database when the query begins.
     *
     * Calculate map coordinates Xmap = (2 * Cellx) + X offset or Ymap = (2 * Celly) + Y offset.
     * Map coordinates are the tiles coordinates according to the map and not the parent cell object.
     * The function getCellIntXaxisCoordinate, of the cell model helper, does the oposite of this equation.
     *
     * @return void
     */
    public function createInitialTilesInDatabase()
    {
        foreach ($this->arrTiles as $intXaxisCoordinate => $arrColumn) {
            foreach ($arrColumn as $intYaxisCoordinate => $tile) {
                $intXaxisOffsetCoord = $tile->getIntXaxisCoordinate();
                $intYaxisOffsetCoord = $tile->getIntYaxisCoordinate();

                // Finding out if there is a tile already there.
                $tileRecord = TileRepository::findByMapCoordinates(
                    $this->getMapPrimaryKey(),
                    $intXaxisCoordinate,
                    $intYaxisCoordinate
                );

                
                // Don't bother with any tiles that are already initialized.
                if ($tileRecord->hasId() === false) {
                    $tileRecord->mapId = intval($this->getMapPrimaryKey());

                    $tileRecord->coordinateX = $intXaxisOffsetCoord;
                    $tileRecord->coordinateY = $intYaxisOffsetCoord;

                    // Based on the coordinates of the x and y cells of the tiles.
                    // The cells coordinates should be returned.
                    $intCellXaxisCoordinate = $tile->getCellIntXaxisCoordinate($intXaxisCoordinate);
                    $intCellYaxisCoordinate = $tile->getCellIntYaxisCoordinate($intYaxisCoordinate);

                    $tileRecord->mapCoordinateX = $intXaxisCoordinate;
                    $tileRecord->mapCoordinateY = $intYaxisCoordinate;

                    $cellRecord = CellRepository::findByCoordinates(
                        $tileRecord->mapId,
                        $intCellXaxisCoordinate,
                        $intCellYaxisCoordinate
                    );

                    if ($cellRecord->hasId()) {
                        // This is all were going to do if the cell record already existed.
                        $cellId = $cellRecord->getId();
                    }

                    // Save to make sure we get a cell_id.
                    $cellId = $cellRecord->save();

                    if ($cellRecord) {
                        $tileRecord->setCellId($cellId);
                    }

                    $tileRecord->description = 'Initial tile';
                    $tileRecord->tileTypeId = 1;
                    $tileRecord->name = 'Initial tile';

                    $tileRecord->save();
                }
            }
        }
    }

    /**
     * Sets a tile's properties.
     *
     * @param tile    $tile               The tile object we will be changing indirectly through the
     *                                    map's arrTempTiles array.
     * @param integer $intXaxisCoordinate The x coordinate of the tile in the tile array of the Map.
     * @param integer $intYaxisCoordinate The y coordinate of the tile in the tile array of the Map.
     * @param array   $arrNewValues
     *
     * @return tile
     */
    public function setTilePropertiesOfMap(Tile $tile, $intXaxisCoordinate, $intYaxisCoordinate, $arrNewValues)
    {
        $intCellXaxisCoordinate = $tile->getCellIntXaxisCoordinate($intXaxisCoordinate);
        $intCellYaxisCoordinate = $tile->getCellIntYaxisCoordinate($intYaxisCoordinate);
        $intXaxisOffsetCoord = $tile->getIntXaxisCoordinate();
        $intYaxisOffsetCoord = $tile->getIntYaxisCoordinate();

        foreach ($arrNewValues as $strProperty => $mxdValue) {
            $strSetter = 'set' . $strProperty;
            $this
                ->arrCells[$intCellXaxisCoordinate][$intCellYaxisCoordinate]
                ->arrTmpTiles[$intXaxisOffsetCoord][$intYaxisOffsetCoord]
                ->$strSetter($mxdValue);
        }

        return $this
            ->arrCells[$intCellXaxisCoordinate][$intCellYaxisCoordinate]
            ->arrTmpTiles[$intXaxisOffsetCoord][$intYaxisOffsetCoord];
    }

    /**
     * Sets the tile in the array by reference
     *
     * @param integer $intXaxisCoordinate The x coordinate of this tile.
     * @param integer $intYaxisCoordinate The y coordinate of this tile.
     * @param Tile    &$tile              The tile were adding to arrTiles.
     */
    public function setTile($intXaxisCoordinate, $intYaxisCoordinate, &$tile)
    {
        $this->arrTiles[$intXaxisCoordinate][$intYaxisCoordinate] = $tile;
    }

    /**
     * Gets the value of arrCells.
     *
     * @param integer $intXaxisCoordinate The x coordinate of the cell we are fetching.
     * @param integer $intYaxisCoordinate The y coordinate of the cell we are fetching.
     *
     * @return mixed
     */
    public function getCell($intXaxisCoordinate, $intYaxisCoordinate)
    {
        return $this->arrCells[$intXaxisCoordinate][$intYaxisCoordinate];
    }

    /**
     * Gets the value of intXaxisCoordinate.
     *
     * @return mixed
     */
    public function getIntXaxisCoordinate()
    {
        return $this->intXaxisCoordinate;
    }

    /**
     * Sets the value of intXaxisCoordinate.
     *
     * @param mixed $intXaxisCoordinate the int xaxis coordinate
     *
     * @return self
     */
    public function setIntXaxisCoordinate($intXaxisCoordinate)
    {
        $this->intXaxisCoordinate = $intXaxisCoordinate;

        return $this;
    }

    /**
     * Gets the value of intYaxisCoordinate.
     *
     * @return mixed
     */
    public function getIntYaxisCoordinate()
    {
        return $this->intYaxisCoordinate;
    }

    /**
     * Sets the value of intYaxisCoordinate.
     *
     * @param mixed $intYaxisCoordinate the int yaxis coordinate
     *
     * @return self
     */
    public function setIntYaxisCoordinate($intYaxisCoordinate)
    {
        $this->intYaxisCoordinate = $intYaxisCoordinate;

        return $this;
    }

    /**
     * Necessary for arrayaccess.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetExists($offset)
    {
        return isset($this->arrCells[$offset]);
    }

    /**
     * Necessary for arrayaccess.
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->arrCells[$offset]);
    }

    /**
     * Necessary for arrayaccess.
     *
     * @param [type] $offset [description]
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->arrCells[$offset]) ? $this->arrCells[$offset] : null;
    }

    /**
     * Necessary for arrayaccess.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->arrCells[] = $value;
        } else {
            $this->arrCells[$offset] = $value;
        }
    }

    /**
     * Returns a string representations of this class.
     *
     * @return string
     */
    public function __toString()
    {
        return print_r($this, true);
    }

    /**
     * Gets the value of size.
     *
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Sets the value of size.
     *
     * @param mixed $size the size
     *
     * @return self
     */
    public function setSize($size = BaseMapGenerator::DEFAULT_GRID_SIZE)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Gets the value of arrCells.
     *
     * @return mixed
     */
    public function getArrCells()
    {
        return $this->arrCells;
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
     * Sets the value of arrCells.
     *
     * @param mixed $arrCells the arr cells
     *
     * @return self
     */
    public function setArrCells($arrCells)
    {
        $this->arrCells = $arrCells;

        return $this;
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
     * Gets the value of mapPrimaryKey.
     * This has some shaddy code and I want to
     * find if it gets to this point while
     * $this->mapId isn't set in the future.
     *
     * @return mixed
     */
    public function getMapPrimaryKey()
    {
        if ($this->mapId) {
            return $this->getMapId();
        }

        return $this->getDatabaseRecord()->id;
    }

    /**
     * Gets the value of databaseRecord.
     *
     * @return mixed
     */
    public function getDatabaseRecord()
    {
        return $this->databaseRecord;
    }

    /**
     * Sets the value of databaseRecord.
     *
     * @param mixed $databaseRecord the database record
     *
     * @return self
     */
    public function setDatabaseRecord($databaseRecord)
    {
        $this->databaseRecord = $databaseRecord;

        return $this;
    }

    /**
     * Gets the value of mapId.
     *
     * @return mixed
     */
    public function getMapId()
    {
        return $this->mapId;
    }

    /**
     * Sets the value of mapId.
     *
     * @param mixed $mapId the map id
     *
     * @return self
     */
    public function setMapId($mapId)
    {
        $this->mapId = $mapId;

        return $this;
    }
}
