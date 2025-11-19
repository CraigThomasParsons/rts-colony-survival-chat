<?php
namespace Generator\viewModel;

use Generator\helpers\ModelHelpers\MapLoader;
use Generator\helpers\ModelHelpers\Tile as TileHelper;
use Generator\view\Tile\TileView;

/**
 * Used to display a map that was saved to the database.
 */
class MapView
{
    protected $loadedMapData;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Gets the value of loadedMapData.
     *
     * @return mixed
     */
    public function getLoadedMapData()
    {
        return $this->loadedMapData;
    }

    /**
     * Sets the value of loadedMapData.
     *
     * @param mixed $loadedMapData the loaded map data
     *
     * @return self
     */
    public function setLoadedMapData(MapLoader $loadedMapData)
    {
        $this->loadedMapData = $loadedMapData;

        return $this;
    }

    public function setTileView($tileRecord)
    {
        // Initiate tile view.
        $tileView = new TileView();

        // Initialize and populate tile helper.
        $tileHelp = new TileHelper($tileRecord->coordinateX, $tileRecord->coordinateY);
        $tileHelp->setStrDescription($tileRecord->description);
        $tileHelp->setIntPrimaryKey($tileRecord->id);

        $tileType = $this->loadedMapData->getTileTypeById($tileRecord->tileType_id);

        if ($tileType) {
            $tileHelp->setTileDisplayType($tileType->name);
        }

        $tileView->populateByObject($tileHelp);

        return $tileView;
    }

    /**
     * Used to Display the the information in the map loader.
     *
     * @return void
     */
    public function displayloadedMapData()
    {
        echo '<table border="0" cellpadding="0" cellspacing="0" class="Preview">';

        // This view has to go through the arrays from the top down.
        for ($intYaxisCoordinate = $this->loadedMapData->size; $intYaxisCoordinate > -1; $intYaxisCoordinate--) {
            echo '<tr>';

            // The rows still go right to left.
            for ($intXaxisCoordinate = 0; $intXaxisCoordinate < $this->loadedMapData->size; $intXaxisCoordinate += 1) {

                if ($this->loadedMapData->tileIsset($intXaxisCoordinate, $intYaxisCoordinate)) {
                    $tile = $this->loadedMapData->getTile($intXaxisCoordinate, $intYaxisCoordinate);

                    // Initialize tile helper
                    $tileView = $this->setTileView($tile);
                    $tileView->displaySelf();
                }
            }
            echo '</tr>';
        }

        echo '</table>';
    }
}
