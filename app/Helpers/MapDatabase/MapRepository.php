<?php
namespace App\Helpers\MapDatabase;

use App\Helpers\MapDatabase\Cell;
use App\Helpers\MapDatabase\Tile;
use App\Helpers\MapDatabase\MapModel as Map;
use App\Models\Map as EloquentModelMap;

use Illuminate\Support\Facades\DB;

/**
 * Keep helper functions here for fetching data from the Map database related to Cells.
 */
class MapRepository
{
    /**
     * Check for a tile records in the database with mapId equal to 1.
     * if it doesn't exist return false
     *
     * @param integer $mapId The map record's primary key
     *
     * @return Tiles
     */
    public static function findAllTiles($mapId)
    {
        $query = DB::table('tile')->where('map_id', '=', intval($mapId));
        $arrTile = $query->get();

        if (count($arrTile) > 0) {
            // This call $query->get() returns an array.
            foreach ($arrTile as $key => $arrValues) {
                $tile = new Tile();
                $tile->populateFromArray($arrValues);
                $objTiles[$tile->mapCoordinateX][$tile->mapCoordinateY] = $tile;
            }

            // Return the tile record that was returned from the database.
            return $objTiles;
        } else {
            // Return a new tile records.
            return false;
        }
    }

    /**
     * Check for a tile records in the database with mapId equal to 1.
     * X and Y indices are swapped to make it easier to check if a column exists.
     * if it doesn't exist return false
     *
     * @param integer $mapId The map record's primary key
     *
     * @return Tiles
     */
    public static function findAllTilesReversedAxis($mapId)
    {
        $query = DB::table('tile')->where('map_id', '=', intval($mapId));
        $arrTile = $query->get();

        if (count($arrTile) > 0) {
            // This call $query->get() returns an array.
            foreach ($arrTile as $key => $arrValues) {
                $tile = new Tile();
                $tile->populateFromArray($arrValues);
                $objTiles[$tile->mapCoordinateY][$tile->mapCoordinateX] = $tile;
            }

            // Return the tile record that was returned from the database.
            return $objTiles;
        } else {
            // Return a new tile records.
            return false;
        }
    }

    /**
     * Check for a cell records in the database with mapId equal to 1.
     * if it doesn't exist return false
     *
     * @param integer $mapId The map record's primary key
     *
     * @return Cells
     */
    public static function findAllCells($mapId)
    {
        $objCells = array();
        $arrCell = array();

        $query = DB::table('cell')->where('map_id', '=', intval($mapId));

        $arrCell = $query->get();

        if (count($arrCell) > 0) {
            // This call $query->get() returns an array.
            foreach ($arrCell as $key => $arrValues) {
                $Cell = new Cell();
                $Cell->populateFromArray($arrValues);
                $objCells[$Cell->coordinateX][$Cell->coordinateY] = $Cell;
            }

            // Return the cell record that was returned from the database.
            return $objCells;
        } else {
            // Return a new cell record.
            return false;
        }
    }

    /**
     * Return from the database all the cells that are tree cells.
     *
     * @return mixed Boolean false if no tree was found in the map.
     */
    public static function findAllTreeCells($mapId)
    {
        $objCells = array();
        $arrCell = array();

        $query = DB::table('cell')
            ->where('map_id', '=', intval($mapId))
            ->where('name', '=', 'Trees');

        $arrCell = $query->get();

        if (count($arrCell) > 0) {
            // This call $query->get() returns an array.
            foreach ($arrCell as $key => $arrValues) {
                $Cell = new Cell();
                $Cell->populateFromArray($arrValues);
                $objCells[$Cell->coordinateX][$Cell->coordinateY] = $Cell;
            }

            // Return the cell record that was returned from the database.
            return $objCells;

        } else {
            // Return false, no trees found
            return false;
        }
    }

    /**
     * Check for a tile records in the database with mapId equal to 1.
     * and where the Tile is water tile.
     *
     * @param integer $mapId The map record's primary key
     *
     * @return Tiles
     */
    public static function findAllWaterTiles($mapId)
    {
        $query = DB::table('tile')
            ->where('map_id', '=', intval($mapId))
            ->where('tileTypeId', '=', 3);

        $arrTile = $query->get();

        if (count($arrTile) > 0) {
            // This call $query->get() returns an array.
            foreach ($arrTile as $key => $arrValues) {
                $tile = new Tile();
                $tile->populateFromArray($arrValues);
                $objTiles[$tile->mapCoordinateX][$tile->mapCoordinateY] = $tile;
            }

            // Return the tile record that was returned from the database.
            return $objTiles;
        } else {
            // Return a new tile records.
            return false;
        }
    }

    /**
     * Check for a tile records in the database with mapId equal to 1.
     * and where the Tile is water tile.
     * Just return coordinates.
     *
     * @param integer $mapId The map record's primary key
     *
     * @return Tiles
     */
    public static function findAllWaterTileCoordinates($mapId)
    {
        $query = DB::table('tile')
            ->where('map_id', '=', intval($mapId))
            ->where('tileTypeId', '=', 3);
        $arrTile = $query->get();

        $coordinatesWaterTiles = array();

        if (count($arrTile) > 0) {
            // This call $query->get() returns an array.
            foreach ($arrTile as $key => $arrValues) {
                $coordinatesWaterTiles[$arrValues['mapCoordinateX']][$arrValues['mapCoordinateY']] = 1;
            }

            // Return the tile coordinates that was returned from the database.
            return $coordinatesWaterTiles;
        } else {
            // Return a new tile records.
            return false;
        }
    }

    /**
     * Check to see if there is a map record already there.
     *
     * @param integer $mapId The map record's primary key
     *
     * @return map
     */
    public static function findFirst($mapId)
    {
        $query = EloquentModelMap::find($mapId);

        $arrMap = $query->get()->toArray();
    
        if (count($arrMap) > 0) {

            foreach ($arrMap as $key => $arrValues) {
                $Map = new Map();
                $Map->populateFromArray($arrValues);
            }

            // Return the map record that was returned from the database.
            return $Map;

        } else {
            // Return a new cell record.
            return false;
        }
    }

    /**
     * Return from the database all the cells that are mountain cells.
     *
     * @return array
     */
    public static function findAllMountainCells($mapId, $mountainLine = null)
    {
        $objCells = array();
        $arrCell = array();

        $query = DB::table('cell')
            ->where('map_id', '=', intval($mapId))
            ->where('name', '=', 'Impassable Rocks');
        if ($mountainLine != null) {
            $query->where('height', '>', intval($mountainLine));
        }

        $arrCell = $query->get();

        if (count($arrCell) > 0) {
            // This call $query->get() returns an array.
            foreach ($arrCell as $key => $arrValues) {
                $Cell = new Cell();
                $Cell->populateFromArray($arrValues);
                $objCells[$Cell->coordinateX][$Cell->coordinateY] = $Cell;
            }

            // Return the cell record that was returned from the database.
            return $objCells;
        } else {
            // Return a new cell record.
            return false;
        }
    }

    /**
     * Fetch a cell by cell Id.
     *
     * @param integer $cellId The cell record's primary key
     *
     * @return cell
     */
    public static function findCell($cellId)
    {
        $query = DB::table('cell')
            ->where('_id', '=', $cellId);

        $arrCell = $query->get();

        if (count($arrCell) > 0) {
            // $query->get() returns an array.
            foreach ($arrCell as $key => $arrValues) {
                $cell = new Cell();
                $cell->populateFromArray($arrValues);
            }

            // Return the cell record that was returned from the database.
            return $cell;
        } else {
            // Return a new cell record.
            return false;
        }
    }
}
