<?php
namespace App\Helpers\MapGenerators;

use App\Helpers\Cell;
use App\Helpers\Processing\TileProcessing;
use App\Helpers\Processing\CellProcessing;
use App\Helpers\Base\BaseMapGenerator;
use App\Helpers\ModelHelpers\Map as MapMemory;
use App\Helpers\NoiseMaker;

/**
 * Will use Fault Line map generator method.
 * Generating 2d height map.
 */
class FaultLine extends BaseMapGenerator
{
    protected $strDropDownDisplayName = 'Fault Line';
    protected $gridSize;
    protected $smooth;
    protected $map;
    protected $noiseMaker;
    protected $treeNoiseMaker;
    protected $cellProcessor;
    protected $arrHistory;

    /**
     * Called when an instance of Anarchy is instantiated.
     */
    public function __construct()
    {
        $this->arrHistory = array();
        $this->arrHistory[] = 'Class FaultLine created at ['.(new \DateTime())->format('Y-m-d H:i:s').']'."\n";
    }

    /**
     * Set up the Tree's array.
     *
     * @param  int  $gridsize
     * @return void
     */
    protected function setupTreeArrGrid($gridsize)
    {
        $perlinTreeMap = $this->getTreeNoiseMaker();
        for ($x = 0; $x < $gridsize; $x += 1) {
            $arrRandomizedTreeNoise[] = 182 * $perlinTreeMap->random1D($x);
        }
        // The array grid will be used later for tree noise.
        $perlinTreeMap->setArrGrid($arrRandomizedTreeNoise);
    }

    /**
     * Do this after you generate height maps. Because trees go on top of the land regardless of height.
     *
     * @param array        $arrMap        2D array
     * @param MapGenerator $perlinTreeMap Height Map noise making device.
     * @param integer      $gridsize      Max length of x, and y.
     *
     * @return array
     */
    public function doTreeLoops(&$arrMap, $gridsize, $perlinTreeMap = null)
    {
        if (is_null($perlinTreeMap)) {
            $perlinTreeMap = $this->getTreeNoiseMaker();
        }
        $smooth = $this->getSmooth();

        // Go through the grid again.
        for ($y = 0; $y < $gridsize; $y += 1) {
            for ($x = 0; $x < $gridsize; $x += 1) {

                $intNewNum = $perlinTreeMap->noise($x, $y, 0, $smooth);
                $intOldNum = $arrMap[$x][$y];

                $raw = ($intNewNum / 2) + .5;

                if ($raw < 0) {
                    $raw = 0;
                }

                $intNewNum = $raw * 255;

                if (($intNewNum > 120) && ($intNewNum < 150) && ($intOldNum > 80) && ($intOldNum < 180)) {

                    // Set to tree cell.
                    $arrMap[$x][$y] = 'T,' . $intOldNum . ',' . $intNewNum;

                    $offsettedX = $x + 1;
                    $offsettedY = $y - 1;

                    // This will sprinkle a little extra in there.
                    // Double check that offsets don't go beyond the grid size.
                    if (($offsettedX % 3 == 2) && ($offsettedX < ($gridsize - 2))
                     && ($offsettedY < ($gridsize) && ($offsettedY > 0))) {
                        $arrMap[$offsettedX][$offsettedY] = 'T,' . $intOldNum . ',' . $intNewNum;
                    }
                }
            }
        }

        // Go through the grid again. Use the height map lengths that are aproximately between 85 and 170.
        for ($y = 0; $y < $gridsize; $y += 1) {
            for ($x = 0; $x < $gridsize; $x += 1) {

                $intNewNum = $perlinTreeMap->noise($x, $y, 0, $smooth);
                $intOldNum = $arrMap[$x][$y];
                $decNum    = hexdec($intOldNum);

                $raw = ($intNewNum / 2) + .5;

                if ($raw < 0) {
                    $raw = 0;
                }

                $intNewNum = $raw * 255;

                if (($intNewNum > 200) && ($decNum > 85) && ($decNum < 170)) {

                    // Set to tree cell, This will be used later when creating the cell objects.
                    $arrMap[$x][$y] = 'T,' . $intOldNum . ',' . $intNewNum;
                }
            }
        }

        return $arrMap;
    }

    /**
     * Makes some perlin noise.
     *
     * @param array        $arrMap          2D array
     * @param MapGenerator $perlinHeightMap Height Map noise making device.
     * @param integer      $gridsize        Max length of x, and y.
     *
     * @return array
     */
    public function makeNoiseForHeightMap(&$arrMap, $perlinHeightMap = null, $gridsize = null)
    {
        if (is_null($perlinHeightMap)) {
            $perlinHeightMap = $this->getNoiseMaker();
        }
        if (is_null($gridsize)) {
            $gridsize = $this->getGridSize();
        }
        for ($y = 0; $y < $gridsize; $y += 1) {
            for ($x = 0; $x < $gridsize; $x += 1) {

                $num = $perlinHeightMap->noise($x, $y, 0, $this->getSmooth());

                $raw = ($num / 2) + .5;

                if ($raw < 0) {
                    $raw = 0;
                }

                $num = dechex($raw * 255);

                if (strlen($num) < 2) {
                    $num = "0" . $num;
                }

                $arrMap[$x][$y] = $num;
            }
        }
    }

