<?php
namespace App\Helpers\MapDatabase;

use Illuminate\Support\Facades\DB;
use App\Helpers\MapDatabase\Tile;

/**
 * Store helper functions here for fetching data from the Map database related to tiles.
 */
class TileRepository
{
    /**
     * This function was written for mongodb, I'm not using that anymore.
     * Check for a tile record in the database,
     * if it doesn't exist return a new one.
     * To test this function 
     *   use App\Models\TempTile;
     *    
     * $tile = new TempTile();
     * $tile->mapCoordinateX = 0;
     * $tile->mapCoordinateY = 0;
     * $tile->mapId = 1;
     * $tile->save();
     *
     * Will need to write some validation into this function if more than one item is returned.
     *
    * @param string $mapId       The map record's primary key
     * @param integer $coordinateX The x-axis co-ordinate
     * @param integer $coordinateY The y-axis co-ordinate
     *
     * @return Tile
     */
    public static function findByMongoMapCoordinates($mapId, $mapCoordinateX, $mapCoordinateY)
    {
    $query = DB::connection('mongo')->collection('Tile')
    ->where('mapCoordinateX', '=', intval($mapCoordinateX))
    ->where('mapCoordinateY', '=', intval($mapCoordinateY))
    ->where('mapId', '=', $mapId);

        $arrTiles = $query->get();

        if (count($arrTiles) > 0) {

            // Array of tiles are returned even though only one should be.
            $arrTile = array_pop($arrTiles);

            $tile = new Tile();
            $tile->populateFromArray($arrTile);

            // Return the tile record that was returned from the database.
            return $tile;

        } else {

            // Return a new tile record.
            return new Tile();
        }
    }

    /**
     * This function was written for mongodb, I'm not using that anymore.
     * Check for a tile record in the database,
     * if it doesn't exist return a new one.
     *
     * Will need to write some validation into this function if more than one item is returned.
     *
    * @param string $mapId       The map record's primary key
     * @param integer $coordinateX The x-axis co-ordinate
     * @param integer $coordinateY The y-axis co-ordinate
     *
     * @return Tile
     */
    public static function findByMapCoordinates($mapId, $mapCoordinateX, $mapCoordinateY)
    {
        $query = DB::table('tile')
            ->where('mapCoordinateX', '=', intval($mapCoordinateX))
            ->where('mapCoordinateX', '=', intval($mapCoordinateY))
            ->where('map_id', '=', $mapId);
        
        $tiles = $query->get();

        if ($tiles->count() > 0) {
            foreach ($tiles as $key => $tileData) {
                $tile = new Tile();
                return $tile->populateFromStdClass($tileData);
            }

            // Return the tile record that was returned from the database.
            //return new Tile();
        } else {
            // Return a new cell record.
            return (new Tile())
                ->setMapId($mapId)
                ->setMapCoordinateX(intval($mapCoordinateX))
                ->setMapCoordinateY(intval($mapCoordinateY));
        }
    }
}