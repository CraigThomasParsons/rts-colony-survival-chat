<?php
namespace App\Helpers\ModelHelpers;

use App\Helpers\MongoDatabase\Cell as CellRecord;
use App\Helpers\MongoDatabase\CellType as CellType;
use App\Helpers\MongoDatabase\CellRepository;

/**
 * Represents 1 single square of a Map.
 */
class Cell
{
    public $arrTmpTiles;

    protected $intXaxisCoordinate;
    protected $intYaxisCoordinate;
    protected $intHeight;
    protected $strHexHeight;
    protected $strType;
    protected $strTypeId;
    protected $strDescription;
    protected $strToStringFunctionName;
    protected $mapPrimaryKey;

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
        $this->setAllTileTypes($strType);

        return $this;
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
     * [printHTML description]
     *
     * @return [type] [description]
     */
    public function printHTML()
    {
        return $this->getStrDescription();
    }

    /**
     * Gets the value of strToStringFunctionName.
     *
     * @return mixed
     */
    public function getStrToStringFunctionName()
    {
        return $this->strToStringFunctionName;
    }

    /**
     * Sets the value of strToStringFunctionName.
     *
     * @param mixed $strToStringFunctionName the str to string function name
     *
     * @return self
     */
    public function setStrToStringFunctionName($strToStringFunctionName)
    {
        $this->strToStringFunctionName = $strToStringFunctionName;

        return $this;
    }

    /**
     * Gets the value of strHexHeight.
     *
     * @return mixed
     */
    public function getStrHexHeight()
    {
        return $this->strHexHeight;
    }

    /**
     * Sets the value of strHexHeight.
     *
     * @param mixed $strHexHeight the str hex height
     *
     * @return self
     */
    public function setStrHexHeight($strHexHeight)
    {
        $this->strHexHeight = $strHexHeight;

        return $this;
    }

    /**
     * Gets the value of arrTmpTiles.
     *
     * @return mixed
     */
    public function getArrTmpTiles()
    {
        return $this->arrTmpTiles;
    }

    /**
     * Sets the value of arrTmpTiles.
     *
     * @param mixed &$arrTmpTiles the arr tmp tiles
     *
     * @return self
     */
    public function setArrTmpTiles(&$arrTmpTiles)
    {
        $this->arrTmpTiles = $arrTmpTiles;

        return $this;
    }

    public function setAllTileTypes($strType)
    {
        foreach ($this->arrTmpTiles as $intXaxisCoordinate => $row) {
            foreach ($row as $intYaxisCoordinate => $tile) {
                $this->arrTmpTiles[$intXaxisCoordinate][$intYaxisCoordinate]->setStrType($strType);
            }
        }
    }

    /**
     * Gets the value of arrTmpTiles at x and y coordinates.
     *
     * @param integer $intXaxisCoordinate
     * @param integer $intYaxisCoordinate
     *
     * @return tile
     */
    public function getArrTmpTile($intXaxisCoordinate, $intYaxisCoordinate)
    {
        return $this->arrTmpTiles[$intXaxisCoordinate][$intYaxisCoordinate];
    }

    /**
     * [getTopLeftTile description]
     *
     * @return [type] [description]
     */
    public function getTopLeftTile()
    {
        return $this->getArrTmpTile(0, 1);
    }

    /**
     * [getTopRightTile description]
     *
     * @return [type] [description]
     */
    public function getTopRightTile()
    {
        return $this->getArrTmpTile(1, 1);
    }

    /**
     * [getBottomRightTile description]
     *
     * @return [type] [description]
     */
    public function getBottomRightTile()
    {
        return $this->getArrTmpTile(1, 0);
    }

    /**
     * [getBottomLeftTile description]
     *
     * @return [type] [description]
     */
    public function getBottomLeftTile()
    {
        return $this->getArrTmpTile(0, 0);
    }

    /**
     * Returns the 4 tiles in div form.
     *
     * @return string
     */
    public function getTileDivStrings()
    {
        $strReturnValue = '';

        $strReturnValue .= $this->getTopLeftTile()->getString();
        $strReturnValue .= $this->getTopRightTile()->getString();
        $strReturnValue .= $this->getBottomLeftTile()->getString();
        $strReturnValue .= $this->getBottomRightTile()->getString();

        return $strReturnValue;
    }

    /**
     * Gets the value of mapPrimaryKey.
     *
     * @return mixed
     */
    public function getMapPrimaryKey()
    {
        return $this->mapPrimaryKey;
    }

    /**
     * Sets the value of mapPrimaryKey.
     *
     * @param mixed $mapPrimaryKey the map primary key
     *
     * @return self
     */
    public function setMapPrimaryKey($mapPrimaryKey)
    {
        $this->mapPrimaryKey = $mapPrimaryKey;

        return $this;
    }

