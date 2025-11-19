<?php
namespace Generator\helpers\ModelHelpers;

use App\Helpers\MapDatabase\Map as MapRecord;
use App\Helpers\MapDatabase\Tile as TileRecord;
use App\Helpers\MapDatabase\Cell as CellRecord;
use App\Helpers\MapDatabase\TileType;

/**
 * This isn't going to be a complete Mpaloader
 */
class MapMapLoader
{
    // If you want to see the query generated then turn on debugging with this flag.
    const SHOW_QUERY = false;
    const NO_LIMIT   = true;

    public $size = 0;

    protected $arrTileSelect;
    protected $arrTileTypes;
    protected $arrTiles;

    /**
     * create Tile From Results
     *
     * @param string $tileType
     * @param string $arrData
     *
     * @return Tile
     */
    public function createTileFromResults($tileType, $arrData)
    {
/*
        $tile = new TileRecord();

        $arrTileSelect = $this->getArrTileSelect();

        // Populate the tile objects with what were selecting.
        foreach ($arrTileSelect as $key) {
            $tile->$key = $arrData[$tileType . '_tile_' . $key];
        }

        return $tile;
*/
    }

    /**
     * Return a tile Type record from a type type id.
     *
     * @param integer $tileTypeId
     *
     * @return tiletype
     */
    public function getTileTypeById($tileTypeId)
    {

        if (isset($this->arrTileTypes[$tileTypeId])) {
            return $this->arrTileTypes[$tileTypeId];
        }

    }

    /**
     * Keeps each tile in an array by MapCoordinates.
     *
     * @return tile
     */
    public function collectTile($tile)
    {
        $this->arrTiles[$tile->mapCoordinateX][$tile->mapCoordinateY] = $tile;
    }

    /**
     * Get all the mountain coordinates you can.
     *
     * @param integer $intMapPrimaryKey The map record's primary key.
     *
     * @return boolean
     */
    public static function getAllMountainCoordinates()
    {/*
        // Build the mountain cell query.
        $strQuery .= "\n".'select'."\n".'

               cell.`id`          as cell_id,
               cell.`name`        as cell_name,
               cell.`description` as cell_description,
               cell.`coordinateX` as cell_coordinateX,
               cell.`coordinateY` as cell_coordinateY,
               cell.`height`      as cell_height,
               cell.`map_id`      as cell_map_id,
               cell.`cellType_id` as cell_cellType_id
               '."\n".'

               from cell
               '."\n".'

               where cell.cellType_id = 2;
        ';

        $result = MapRecord::Query($strQuery);

        if ($result) {
            while ($arrData = mysql_fetch_assoc($result)) {

            }
        }

        return true;*/
    }

