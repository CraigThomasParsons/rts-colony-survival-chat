<?php
namespace App\Helpers;

/**
 * Helper function for generating height maps.
 * Original Approximate Date of Creation: Monday February 08 2008
 *
 * Date of Last Modification: Thursday May 15 2014
 * todo:
 *   - See if you can create a Map shuffler, to randomize trees and water cells.
 */
class HeightMapHelper
{

    protected $randMin;
    protected $randMax;
    protected $arrHeightArray;

    /**
     * Smooths out an array of heights.
     * Converted a class I wrote in C
     *
     * Author: Craig Parsons
     * Function name: SmoothAlgorithm
     *
     * Date of Creation: Monday February 08 2008
     * Date of Last Modification: Thursday May 15 2014
     *
     * @param integer $intTop        Top $intExCoordinate coordinate
     * @param integer $intLeft       Leftest y coordinate
     * @param integer $intBottom     Lowest $intExCoordinate coord
     * @param integer $intRight      Leftest y coordinate
     * @param integer $intIterations How many times were going to loop through this array.
     */
    public function heightSmoothAlgorithm($intTop, $intLeft, $intBottom, $intRight, $intIterations)
    {
        $intExCoordinate   = 0;
        $intWhyCoordinate  = 0;
        $intIterationCount = 0;

        $intHeight = $intBottom - $intTop;
        $intWidth  = $intRight - $intLeft;
        $dblSum    = 0.0;

        for ($intIterationCount = 0; $intIterationCount < $intIterations; $intIterationCount++) {
            for ($intWhyCoordinate = 0; $intWhyCoordinate < $intWidth; $intWhyCoordinate++) {
                for ($intExCoordinate = 0; $intExCoordinate < $intHeight; $intExCoordinate++) {

                    $dblSum    = $this->arrHeightArray[$intExCoordinate][$intWhyCoordinate];
                    $numValues = 1;

                    if ($intExCoordinate > 0) {

                        $dblSum += $this->arrHeightArray[$intExCoordinate - 1][$intWhyCoordinate];
                        $numValues++;
                    }

                    if ($intExCoordinate < $intWidth - 1) {

                        $dblSum += $this->arrHeightArray[$intExCoordinate + 1][$intWhyCoordinate];
                        $numValues++;
                    }

                    if ($intWhyCoordinate > 0) {

                        $dblSum += $this->arrHeightArray[$intExCoordinate][$intWhyCoordinate - 1];
                        $numValues++;
                    }

                    if ($intWhyCoordinate < $intHeight - 1) {

                        $dblSum += $this->arrHeightArray[$intExCoordinate][$intWhyCoordinate + 1];
                        $numValues++;
                    }
                    // The new height will be the average of all it's neightbors.
                    $this->arrHeightArray[$intExCoordinate][$intWhyCoordinate] = $dblSum / $numValues;

                }
            }
        }

    }

    /**
     * Function name: MidpointDisplacementGen
     * Purpose: calls the MidpointDislacement Alg over the whole
     * Heightmap
     *
     * @return void
     */
    public function heightMidpointDisplacementGenFull()
    {
        if ($intSizeX < $intSizeZ) {
            $intSize = $intSizeX;

        } else {
            $intSize = $intSizeZ;
        }


        while (powerOf2($intSize) != 1) {
            $intSize--;
        }

        //$intSize is power of 2+1
        MidpointDisplacementGen(0, 0, $intSize * 2, $intSize * 2);
    }

    /**
     * Author: Craig Parsons
     * Function name: MidpointDisplacement
     * Date of Creation: Monday February 04 2008
     * Parameters: the coordinates of the indices of the
     *             square inside the height map
     *             that are to be tessalated.
     *
     * Description: noise maker
     * Algorithm: Pseudo code taken from
     * http://www.gameprogrammer.com/fractal.html#diamond
     *
     * Start:
     * Do diamond step.
     * Do square step.
     * Reduce random number range.
     * Call myself four times.
     * End:
     *
     * Last Modified:
     * February 09 2008: made sure that the heights are all added not overwriten
     *                   so that I can do this algoritm after the hillgorithm or
     *                   faultline. pretty much just made '=' become '+=' so that
     *                   i can do this after the other algorithms to add noise
     *
     *
     * @param [type] $intTop
     * @param [type] $intLeft
     * @param [type] $intBottom
     * @param [type] $intRight
     *
     * @return integer 0 if successful.
     */
    public function heightMidpointDisplacementGen($intTop, $intLeft, $intBottom, $intRight)
    {
        $a = 0;
        $b = 0;
        $c = 0;
        $d = 0;

        $intHeight = $intBottom - $intTop;
        $intWidth  = $intRight - $intLeft;

        if ($intWidth <= 1) {
            return (0);
        }

        if ($intHeight <= 1) {
            return (0);
        }

        $dblMiddle = ($intBottom + $intTop) / 2;
        $dblCenter = ($intRight + $intLeft) / 2;

        // They were caste as integers in the c++ version.
        $dblMiddle = intval($dblMiddle);
        $dblCenter = intval($dblCenter);

        $a = $this->arrHeightArray[$intTop][$intLeft] += randum($this->randMin, $this->randMax);
        $b = $this->arrHeightArray[$intTop][$intRight] += randum($this->randMin, $this->randMax);
        $c = $this->arrHeightArray[$intBottom][$intLeft] += randum($this->randMin, $this->randMax);
        $d = $this->arrHeightArray[$intBottom][$intRight] += randum($this->randMin, $this->randMax);

        $this->arrHeightArray[$dblMiddle][$dblCenter] = ($a + $b + $c + $d) / 4;

        $this->arrHeightArray[$intTop][$dblCenter]    = ($a + $b) / 2;
        $this->arrHeightArray[$intBottom][$dblCenter] = ($c + $d) / 2;
        $this->arrHeightArray[$dblMiddle][$intLeft]   = ($c + $a) / 2;
        $this->arrHeightArray[$dblMiddle][$intRight]  = ($b + $d) / 2;

        MidpointDisplacementGen($intTop, $dblCenter, $dblMiddle, $intRight);
        MidpointDisplacementGen($intTop, $intLeft, $dblMiddle, $dblCenter);
        MidpointDisplacementGen($dblMiddle, $intLeft, $intBottom, $dblCenter);
        MidpointDisplacementGen($dblMiddle, $dblCenter, $intBottom, $intRight);

        return 0;
    }

    /**
     * Gets the value of arrHeightArray.
     *
     * @return mixed
     */
    public function getArrHeightArray()
    {
        return $this->arrHeightArray;
    }

    /**
     * Sets the value of arrHeightArray.
     *
     * @param mixed $arrHeightArray the arr height array
     *
     * @return self
     */
    public function setArrHeightArray($arrHeightArray)
    {
        $this->arrHeightArray = $arrHeightArray;

        return $this;
    }

    /**
     * Gets the value of randMin.
     *
     * @return mixed
     */
    public function getRandMin()
    {
        return $this->randMin;
    }

    /**
     * Sets the value of randMin.
     *
     * @param mixed $randMin the rand min
     *
     * @return self
     */
    public function setRandMin($randMin)
    {
        $this->randMin = $randMin;

        return $this;
    }

    /**
     * Gets the value of randMax.
     *
     * @return mixed
     */
    public function getRandMax()
    {
        return $this->randMax;
    }

    /**
     * Sets the value of randMax.
     *
     * @param mixed $randMax the rand max
     *
     * @return self
     */
    public function setRandMax($randMax)
    {
        $this->randMax = $randMax;

        return $this;
    }
}