    /**
     * Given the string type settings this function returns
     * a database table id.
     *
     * @return integer
     */
    public function strTypeToCellTypeId()
    {
        if ($this->getStrType() == 'Trees') {
            return CellType::TREE_ID;

        } elseif ($this->getStrType() == 'Passable Land') {
            return CellType::LAND_ID;

        } elseif ($this->getStrType() == 'Impassable Rocks') {
            return CellType::MOUNTAIN_ID;

        } elseif ($this->getStrType() == 'Water') {
            return CellType::WATER_ID;
        }
    }

    /**
     * Check for a cell record in the database,
     * if it doesn't exist return a new one.
     *
     * @param integer $mapId       The map record's primary key
     * @param integer $coordinateX The x-axis co-ordinate
     * @param integer $coordinateY The y-axis co-ordinate
     *
     * @return CellRecord
     */
    protected function cellRecord($mapId, $coordinateX, $coordinateY)
    {
        return CellRepository::findByCoordinates($mapId, $coordinateX, $coordinateY);
    }

    /**
     * Populates a Cell Record with this instance's data
     *
     * @param CellRecord $cellRecord
     *
     * @return CellRecord
     */
    public function populateCellRecordWithThisData($cellRecord)
    {
    $cellRecord->map_id      = $this->getMapPrimaryKey();
        $cellRecord->coordinateX = $this->getIntXaxisCoordinate();
        $cellRecord->coordinateY = $this->getIntYaxisCoordinate();
        $cellRecord->height      = $this->getIntHeight();
        $cellRecord->name        = $this->getStrHexHeight() . $this->getStrType();
        $cellRecord->description = $this->getStrDescription();
        $cellRecord->cellType_id = $this->strTypeToCellTypeId();

        return $cellRecord;
    }

    /**
     * Saves this data to the database.
     *
     * @return $this
     */
    public function persist()
    {
        $cellRecord = $this->populateCellRecordWithThisData($this->cellRecord(
            $this->getMapPrimaryKey(),
            $this->getIntXaxisCoordinate(),
            $this->getIntYaxisCoordinate()
        ));

        $cellRecord->save();

        return $this;
    }

    /**
     * Returns a string representations of this class.
     * Todo: Eventually I plan to use inheritance for this kind of solution.
     * Todo: Erase this function because view related behaviour shouldn't be in models.
     * 
     * @return string
     */
    public function getString()
    {
        $strReturnValue = '';
        $strHexNum      = dechex($this->getIntHeight());
        $strWidth       = '';

        if ($this->getStrType() == 'Water') {
            $colKey = $this->getintYaxisCoordinate();
            $rowKey = $this->getintXaxisCoordinate();

            // Not really necessary but I'm displaying cool little ascii art that look like waves.
            $strReturnValue = '<td title="X' . $this->getIntXaxisCoordinate() . ' Y' . $this->getIntYaxisCoordinate() . '" style="' . $strWidth . 'background-color:#0C0B' . $strHexNum . ';color:white">';

            $strReturnValue .= $this->getTileDivStrings();

            $strReturnValue .= '</td>';

        } elseif ($this->getStrType() == 'Trees') {
            $strReturnValue = '<td title="X' . $this->getIntXaxisCoordinate() . ' Y' . $this->getIntYaxisCoordinate() . '" style="' . $strWidth . 'background-color:#00' . $strHexNum . '00;">
            <div class="TreeCell">&nbsp;</div></td>';

        } elseif ($this->getStrType() == 'Passable Land') {
            $strReturnValue = '<td title="X' . $this->getIntXaxisCoordinate() . ' Y' . $this->getIntYaxisCoordinate() . '" class="LandCell" style="' . $strWidth . 'background-color:#7c' . $strHexNum . '00;">

            <div class="LandCell">&nbsp;</div></td>';

        } elseif ($this->getStrType() == 'Impassable Rocks') {

            $strReturnValue = '<td title="X' . $this->getIntXaxisCoordinate() . ' Y' . $this->getIntYaxisCoordinate() . ' Z' . $this->getIntHeight() . '" style="' . $strWidth . 'background-color:#' . $strHexNum . $strHexNum . $strHexNum . ';">';

            $strReturnValue .= '<div class="RockCell">'.$this->getTileDivStrings().'</div>';

            $strReturnValue .= '</td>';

        } else {
            // Assuming cell was set as type "Passable Land" if nothing else is matched.
            $strReturnValue = '<td title="X' . $this->getIntXaxisCoordinate() . ' Y' . $this->getIntYaxisCoordinate() . ' Z' . $this->getIntHeight() . '" class="LandCell" style="' . $strWidth . 'background-color:#EEEE' . $strHexNum . ';">&nbsp;</td>';
        }

        return $strReturnValue;
    }
}