    /**
     * This function runs the Map Generator.
     *
     * @return void
     */
    public function runGenerator()
    {        
        // If there is no map in the database create a new one.
        if (is_null($this->map)) {
            $this->setMap(new MapMemory(0, 0));
        }
        // Initialize Cell processor and tile processors.
        $objCellProcessor = new CellProcessing($this->map, new TileProcessing($this->map));
    
        $this->setCellProcessor($objCellProcessor);

        //echo '<pre>';
        //var_dump($objCellProcessor);
        //echo '</pre>';

        $this->setGridSize($this->map->getSize());
        $this->map->initialize();

        // $smooth     = $this->getSmooth();
        // $gridsize   = $this->getGridSize();
        // $waterLevel = $this->getWaterLevel();
        // $size       = 1;

        // if (is_null($smooth)) {
        //     $smooth = BaseMapGenerator::DEFAULT_SMOOTH_SIZE;
        //     $this->setSmooth($smooth);
        // }

        // if (is_null($gridsize)) {
        //     $gridsize = BaseMapGenerator::DEFAULT_GRID_SIZE;
        //     $this->setGridSize($gridsize);
        // }

        // if (is_null($waterLevel)) {
        //     $waterLevel = BaseMapGenerator::DEFAULT_WATER_LEVEL;
        //     $this->setWaterLevel($waterLevel);
        // }

        // $arrMap      = array();
        // $arrFinalMap = $this->getMap();
        // $strSeed = sha1(md5(time()));
                
        // Set up permutations for perlinHeightMap and perlinTreeMap.
        // $perlinHeightMap = new NoiseMaker($strSeed);
        // $this->setNoiseMaker($perlinHeightMap);

        // Make sure the perlin noise generator's seed is set.
        // If it isn't use a default.
        // $mxdSeed = $this->getSeed();

        //if ($mxdSeed) {
        //    $strSeed = $mxdSeed;

        //} else {
        //    $strSeed = sha1(md5(time()));
        //}

        //$this->noiseMaker->setSeed($strSeed);

        //$arrRandomizedNoise     = array();
        //$arrRandomizedTreeNoise = array();

        //for ($x = 0; $x < $gridsize; $x += 1) {
            //$arrRandomizedNoise[] = 255 * $perlinHeightMap->random1D($x);
        //}

        //$perlinHeightMap->setArrGrid($arrRandomizedNoise);

        // Using a different seed to shake things up.
        // The seed is created by reverse string of the one passed in and then used for trees.
        //$perlinTreeMap = new NoiseMaker(strrev($strSeed));
        //$this->setTreeNoiseMaker($perlinTreeMap);

        // Creating random data;
        // $this->makeNoiseForHeightMap($arrMap);
        // $this->setupTreeArrGrid($gridsize);
        // $this->doTreeLoops($arrMap, $gridsize - 1);

        // $objCellProcessor->processCellsFromHeightMap($arrMap);
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
     * Gets the value of noiseMaker.
     *
     * @return mixed
     */
    public function getNoiseMaker()
    {
        return $this->noiseMaker;
    }

    /**
     * Sets the value of noiseMaker.
     *
     * @param mixed $noiseMaker the noise maker
     *
     * @return self
     */
    public function setNoiseMaker($noiseMaker)
    {
        $this->noiseMaker = $noiseMaker;

        return $this;
    }

    /**
     * Gets the value of treeNoiseMaker.
     *
     * @return mixed
     */
    public function getTreeNoiseMaker()
    {
        return $this->treeNoiseMaker;
    }

    /**
     * Sets the value of treeNoiseMaker.
     *
     * @param mixed $treeNoiseMaker the tree noise maker
     *
     * @return self
     */
    public function setTreeNoiseMaker($treeNoiseMaker)
    {
        $this->treeNoiseMaker = $treeNoiseMaker;

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
     * Gets the value of smooth.
     *
     * @return mixed
     */
    public function getSmooth()
    {
        return $this->smooth;
    }

    /**
     * Sets the value of smooth.
     *
     * @param mixed $smooth the smooth
     *
     * @return self
     */
    public function setSmooth($smooth)
    {
        $this->smooth = $smooth;

        return $this;
    }

    /**
     * Gets the value of strDropDownDisplayName.
     *
     * @return boolean
     */
    public function hasStrDropDownDisplayName()
    {
        return (strlen($this->getStrDropDownDisplayName()) > 0);
    }

    /**
     * Gets the value of strDropDownDisplayName.
     *
     * @return string
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
     * Gets the value of cellProcessor.
     *
     * @return mixed
     */
    public function getCellProcessor()
    {
        return $this->cellProcessor;
    }

    /**
     * Sets the value of cellProcessor.
     *
     * @param mixed $cellProcessor the cell processor
     *
     * @return self
     */
    public function setCellProcessor($cellProcessor)
    {
        $this->cellProcessor = $cellProcessor;

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
        $this->cellProcessor->setWaterLevel($waterLevel);

        return $this;
    }

    /**
     * Gets the value of waterCellCount.
     *
     * @return mixed
     */
    public function getWaterLevel()
    {
        return $this->cellProcessor->getWaterLevel();
    }

    /**
     * Gets the value of waterCellCount.
     *
     * @return mixed
     */
    public function getWaterCellCount()
    {
        return $this->cellProcessor->getWaterCellCount();
    }

    /**
     * Gets the value of MountainCellCount.
     *
     * @return mixed
     */
    public function getMountainCellCount()
    {
        return $this->cellProcessor->getMountainCellCount();
    }
}
