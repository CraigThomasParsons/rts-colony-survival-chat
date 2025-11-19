<?php
namespace App\Helpers\ModelHelpers;

/**
 * Represents a single square of the 4 squares in each cell.
 */
class Tile
{
    /**
     * If the data of this instance was populated from a record in the database.
     * This variable will be useful in debugging when I need to double check the db.
     *
     * @var integer
     */
    protected $intPrimaryKey;

    protected $intXaxisCoordinate;
    protected $intYaxisCoordinate;
    protected $intHeight;
    protected $strType;
    protected $strTypeId;
    protected $strDescription;

    // Set by Tile Processing. Used for displaying the tile.
    protected $strTileDisplayType;
    protected $objTileDisplayType;

    /**
     * Constructor for a cell object.
     *
     * @param integer $intXaxisCoordinate
     * @param integer $intYaxisCoordinate
     */
    public function __construct($intXaxisCoordinate = 0, $intYaxisCoordinate = 0)
    {
        $this->setIntXaxisCoordinate($intXaxisCoordinate);
        $this->setIntYaxisCoordinate($intYaxisCoordinate);
    }

    /**
     * Gets the value of a Cell's intXaxisCoordinate.
     *
     * @param mixed $intXaxisCoordinate The map's int xaxis coordinate (the whole Map).
     *
     * @return mixed The map's cell Xaxis coordinate
     */
    public function getCellIntXaxisCoordinate($intXaxisCoordinate)
    {
        $intXaxisOffsetCoord = $this->intXaxisCoordinate;
        $intCellXCoordinate  = ($intXaxisCoordinate - $intXaxisOffsetCoord) / 2;

        return $intCellXCoordinate;
    }

    /**
     * Gets the value of intXaxisCoordinate.
     *
     * @return mixed
     */
    public function getIntXaxisCoordinate()
    {
        return $this->intXaxisCoordinate;
    }

    /**
     * Sets the value of intXaxisCoordinate.
     *
     * @param mixed $intXaxisCoordinate the int xaxis coordinate
     *
     * @return self
     */
    public function setIntXaxisCoordinate($intXaxisCoordinate)
    {
        $this->intXaxisCoordinate = $intXaxisCoordinate;

        return $this;
    }

    /**
     * Gets the value of cell's intYaxisCoordinate.
     *
     * @param mixed $intYaxisCoordinate The Tile int xaxis coordinate of the whole Map.
     *
     * @return mixed The map's cell yaxis coordinate
     */
    public function getCellIntYaxisCoordinate($intYaxisCoordinate)
    {
        $intYaxisOffsetCoord = $this->intYaxisCoordinate;
        $intCellYCoordinate  = ($intYaxisCoordinate - $intYaxisOffsetCoord) / 2;

        return $intCellYCoordinate;
    }

    /**
     * Gets the value of intYaxisCoordinate.
     *
     * @return mixed
     */
    public function getIntYaxisCoordinate()
    {
        return $this->intYaxisCoordinate;
    }

    /**
     * Sets the value of intYaxisCoordinate.
     *
     * @param mixed $intYaxisCoordinate the int yaxis coordinate
     *
     * @return self
     */
    public function setIntYaxisCoordinate($intYaxisCoordinate)
    {
        $this->intYaxisCoordinate = $intYaxisCoordinate;

        return $this;
    }

    /**
     * Gets the value of intHeight.
     *
     * @return mixed
     */
    public function getIntHeight()
    {
        return $this->intHeight;
    }

    /**
     * Sets the value of intHeight.
     *
     * @param mixed $intHeight the int height
     *
     * @return self
     */
    public function setIntHeight($intHeight)
    {
        $this->intHeight = $intHeight;

        return $this;
    }

    /**
     * Gets the value of strType.
     *
     * @return mixed
     */
    public function getStrType()
    {
        return $this->strType;
    }

    /**
     * Sets the value of strType.
     *
     * @param mixed $strType the str type
     *
     * @return self
     */
    public function setStrType($strType)
    {
        $this->strType = $strType;

        return $this;
    }

    /**
     * Checks to see if this type is not water.
     *
     * @return boolean
     */
    public function notWater()
    {
        return ($this->strType != 'Water');
    }

    /**
     * Checks to see if this type is water.
     *
     * @return boolean
     */
    public function isWater()
    {
        return ($this->strType == 'Water');
    }

