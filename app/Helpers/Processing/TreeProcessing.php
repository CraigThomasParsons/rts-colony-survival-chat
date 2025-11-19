<?php
namespace App\Helpers\Processing;

use App\Helpers\Database\Cell as CellRecord;
use App\Helpers\Database\Tile as TileRecord;
use App\Helpers\Coordinates;

/**
 * A very simple PHP implementation of John Conway's Game of Life.
 * This implementation uses several nested for loops as well as two-dimensional
 * Arrays to create a grid for the cells in the simulation to interact.
 *
 * I did the hole puncher to get better results
 * from the game of life.
 *
 */
class TreeProcessing
{
    const FREE = 0;
    const LIFE = 1;
    const WALL = 2;

    const MOORE_NEIGHBORHOOD = 'm';
    const VON_NEUMANN_NEIGHBORHOOD = 'v';

    protected $mapLoader;
    protected $arrPossibleTreeTiles;
    protected $patternChoice;
    protected $iterations;
    protected $boolInvertSave;
    protected $treeProcessingDatabaseLayer;

    /**
     * Simple constructor
     */
    public function __construct($treeProcessingDatabaseLayer)
    {
        // Setting default values.
        $this->arrPossibleTreeTiles = array();
        $this->arrLifeGrid          = array();
        $this->patternChoice        = self::VON_NEUMANN_NEIGHBORHOOD;
        $this->boolInvertSave       = false;
        $this->mapLoader            = null;
        $this->iterations           = 25;

        // This is to help me switch from using mongo to mysql if I ever need to.
        $this->treeProcessingDatabaseLayer = $treeProcessingDatabaseLayer;
    }

    /**
     * @return boolean
     */
    public function invertSave()
    {
        return $this->boolInvertSave;
    }

    /**
     * Go through each cell and random select
     * one tree tile out of a cell to destroy.
     *
     * @return void
     */
    public function holePuncher($mapId = 1)
    {
        $this->treeProcessingDatabaseLayer->holePuncher($mapId);

        return $this;
    }

    /**
     * Check if this is a tree tile.
     *
     * @param tile $tile
     *
     * @return boolean
     */
    public function isLife($tile)
    {
        if ($tile->tileTypeId == 29) {
            return true;
        }

        return false;
    }

    /**
     * Check if this tile is a barrier to life.
     * That means tile types 3 to 27.
     *
     * @param tile $tile Tile active record
     *
     * @return boolean
     */
    public function isBarrierToLife($tile)
    {
        if ($tile->tileTypeId < 27 && $tile->tileTypeId > 3) {
            return true;
        }

        return false;
    }

    /**
     * Go through the whole MapLoader array and find water and cliff tiles,
     * mark those as 2.
     * The trees will be marked as 1, and anything else as 0.
     *
     * @return this
     */
    public function createLifeGrid()
    {
        if ($this->mapLoader) {
            $arrSearchTiles = $this->mapLoader->getArrTiles();
        }

        foreach ($arrSearchTiles as $intXaxisCoordinate => $row) {
            foreach ($row as $intYaxisCoordinate => $tile) {

                // This is a free space.
                $this->arrLifeGrid[$tile->mapCoordinateX][$tile->mapCoordinateY] = self::FREE;

                if ($this->isLife($tile)) {
                    // Tree found.
                    $this->arrLifeGrid[$tile->mapCoordinateX][$tile->mapCoordinateY] = self::LIFE;

                } elseif ($this->isBarrierToLife($tile)) {
                    // Like lakes and cliff sides.
                    $this->arrLifeGrid[$tile->mapCoordinateX][$tile->mapCoordinateY] = self::WALL;
                }

            }
        }

        return $this;
    }

