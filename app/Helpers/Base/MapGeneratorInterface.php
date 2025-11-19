<?php
namespace App\Helpers\Base;

/**
* This interface is for MapGenerators.
* Any class implementing this class should be compatible with Map Generating views and controllers.
*/
interface MapGeneratorInterface
{

    /**
     * This is the main hook.
     */
    public function runGenerator();

    /**
     * Gets the value of map.
     *
     * @return mixed
     */
    public function getMap();

    /**
     * Sets the value of map.
     *
     * @param mixed $map the map
     *
     * @return self
     */
    public function setMap($map);

    /**
     * Gets the value of gridSize.
     *
     * @return mixed
     */
    public function getGridSize();

    /**
     * Sets the value of gridSize.
     *
     * @param mixed $mxdGridSize the grid size
     *
     * @return self
     */
    public function setGridSize($mxdGridSize);

    /**
     * Gets the value of boolShowInMenu.
     *
     * @return mixed
     */
    public function getBoolShowInMenu();

    /**
     * Sets the value of boolShowInMenu.
     *
     * @param mixed $boolShowInMenu the bool show in menu
     *
     * @return self
     */
    public function setBoolShowInMenu($boolShowInMenu);

    /**
     * Gets the value of seed.
     *
     * @return mixed
     */
    public function getSeed();

    /**
     * Sets the value of seed.
     *
     * @param mixed $seed the seed
     *
     * @return self
     */
    public function setSeed($seed);
}
