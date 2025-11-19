<?php
namespace App\Helpers\Base;

/**
 * The base class for all Map Generators
 */
class BaseMapGenerator implements MapGeneratorInterface
{
    const DEFAULT_GRID_SIZE = 164;
    const DEFAULT_SMOOTH_SIZE = 64;
    const DEFAULT_WATER_LEVEL = 80;

    protected $map = null;
    protected $gridSize;
    protected $seed;
    protected $boolShowInMenu = false;
    protected $strDropDownDisplayName = '';

    /**
     * This is the main hook.
     */
    public function runGenerator()
    {

    }

    /**
     * Gets the value of map.
     *
     * @return mixed
     */
    public function hasMap()
    {
        return (is_null($this->map) == false);
    }

    /**
     * Gets the value of map.
     *
     * @return mixed
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Sets the value of map.
     *
     * @param mixed $map the map
     *
     * @return self
     */
    public function setMap($map)
    {
        $this->map = $map;

        return $this;
    }

    /**
     * Gets the value of gridSize.
     *
     * @return mixed
     */
    public function getGridSize()
    {
        return $this->gridSize;
    }

    /**
     * Sets the value of gridSize.
     *
     * @param mixed $gridSize the grid size
     *
     * @return self
     */
    public function setGridSize($gridSize)
    {
        $this->gridSize = $gridSize;

        return $this;
    }

    /**
     * Gets the value of boolShowInMenu.
     *
     * @return mixed
     */
    public function getBoolShowInMenu()
    {
        return $this->boolShowInMenu;
    }

    /**
     * Sets the value of boolShowInMenu.
     *
     * @param mixed $boolShowInMenu the bool show in menu
     *
     * @return self
     */
    public function setBoolShowInMenu($boolShowInMenu)
    {
        $this->boolShowInMenu = $boolShowInMenu;

        return $this;
    }

    /**
     * By default the Map Generators won't display in drop down menus.
     * Unless specified to in the class.
     *
     * @return boolean false
     */
    public function hasStrDropDownDisplayName()
    {
        return (strlen($this->getStrDropDownDisplayName()) > 0);
    }

    /**
     * Gets the value of strDropDownDisplayName.
     *
     * @return mixed
     */
    public function getStrDropDownDisplayName()
    {
        return $this->strDropDownDisplayName;
    }

    /**
     * Sets the value of strDropDownDisplayName.
     *
     * @param mixed $strDropDownDisplayName the str drop down display name
     *
     * @return self
     */
    public function setStrDropDownDisplayName($strDropDownDisplayName)
    {
        $this->strDropDownDisplayName = $strDropDownDisplayName;

        return $this;
    }

    /**
     * Gets the value of seed.
     *
     * @return mixed
     */
    public function getSeed()
    {
        return $this->seed;
    }

    /**
     * Sets the value of seed.
     *
     * @param mixed $seed the seed
     *
     * @return self
     */
    public function setSeed($seed)
    {
        $this->seed = $seed;

        return $this;
    }
}