    /**
     * Turn Life to Tree tiles.
     * Will actually save the life to tree tiles.
     *
     * @return void
     */
    public function turnLifeToTreeTiles()
    {
        $arrTiles = $this->mapLoader->getArrTiles();
        foreach ($arrTiles as $intXaxisCoord => &$row) {
            foreach ($row as $intYaxisCoord => &$tile) {
                // Remove tree if it is no longer in the arrLifeGrid.
                if ($this->isLife($tile)
                    && ($this->arrLifeGrid[$intXaxisCoord][$intYaxisCoord] != self::LIFE)) {
                    $tile->name        = 'Trees';
                    $tile->tileTypeId = 29;
                }
                if (($this->isBarrierToLife($tile) == false)
                    && ($this->arrLifeGrid[$intXaxisCoord][$intYaxisCoord] == self::LIFE)) {
                    $tile->name        = 'Passable Land';
                    $tile->tileTypeId = 1;
                }
            }
        }

        $this->mapLoader->setArrTiles($arrTiles);

        foreach ($arrTiles as $intXaxisCoord => &$row) {
            foreach ($row as $intYaxisCoord => &$tile) {
                if ($this->invertSave() == true) {
                    if ($this->arrLifeGrid[$intXaxisCoord][$intYaxisCoord] == self::LIFE) {
                        $tile->name        = 'Passable Land';
                        $tile->tileTypeId = 1;

                    } else {
                        $tile->name        = 'Trees';
                        $tile->tileTypeId = 29;
                    }
                    $tile->save();
                } else {
                    if ($this->arrLifeGrid[$intXaxisCoord][$intYaxisCoord] == self::LIFE) {
                        $tile->name        = 'Trees';
                        $tile->tileTypeId = 29;

                    } else {
                        $tile->name        = 'Passable Land';
                        $tile->tileTypeId = 1;
                    }
                    $tile->save();
                }

            }
        }
    }

    /**
     * run John Conway's Game Of Life algorithm
     *
     * @return this
     */
    public function runJohnConwaysGameOfLife()
    {
        // Helps make the cells spread a little bit more in random directions.
        // $this->holePuncher();

        // Initializes the grid from the database.
        $this->createLifeGrid();

        // This will get the main algorithm running.
        $this->life();

        $this->setPatternChoice(self::MOORE_NEIGHBORHOOD);

        // Run the algoritm.
        for ($i = 1; $i < 3; $i++) {
            $this->lifeCheckVonNeumannNeighborhood(true);
        }

        $this->turnLifeToTreeTiles();

        return $this;
    }

    /**
     * The life function is the most important function in the program.
     * It counts the number of cells surrounding the center cell, and
     * determines whether it lives, dies, or stays the same.
     *
     * @return this
     */
    protected function life()
    {
        $choice = $this->getPatternChoice();

        if ($choice == self::MOORE_NEIGHBORHOOD) {
            for ($i = 1; $i < $this->iterations; $i++) {
                $this->lifeCheckMooreNeighborhood();
            }

        } elseif ($choice == self::VON_NEUMANN_NEIGHBORHOOD) {
            for ($i = 1; $i < $this->iterations; $i++) {
                $this->lifeCheckVonNeumannNeighborhood();
            }
        }

        return $this;
    }

