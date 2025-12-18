<?php
namespace App\Helpers\Processing;

use Generator\helpers\ModelHelpers\Cell;
use Generator\helpers\ModelHelpers\Tile;
use App\Helpers\Coordinates;

/**
 * Todos:
 *
 * Create CellProcessing
 * The main loop that decides what each cell will be, is going to be in this class.
 * For mountains we will need to re-run this maybe 2 times.
 *
 * 2)
 * So that you don't loop through each and every single water tile in the Map.
 * On construction put water, land and mountain coordinates in their own arrays.
 *
 * Also this should make it easier to fine tune the cell arrays mentioned in #2.
 * Write a way of finding separate mountains.
 * This way you can make an algorithm foreach mountain.
 *
 * Maze Stab. Run the maze algorithm on the moutains. Find the square around the terrain.
 * Run the maze algorithm on the mountain area.
 * Make sure that you stab some space inside so that people can get in.
 *
 */
class CellProcessing
{
    const DEFAULT_MOUNTAIN_LINE = 156;
    const UNACCEPTABLE_WATER_COUNT = 235;

    protected $map;

    protected $waterLevel;
    protected $waterCellCount;

    protected $mountainLine;
    protected $originalMountainLine;
    protected $mountainCellCount;

    protected $arrMountainCoordinates;
    protected $arrWaterCoordinates;

    protected $tileProcessor;

    protected $boolSaveToDatabaseWhileProcessing = true;

    /**
     * Simple constructor
     *
     * @param map &$map
     */
    public function __construct(&$map, $tileProcessor = null)
    {
        $this->map = $map;
        $this->arrMountainCoordinates = array();
        $this->arrWaterCoordinates    = array();

        if (is_null($tileProcessor)) {
            $this->tileProcessor = new TileProcessing($map);

        } else {
            $this->tileProcessor = $tileProcessor;
        }

    }

    /**
     * Sets defaults if they aren't already set.
     *
     * @return void
     */
    public function intialialize()
    {
        if (is_null($this->mountainLine)) {
            $this->setMountainLine(CellProcessing::DEFAULT_MOUNTAIN_LINE);
        }
    }

    /**
     * process Cells
     *
     * @return void
     */
    public function processCells()
    {
        $arrCells = $this->map->getArrCells();
    }