    /**
     * fetch Data
     *
     * @param integer $intMapPrimaryKey The map record's primary key.
     *
     * @return boolean
     */
    public function fetchData($intMapPrimaryKey)
    {
        /*
        $arrTileAliasPrefixHelp = array(
            'top_right',
            'top_left',
            'bottom_left',
            'bottom_right'
        );

        $arrTileSelect    = $this->getArrTileSelect();
        $strQuery         = "\n".'select'."\n";

        foreach ($arrTileAliasPrefixHelp as $prefix) {
            $strTempIndentVar = '               ';
            foreach ($arrTileSelect as $fieldName) {
                // Select the tile field.
                $strQuery .= $strTempIndentVar . $prefix . '_tile.' . $fieldName;

                // Alias the field with the prefix ex: top_right, top_left, bottom_left and bottom right.
                $strQuery .= ' as ';
                $strQuery .= $prefix . '_tile_' . $fieldName . ',' . "\n";
            }
            $strQuery .= "\n";
        }

        // Build the cell query.
        $strQuery .= '
               cell.`id`          as cell_id,
               cell.`name`        as cell_name,
               cell.`description` as cell_description,
               cell.`coordinateX` as cell_coordinateX,
               cell.`coordinateY` as cell_coordinateY,
               cell.`height`      as cell_height,
               cell.`map_id`      as cell_map_id,
               cell.`cellType_id` as cell_cellType_id

        ';

        $strQuery .= 'from cell';
        $strQuery .= '
        left join tile as top_right_tile on cell.id = top_right_tile.cell_id and
        top_right_tile.coordinateX = 1 and
        top_right_tile.coordinateY = 1 and
        top_right_tile.map_id = ' . $intMapPrimaryKey . '

        left join tile as top_left_tile on cell.id = top_left_tile.cell_id and
        top_left_tile.coordinateX = 0 and
        top_left_tile.coordinateY = 1 and
        top_left_tile.map_id = ' . $intMapPrimaryKey . '

        left join tile as bottom_left_tile on cell.id = bottom_left_tile.cell_id and
        bottom_left_tile.coordinateX = 0 and
        bottom_left_tile.coordinateY = 0 and
        bottom_left_tile.map_id = ' . $intMapPrimaryKey . '

        left join tile as bottom_right_tile on cell.id = bottom_right_tile.cell_id and
        bottom_right_tile.coordinateX = 1 and
        bottom_right_tile.coordinateY = 0 and
        bottom_right_tile.map_id = ' . $intMapPrimaryKey;

        if (!self::NO_LIMIT) {
            $strQuery = $strQuery . '
            limit 125';
        }

        if (self::SHOW_QUERY) {
            echo '<pre>' . $strQuery . '

        ';
        }

        $result = MapRecord::Query($strQuery);

        if ($result) {
            while ($arrData = mysql_fetch_assoc($result)) {

                $cell              = new CellRecord();
                $cell->id          = $arrData['cell_id'];
                $cell->name        = $arrData['cell_name'];
                $cell->description = $arrData['cell_description'];
                $cell->coordinateX = $arrData['cell_coordinateX'];
                $cell->name        = $arrData['cell_coordinateY'];
                $cell->height      = $arrData['cell_height'];
                $cell->map_id      = $arrData['cell_map_id'];
                $cell->cellType_id = $arrData['cell_cellType_id'];

                $topRight    = $this->createTileFromResults('top_right', $arrData);
                $topLeft     = $this->createTileFromResults('top_left', $arrData);
                $bottomLeft  = $this->createTileFromResults('bottom_left', $arrData);
                $bottomRight = $this->createTileFromResults('bottom_right', $arrData);

                $this->collectTile($topRight);
                $this->collectTile($topLeft);
                $this->collectTile($bottomLeft);
                $this->collectTile($bottomRight);
            }
            $this->size = count($this->arrTiles);
            $this->arrTileTypes = TileType::FindAll();

            // Just setting a default. There shouldn't ever be 0 id's,
            // will have to fix this later with table constraints.
            $this->arrTileTypes[0] = $this->arrTileTypes[1];

        }
        if (SHOW_QUERY) {
            echo '</pre>';
        }

        return true;*/
    }

    /**
     * The Map's x, and y coordinates are setup in the constructor.
     * Size is optional.
     *
     * Unfortunately there will be some null tiles.
     *
     * @param integer $intMapPrimaryKey
     */
    public function __construct($intMapPrimaryKey = 1)
    {
        // This is what the fields the query will select on the tiles.
        // This is also used to populate the tiles as well.
        $arrTileSelect = array(
            'id',
            'name',
            'description',
            'coordinateX',
            'coordinateY',
            'mapCoordinateX',
            'mapCoordinateY',
            'map_id',
            'cell_id',
            'tileType_id'
        );
        $this->setArrTileSelect($arrTileSelect);

        return $this->fetchData($intMapPrimaryKey);
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
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Gets the value of arrTileSelect.
     *
     * @return mixed
     */
    public function getArrTileSelect()
    {
        return $this->arrTileSelect;
    }

    /**
     * Sets the value of arrTileSelect.
     *
     * @param mixed $arrTileSelect the arr tile select
     *
     * @return self
     */
    public function setArrTileSelect($arrTileSelect)
    {
        $this->arrTileSelect = $arrTileSelect;

        return $this;
    }

    /**
     * Gets the value of arrTileTypes.
     *
     * @return mixed
     */
    public function getArrTileTypes()
    {
        return $this->arrTileTypes;
    }

    /**
     * Sets the value of arrTileTypes.
     *
     * @param mixed $arrTileTypes the arr tile types
     *
     * @return self
     */
    public function setArrTileTypes($arrTileTypes)
    {
        $this->arrTileTypes = $arrTileTypes;

        return $this;
    }
}