    /**
     * The Moore neighborhood checks all 8 cells surrounding the current cell in the array.
     *
     * The cell stays the same if the countLife is 2 so I didn't bother adding in that change.
     * if ($countLife == 2) {
     * } Code is left here as an FYI.
     *
     * @return void
     */
    public function lifeCheckMooreNeighborhood()
    {
        foreach ($this->arrLifeGrid as $j => $row) {
            foreach ($row as $i => $tile) {

                // Re-intializing.
                $countLife = 0;

                if (isset($this->arrLifeGrid[$j - 1][$i]) && $this->arrLifeGrid[$j - 1][$i]) {
                    $countLife += (int) ($this->arrLifeGrid[$j - 1][$i] == self::LIFE);
                }

                if (isset($this->arrLifeGrid[$j + 1][$i]) && $this->arrLifeGrid[$j + 1][$i]) {
                    $countLife += (int) ($this->arrLifeGrid[$j + 1][$i] == self::LIFE);
                }

                if (isset($this->arrLifeGrid[$j][$i - 1]) && $this->arrLifeGrid[$j][$i - 1]) {
                    $countLife += (int) ($this->arrLifeGrid[$j][$i - 1] == self::LIFE);
                }

                if (isset($this->arrLifeGrid[$j][$i + 1]) && $this->arrLifeGrid[$j][$i + 1]) {
                    $countLife += (int) ($this->arrLifeGrid[$j][$i + 1] == self::LIFE);
                }

                if (isset($this->arrLifeGrid[$j - 1][$i + 1]) && $this->arrLifeGrid[$j - 1][$i + 1]) {
                    $countLife += (int) ($this->arrLifeGrid[$j - 1][$i + 1] == self::LIFE);
                }

                if (isset($this->arrLifeGrid[$j - 1][$i - 1]) && $this->arrLifeGrid[$j - 1][$i - 1]) {
                    $countLife += (int) ($this->arrLifeGrid[$j - 1][$i - 1] == self::LIFE);
                }

                if (isset($this->arrLifeGrid[$j + 1][$i + 1]) && $this->arrLifeGrid[$j + 1][$i + 1]) {
                    $countLife += (int) ($this->arrLifeGrid[$j + 1][$i + 1] == self::LIFE);
                }

                if (isset($this->arrLifeGrid[$j + 1][$i - 1]) && $this->arrLifeGrid[$j + 1][$i - 1]) {
                    $countLife += (int) ($this->arrLifeGrid[$j + 1][$i - 1] == self::LIFE);
                }

                // The cell dies because there is either too much neighboring life or too little.
                if ($countLife < 2 || $countLife > 3) {
                    if ($this->arrLifeGrid[$j][$i] == self::LIFE) {
                        $this->arrLifeGrid[$j][$i] = self::FREE;
                    }
                }

                // The cell either stays alive, or is "born".
                if ($countLife == 3) {
                    if ($this->arrLifeGrid[$j][$i] != self::WALL) {
                        $this->arrLifeGrid[$j][$i] = self::LIFE;
                    }
                }
            }
        }
        $this->turnLifeToTreeTiles();

        return $this;
    }

    /**
     * The Von Neumann neighborhood checks only the 4 surrounding cells in the array,
     * (North, Sourth, East, and West).
     *
     * @return void
     */
    public function lifeCheckVonNeumannNeighborhood($onlyCreate = false)
    {
        foreach ($this->arrLifeGrid as $j => $row) {
            foreach ($row as $i => $tile) {

                // Re-intializing.
                $countLife = 0;

                if (isset($this->arrLifeGrid[$j - 1][$i]) && $this->arrLifeGrid[$j - 1][$i]) {
                    $countLife += (int) ($this->arrLifeGrid[$j - 1][$i] == self::LIFE);
                }

                if (isset($this->arrLifeGrid[$j + 1][$i]) && $this->arrLifeGrid[$j + 1][$i]) {
                    $countLife += (int) ($this->arrLifeGrid[$j + 1][$i] == self::LIFE);
                }

                if (isset($this->arrLifeGrid[$j][$i - 1]) && $this->arrLifeGrid[$j][$i - 1]) {
                    $countLife += (int) ($this->arrLifeGrid[$j][$i - 1] == self::LIFE);
                }

                if (isset($this->arrLifeGrid[$j][$i + 1]) && $this->arrLifeGrid[$j][$i + 1]) {
                    $countLife += (int) ($this->arrLifeGrid[$j][$i + 1] == self::LIFE);
                }

                if (isset($this->arrLifeGrid[$j + 1][$i - 1]) && $this->arrLifeGrid[$j + 1][$i - 1]) {
                    $countLife += (int) ($this->arrLifeGrid[$j + 1][$i - 1] == self::LIFE);
                }

                // The cell dies because there is either too much neighboring life or too little.
                if ($onlyCreate == false) {
                    if ($countLife < 2 || $countLife > 3) {
                        if ($this->arrLifeGrid[$j][$i] == self::LIFE) {
                            $this->arrLifeGrid[$j][$i] = self::FREE;
                        }
                    }
                }

                // The cell either stays alive, or is "born".
                if ($countLife == 3) {
                    if ($this->arrLifeGrid[$j][$i] != self::WALL) {
                        $this->arrLifeGrid[$j][$i] = self::LIFE;
                    }
                }
            }
        }
    }

