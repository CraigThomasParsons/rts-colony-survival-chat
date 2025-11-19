<?php
namespace App\Helpers;

/**
 * Simple object I'm using to store x and y axis values.
 */
class Coordinates
{
    protected $xAxis;
    protected $yAxis;

    /**
     * Instatiates an instance of Coordinates,
     * Initializes values that aren't passed in.
     *
     * @param mixed $xAxis
     * @param mixed $yAxis
     */
    public function __construct($xAxis = null, $yAxis = null)
    {
        $this->xAxis = $xAxis;
        $this->yAxis = $yAxis;
    }

    /**
     * Checks if this classes coordinates matches the ones passed in.
     *
     * @param integer $xAxis
     * @param integer $yAxis
     *
     * @return boolean
     */
    public function matchCoordinates($xAxis, $yAxis)
    {
        return (($this->xAxis == $xAxis) && ($this->yAxis == $yAxis));
    }

    /**
     * Gets the value of xAxis.
     *
     * @return mixed
     */
    public function getXAxis()
    {
        return $this->xAxis;
    }

    /**
     * Sets the value of xAxis.
     *
     * @param mixed $xAxis the x axis
     *
     * @return self
     */
    public function setXAxis($xAxis)
    {
        $this->xAxis = $xAxis;

        return $this;
    }

    /**
     * Gets the value of yAxis.
     *
     * @return mixed
     */
    public function getYAxis()
    {
        return $this->yAxis;
    }

    /**
     * Sets the value of yAxis.
     *
     * @param mixed $yAxis the y axis
     *
     * @return self
     */
    public function setYAxis($yAxis)
    {
        $this->yAxis = $yAxis;

        return $this;
    }
}
