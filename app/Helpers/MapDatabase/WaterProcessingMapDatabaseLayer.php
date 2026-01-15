<?php
namespace App\Helpers\MapDatabase;

use App\Helpers\MapDatabase\MapRepository;
use App\Helpers\ModelHelpers\Tile as TileHelper;

/**
 * Store helper functions here for fetching data from the Map database related to Water Processing.
 */
class WaterProcessingMapDatabaseLayer
{
    protected $mapId;
    protected $allTiles;

    /**
     * Constructor for a tile objects.
     *
     * @param integer $mapId The map's primary key
     */
    public function __construct($mapId)
    {
        $this->mapId = $mapId;
        $tiles = MapRepository::findAllTiles($mapId);

        foreach ($tiles as $tileRow) {
            foreach ($tileRow as $tile) {
                // Instantiating the helper.
                $helper = new TileHelper($tile->mapCoordinateX, $tile->mapCoordinateY);

                // Populate the helper data with the Mapdb.
                $helper->setStrType($tile->name);
                $helper->setStrTypeId($tile->tileTypeId);
                $helper->setStrDescription($tile->description);
                $helper->setIntPrimaryKey($tile->id);

                $tile->setHelper($helper);
            }
        }
        $this->allTiles = $tiles;
    }

    /**
     * Return all the cells.
     *
     * @return array of tile objects.
     */
    public function getArrTiles()
    {
        return $this->allTiles;
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