    /**
     * Checks to see if this type is not water.
     *
     * @return boolean
     */
    public function notRocky()
    {
        return ($this->strType != 'Impassable Rocks');
    }

    /**
     * Checks to see if this type is water.
     *
     * @return boolean
     */
    public function isRocky()
    {
        return ($this->strType == 'Impassable Rocks');
    }

    /**
     * Gets the value of strTypeId.
     *
     * @return mixed
     */
    public function getStrTypeId()
    {
        return $this->strTypeId;
    }

    /**
     * Sets the value of strTypeId.
     *
     * @param mixed $strTypeId the str type id
     *
     * @return self
     */
    public function setStrTypeId($strTypeId)
    {
        $this->strTypeId = $strTypeId;

        return $this;
    }

    /**
     * Gets the value of strDescription.
     *
     * @return mixed
     */
    public function getStrDescription()
    {
        return $this->strDescription;
    }

    /**
     * Sets the value of strDescription.
     *
     * @param mixed $strDescription the str description
     *
     * @return self
     */
    public function setStrDescription($strDescription)
    {
        $this->strDescription = $strDescription;

        return $this;
    }

    /**
     * Returns this class as a divider.
     *
     * @return string
     */
    public function getString()
    {
        if ($this->objTileDisplayType) {
            return $this->objTileDisplayType->getString();
        } else {
            return '';
        }
    }

    /**
     * Returns this class as a table data.
     *
     * @return string
     */
    public function getTableData()
    {
        if ($this->objTileDisplayType) {
            return $this->objTileDisplayType->getTableData();
        } else {
            return '<td>error:<pre>'.print_r($this, true).'</pre></td>';
        }
    }

    /**
     * Gets the value of strtileDisplayType.
     *
     * @return mixed
     */
    public function getTileDisplayType()
    {
        return $this->strTileDisplayType;
    }

    /**
     * Sets the value of tileDisplayType.
     *
     * @param mixed $tileDisplayType the tile display type
     *
     * @return self
     */
    public function setTileDisplayType($strTileDisplayType)
    {
        $this->strTileDisplayType = $strTileDisplayType;

        $strClassName = array_pop(explode('-', $strTileDisplayType));
        $strClassName = 'Generator\view\Tile\TileTypes\\'.$strClassName;

        if (is_null($this->objTileDisplayType)) {
            $this->objTileDisplayType = new $strClassName();
            $this->objTileDisplayType->setTileDisplayType($this->strTileDisplayType);
        }

        return $this;
    }

    /**
     * Gets the value of strTileDisplayType.
     *
     * @return mixed
     */
    public function getStrTileDisplayType()
    {
        return $this->strTileDisplayType;
    }

    /**
     * Sets the value of strTileDisplayType.
     *
     * @param mixed $strTileDisplayType the str tile display type
     *
     * @return self
     */
    public function setStrTileDisplayType($strTileDisplayType)
    {
        $this->strTileDisplayType = $strTileDisplayType;

        return $this;
    }

    /**
     * Gets the value of objTileDisplayType.
     *
     * @return mixed
     */
    public function getObjTileDisplayType()
    {
        return $this->objTileDisplayType;
    }

    /**
     * Sets the value of objTileDisplayType.
     *
     * @param mixed $objTileDisplayType the obj tile display type
     *
     * @return self
     */
    public function setObjTileDisplayType($objTileDisplayType)
    {
        $this->objTileDisplayType = $objTileDisplayType;

        return $this;
    }

    /**
     * Saves this data to the database.
     *
     * @return $this
     */
    public function persist()
    {
        return $this;
    }

    /**
     * Gets the If the data of this instance was populated from a record in the database
     * This variable will be useful in debugging when I need to double check the db.
     *
     * @return integer
     */
    public function getIntPrimaryKey()
    {
        return $this->intPrimaryKey;
    }

    /**
     * Sets the If the data of this instance was populated from a record in the database
     * This variable will be useful in debugging when I need to double check the db.
     *
     * @param integer $intPrimaryKey the int primary key
     *
     * @return self
     */
    public function setIntPrimaryKey($intPrimaryKey)
    {
        $this->intPrimaryKey = $intPrimaryKey;

        return $this;
    }
}
