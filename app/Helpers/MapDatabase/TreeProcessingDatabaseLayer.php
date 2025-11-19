<?php
namespace App\Helpers\MapDatabase;

use Illuminate\Support\Facades\DB;
use App\Helpers\MapDatabase\Cell;
use App\Helpers\MapDatabase\Tile;

/**
 * Store helper functions here for fetching data from the Map database related to Tree Processing.
 */
class TreeProcessingMapDatabaseLayer
{

    /**
     * Go through each cell and random select
     * one tree tile out of a cell to destroy.
     *
     * @return void
     */
    public function holePuncher($mapId = 1)
    {
        $arrHoleLocations = array();
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

        // Query the map for tree cells.
        $strQuery = "

        select cell.id from cell
        where cell.map_id = " . $mapId . " and cell.cellType_id = 4";

        $result = CellRecord::Query($strQuery);

        if ($result) {
            while ($arrData = mysql_fetch_assoc($result)) {
                // Selects a random value from 0 to 3.
                $arrHoleLocations[$arrData['id']] = array_rand($arrTileSelectors, 1);
            }
        }
        mysql_free_result($result);

        $strQuery = "
        SELECT tile. *
        FROM  `tile` AS tile
        INNER JOIN tileType ON  `tile`.`tileType_id` = tileType.id
        AND tileType.name =  'inner-Tree'
        WHERE tile.map_id = " . $mapId;

        $result = TileRecord::Query($strQuery);

        if ($result) {
            while ($arrData = mysql_fetch_assoc($result)) {
                if (isset($arrHoleLocations[$arrData['cell_id']])) {
                    $targetCoordinate = $arrCoordinates[$arrHoleLocations[$arrData['cell_id']]];

                    $coordinateX = $arrData['coordinateX'];
                    $coordinateY = $arrData['coordinateY'];

                    if ($targetCoordinate->matchCoordinates($coordinateX, $coordinateY)) {
                        $strRemoveTreeSQL = "
                        UPDATE `tile`
                        SET    `name` = 'Passable Land',
                               `tiletype_id` = '1'
                        WHERE  `tile`.`id` = " . $arrData['id'] . ";";

                        // Delete that tile.
                        $deletResult = TileRecord::Query($strRemoveTreeSQL);

                        // Remove it from the array to cut down on these checks.
                        unset($arrHoleLocations[$arrData['cell_id']]);

                    } else {
                        $this->arrPossibleTreeTiles[$arrData['id']] = array(
                            $arrData[$mapCoordinateX],
                            $arrData[$mapCoordinateY]
                        );
                    }
                }
            }
        }
        mysql_free_result($result);

        return $this;
    }

}