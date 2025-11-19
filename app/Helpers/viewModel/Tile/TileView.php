<?php
namespace Generator\viewModel\Tile;

/**
 * Represents single square of the 4 squares in each cell.
 */
class TileView
{

    /**
     * This function is called when a new instance of TileView is instantiated.
     */
    public function __construct()
    {
    }

    /**
     * populate by Object
     * Eventually this should be more
     * complex.
     *
     * @return void
     */
    public function populateByObject($tile)
    {
        $this->tile = $tile;
    }

    public function displaySelf()
    {
        echo $this->tile->getTableData();
    }
}