    /**
     * Process Cells From a Height Map array
     *
     * @return void
     */
    public function processCellsFromHeightMap($arrMap)
    {
        //Set's things up for processing.
        $this->intialialize();

        $savingEnabled = $this->getBoolSaveToDatabaseWhileProcessing();

        if ($savingEnabled) {
            $mapPrimaryKey = $this->map->databaseRecord->id;
        }

        $waterCellCount    = 0;
        $mountainCellCount = 0;

        foreach ($arrMap as $key => $rowValue) {

            foreach ($rowValue as $colKey => $mxdNum) {

                $strDescription = '';

                // Retreive cell object.
                $tmpCell = $this->map[$key][$colKey];

                // Grab the first character.
                $chrFirst = substr($mxdNum, 0, 1);

                // If this isn't a tree.
                if ($chrFirst != 'T') {

                    $strHexNum = $mxdNum;

                    // Will be storing this in the database.
                    $decNum = hexdec($mxdNum);

                    // Record Height and hex representation.
                    $tmpCell->setIntHeight($decNum);
                    $tmpCell->setStrHexHeight($strHexNum);

                    if ($decNum < $this->getWaterLevel()) {
                        $waterCellCount++;

                        // Label the cell as water.
                        $tmpCell->setStrType('Water');

                        // Record Height.
                        $tmpCell->setIntHeight($decNum);

                        $strDescription = 'Water Cell @ height:' . $decNum . '; ';
                        $strDescription = 'Water-Count: ' . $waterCellCount . ';';

                        // Not really necessary but I'm keeping this old info.
                        $tmpCell->setStrDescription($strDescription);

                        $this->arrWaterCoordinates[] = new Coordinates($key, $colKey);

                    } elseif ($decNum > $this->getMountainLine()) {
                        $mountainCellCount++;

                        // Label the cell as Mountains.
                        $tmpCell->setStrType('Impassable Rocks');

                        // Record Height.
                        $tmpCell->setIntHeight($decNum);

                        // Displaying Rocky Mountains, not passable.
                        $strDescription = 'Mountain Cell @ height: ' . $decNum . '; ';
                        $strDescription .= '+Mountain-Count: ' . $mountainCellCount . ';';

                        $tmpCell->setStrDescription($strDescription);

                        $this->addMountainCoordinate(new Coordinates($key, $colKey));

                    } else {
                        // Label the cell as Land.
                        $tmpCell->setStrType('Passable Land');

                        // Displaying Land.
                        $tmpCell->setStrDescription('Land @ height: ' . $decNum . ';');
                    }

                } else {
                    // The trees won't always be based on heights like other cell's are.
                    $arrNum = explode(',', $mxdNum);
                    $decNum = hexdec($arrNum[1]);

                    // Label the cell as Trees.
                    $tmpCell->setStrType('Trees');

                    // Record Height.
                    $tmpCell->setIntHeight($decNum);
                    $tmpCell->setStrDescription('Trees @ height: ' . $decNum . ';');
                }

                if ($savingEnabled) {
                    $tmpCell->setMapPrimaryKey($mapPrimaryKey);
                    $tmpCell->persist();
                }

            } // End of foreach

        } // End of foreach

        // If the water count is too lower increase the water level a bit.
        if ($waterCellCount < self::UNACCEPTABLE_WATER_COUNT) {

            // Destroy the water coordinate array and start over.
            $this->arrWaterCoordinates[] = array();
            $waterCellCount = 0;

            // Increasing the count by raising the water level.
            $waterLevel = 95;

            foreach ($arrMap as $rowKey => $rowValue) {

                foreach ($rowValue as $colKey => $mxdNum) {

                    // Will be storing this in the database.
                    $decNum    = hexdec($mxdNum);
                    $strHexNum = $mxdNum;

                    if ($decNum < $waterLevel) {
                        // Making new cell a water cell.
                        $cell = $this->map[$colKey][$rowKey];
                        $waterCellCount++;

                        // Label the cell as water.
                        $cell->setStrType('Water');

                        // Record Height.
                        $cell->setIntHeight($decNum);

                        $strDescription = 'Water Cell, after raising water level.';
                        $strDescription .= ' now @ height: ' . $decNum . '; Water-Count';

                        $cell->setStrDescription($strDescription);
                        $this->arrWaterCoordinates[] = new Coordinates($colKey, $rowKey);
                    }

                } // End of foreach

            } // End of foreach

        }
        $this->setWaterCellCount($waterCellCount);
        $this->setMountainCellCount($mountainCellCount);
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
     * Sets the value of waterLevel.
     *
     * @param mixed $waterLevel the water max
     *
     * @return self
     */
    public function setWaterLevel($waterLevel)
    {
        $this->waterLevel = $waterLevel;

        return $this;
    }

    /**
     * Gets the value of waterCellCount.
     *
     * @return mixed
     */
    public function getWaterCellCount()
    {
        return $this->waterCellCount;
    }

    /**
     * Sets the value of waterCellCount.
     *
     * @param mixed $waterCellCount the water cell count
     *
     * @return self
     */
    public function setWaterCellCount($waterCellCount)
    {
        $this->waterCellCount = $waterCellCount;

        return $this;
    }

    /**
     * Gets the value of MountainCellCount.
     *
     * @return mixed
     */
    public function getMountainCellCount()
    {
        return $this->mountainCellCount;
    }

    /**
     * Sets the value of MountainCellCount.
     *
     * @param mixed $MountainCellCount the mountain cell count
     *
     * @return self
     */
    public function setMountainCellCount($MountainCellCount)
    {
        $this->mountainCellCount = $MountainCellCount;

        return $this;
    }

    /**
     * Gets the value of waterLevel.
     *
     * @return mixed
     */
    public function getWaterLevel()
    {
        return $this->waterLevel;
    }

    /**
     * Gets the value of mountainLine.
     *
     * @return mixed
     */
    public function getMountainLine()
    {
        return $this->mountainLine;
    }

    /**
     * Sets the value of mountainLine.
     *
     * @param mixed $mountainLine the mountain line
     *
     * @return self
     */
    public function setMountainLine($mountainLine)
    {
        static $intCallCount;

        if (isset($intCallCount) === false) {
            $intCallCount = 1;

        } else {
            $intCallCount++;
        }

        // If this is the second time setMountainLine is being called.
        if ($intCallCount == 2) {

            // Keep the original amount.
            $this->setOriginalMountainLine($this->getMountainLine());
        }

        $this->mountainLine = $mountainLine;

        return $this;
    }

    /**
     * Gets the value of originalMountainLine.
     *
     * @return mixed
     */
    public function getOriginalMountainLine()
    {
        return $this->originalMountainLine;
    }

    /**
     * Sets the value of originalMountainLine.
     *
     * @param mixed $originalMountainLine the original mountain line
     *
     * @return self
     */
    public function setOriginalMountainLine($originalMountainLine)
    {
        $this->originalMountainLine = $originalMountainLine;

        return $this;
    }

    /**
     * Gets the value of arrMountainCoordinates.
     *
     * @return mixed
     */
    public function getArrMountainCoordinates()
    {
        return $this->arrMountainCoordinates;
    }

    /**
     * Sets the value of arrMountainCoordinates.
     *
     * @param mixed $arrMountainCoordinates the arr mountain coordinates
     *
     * @return self
     */
    public function setArrMountainCoordinates($arrMountainCoordinates)
    {
        $this->arrMountainCoordinates = $arrMountainCoordinates;

        return $this;
    }

    /**
     * Adds a value of arrMountainCoordinates.
     *
     * @param  Coordinates $mxdMountainCoordinates The cell coordinates of a Rocky cell.
     * @return self
     */
    public function addMountainCoordinate(Coordinates $mxdMountainCoordinates, $boolDoArrMountainCoordinates = true)
    {
        if ($boolDoArrMountainCoordinates) {
            $this->arrMountainCoordinates[] = $mxdMountainCoordinates;
        }

        $arrOffset = array(0, 1);
        foreach ($arrOffset as $offsetValueX) {
            foreach ($arrOffset as $offsetValueY) {
                $xTwo = (2 * $mxdMountainCoordinates->getXAxis()) + $offsetValueX;
                $yTwo = (2 * $mxdMountainCoordinates->getYAxis()) + $offsetValueY;

                // There is four tile coordinates per cell.
                $this->tileProcessor->addArrPossibleCliffTiles(new Coordinates($xTwo, $yTwo));
            }
        }

        return $this;
    }

    /**
     * Gets the value of arrWaterCoordinates.
     *
     * @return mixed
     */
    public function getArrWaterCoordinates()
    {
        return $this->arrWaterCoordinates;
    }

    /**
     * Sets the value of arrWaterCoordinates.
     *
     * @param mixed $arrWaterCoordinates the arr water coordinates
     *
     * @return self
     */
    public function setArrWaterCoordinates($arrWaterCoordinates)
    {
        $this->arrWaterCoordinates = $arrWaterCoordinates;

        return $this;
    }

    /**
     * Gets the value of tileProcessor.
     *
     * @return mixed
     */
    public function getTileProcessor()
    {
        return $this->tileProcessor;
    }

    /**
     * Sets the value of tileProcessor.
     *
     * @param mixed $tileProcessor the tile processor
     *
     * @return self
     */
    public function setTileProcessor($tileProcessor)
    {
        $this->tileProcessor = $tileProcessor;

        return $this;
    }

    /**
     * Gets the value of boolSaveToDatabaseWhileProcessing.
     *
     * @return mixed
     */
    public function getBoolSaveToDatabaseWhileProcessing()
    {
        return $this->boolSaveToDatabaseWhileProcessing;
    }

    /**
     * Sets the value of boolSaveToDatabaseWhileProcessing.
     *
     * @param mixed $boolSaveToDatabaseWhileProcessing the bool save to database while processing
     *
     * @return self
     */
    public function setBoolSaveToDatabaseWhileProcessing($boolSaveToDatabaseWhileProcessing)
    {
        $this->boolSaveToDatabaseWhileProcessing = $boolSaveToDatabaseWhileProcessing;

        return $this;
    }

    /**
     * Tell this instance of cell processing to save to the database while processing.
     *
     * @return self
     */
    public function saveToDatabaseWhileProcessing()
    {
        $this->setBoolSaveToDatabaseWhileProcessing(true);

        return $this;
    }

    /**
     * Tell this instance of cell processing not to Save to the database while processing
     *
     * @return self
     */
    public function doNotSaveToDatabaseWhileProcessing()
    {
        $this->setBoolSaveToDatabaseWhileProcessing(false);

        return $this;
    }
}