    /**
     * Removes the tree tile found out on their own in the wide open.
     * If there are 8 neighboring dead cells the tree cell will be removed.
     * I have an idea to have a 1 in 4 chance to leave an orphan.
     * not implemented yet.
     *
     * @param integer $intDeadTreeCountThreshold Defaults, to 8.
     *
     * @return self
     */
    public function purgeOrphans($intDeadTreeCountThreshold = 8)
    {
        $this->createLifeGrid();

        foreach ($this->arrLifeGrid as $j => $row) {
            foreach ($row as $i => $tile) {

                // Re-intializing.
                $countDeath = 0;

                if (isset($this->arrLifeGrid[$j - 1][$i])) {
                    $countDeath += (int) ($this->arrLifeGrid[$j - 1][$i] != self::LIFE);
                }
                if (isset($this->arrLifeGrid[$j + 1][$i])) {
                    $countDeath += (int) ($this->arrLifeGrid[$j + 1][$i] != self::LIFE);
                }
                if (isset($this->arrLifeGrid[$j][$i - 1])) {
                    $countDeath += (int) ($this->arrLifeGrid[$j][$i - 1] != self::LIFE);
                }
                if (isset($this->arrLifeGrid[$j][$i + 1])) {
                    $countDeath += (int) ($this->arrLifeGrid[$j][$i + 1] != self::LIFE);
                }
                if (isset($this->arrLifeGrid[$j - 1][$i + 1])) {
                    $countDeath += (int) ($this->arrLifeGrid[$j - 1][$i + 1] != self::LIFE);
                }
                if (isset($this->arrLifeGrid[$j - 1][$i - 1])) {
                    $countDeath += (int) ($this->arrLifeGrid[$j - 1][$i - 1] != self::LIFE);
                }
                if (isset($this->arrLifeGrid[$j + 1][$i + 1])) {
                    $countDeath += (int) ($this->arrLifeGrid[$j + 1][$i + 1] != self::LIFE);
                }
                if (isset($this->arrLifeGrid[$j + 1][$i - 1])) {
                    $countDeath += (int) ($this->arrLifeGrid[$j + 1][$i - 1] != self::LIFE);
                }

                if ($countDeath >= $intDeadTreeCountThreshold) {
                    $this->arrLifeGrid[$j][$i] = self::FREE;
                }
            }
        }
        $this->turnLifeToTreeTiles();

        return $this;
    }

    /**
     * Gets the value of mapLoader.
     *
     * @return mixed
     */
    public function getMapLoader()
    {
        return $this->mapLoader;
    }

    /**
     * Sets the value of mapLoader.
     *
     * @param mixed $mapLoader the map loader
     *
     * @return self
     */
    public function setMapLoader($mapLoader)
    {
        $this->mapLoader = $mapLoader;

        return $this;
    }

    /**
     * Gets the value of arrPossibleTreeTiles.
     *
     * @return mixed
     */
    public function getArrPossibleTreeTiles()
    {
        return $this->arrPossibleTreeTiles;
    }

    /**
     * Sets the value of arrPossibleTreeTiles.
     *
     * @param mixed $arrPossibleTreeTiles the arr possible tree tiles
     *
     * @return self
     */
    public function setArrPossibleTreeTiles($arrPossibleTreeTiles)
    {
        $this->arrPossibleTreeTiles = $arrPossibleTreeTiles;

        return $this;
    }

    /**
     * Gets the value of patternChoice.
     *
     * @return mixed
     */
    public function getPatternChoice()
    {
        return $this->patternChoice;
    }

    /**
     * Sets the value of patternChoice.
     *
     * @param mixed $patternChoice the pattern choice
     *
     * @return self
     */
    public function setPatternChoice($patternChoice)
    {
        $this->patternChoice = $patternChoice;

        return $this;
    }

    /**
     * Gets the value of iterations.
     *
     * @return mixed
     */
    public function getIterations()
    {
        return $this->iterations;
    }

    /**
     * Sets the value of iterations.
     *
     * @param mixed $iterations the iterations
     *
     * @return self
     */
    public function setIterations($iterations)
    {
        $this->iterations = $iterations;

        return $this;
    }

    /**
     * Gets the value of boolInvertSave.
     *
     * @return mixed
     */
    public function getBoolInvertSave()
    {
        return $this->boolInvertSave;
    }

    /**
     * Sets the value of boolInvertSave.
     *
     * @param mixed $boolInvertSave the bool invert save
     *
     * @return self
     */
    public function setBoolInvertSave($boolInvertSave)
    {
        $this->boolInvertSave = $boolInvertSave;

        return $this;
    }
}
