<?php
namespace App\Helpers\MapDatabase;

use App\Helpers\MapDatabase\Cell;
use App\Models\Cell as EloquentCell;
use Illuminate\Support\Facades\DB;

/**
 * Store helper functions here for fetching data from the Map database related to Cells.
 */
class CellRepository
{
    /**
     * Check for a cell record in the database,
     * if it doesn't exist return a new one.
     *
    * @param string $mapId       The map record's primary key
     * @param integer $coordinateX The x-axis co-ordinate
     * @param integer $coordinateY The y-axis co-ordinate
     *
     * @return Cell
     */
    public static function findByCoordinates($mapId = 1, $coordinateX = null, $coordinateY = null)
    {
        $query = DB::table('cell')
            ->where('coordinateX', '=', intval($coordinateX))
            ->where('coordinateY', '=', intval($coordinateY))
            ->where('map_id', '=', $mapId);
        
        $cells = $query->get();

        if ($cells->count() == 1) {
            // $query->get() returns a collection.
            foreach ($cells as $key => $arrValues) {
                $Cell = new Cell();
                $Cell->populateFromArray($arrValues);
            }

            // Return the cell record that was returned from the database.
            return $Cell;
        } else {
            // Return a new cell record.
            return (new Cell())
                ->setMapId($mapId)
                ->setCoordinateX(intval($coordinateX))
                ->setCoordinateY(intval($coordinateY));
        }
    }

    // /**
    //  * I commented this out as we are not using MongoDB currently.
    //  * Check for a cell record in the database,
    //  * if it doesn't exist return a new one.
    //  *
    // * @param string $mapId       The map record's primary key
    //  * @param integer $coordinateX The x-axis co-ordinate
    //  * @param integer $coordinateY The y-axis co-ordinate
    //  *
    //  * @return Cell
    //  */
    // public static function findMongoCellsByCoordinates($mapId, $coordinateX, $coordinateY)
    // {
    //     $query = DB::connection('mongo')
    //         ->collection('Cell')
    //         ->where('coordinateX', '=', intval($coordinateX))
    //         ->where('coordinateY', '=', intval($coordinateY))
    //         ->where('mapId', '=', $mapId);

    //     $arrCell = $query->get();

    //     if (count($arrCell) > 0) {

    //         // $query->get() unfortunately returns an array.
    //         foreach ($arrCell as $key => $arrValues) {
    //             $Cell = new Cell();
    //             $Cell->populateFromArray($arrValues);
    //         }

    //         // Return the cell record that was returned from the database.
    //         return $Cell;
    //     } else {
    //         // Return a new cell record.
    //         return new Cell();
    //     }
    // }
}
