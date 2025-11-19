<?php
namespace App\Helpers\MapDatabase;

use App\Models\Tile as TempTile;
use App\Helpers\ModelHelpers\Tile as TileHelper;
use App\Helpers\MapDatabase\MapModel;

/**
 * This class was initially created to use a mongodb.
 * I've decided to reuse it as a helper class.
 */
class Tile extends MapModel
{
    /**
     * array of the values I try to store in map model
     *
     * @var $data
     */
    protected $data;

    /**
     * An array of error messages
     *
     * @var $error
     */
    protected $error;

    /**
     * Map Status
     *
     * @var App\Models\MapStatus
     */
    protected $state;

    /**
     * @var App\Helpers\ModelHelpers\Tile
     */
    protected $tileHelper;

    /**
     * Any empty string value 
     * is just a indicator that the field exists.
     * 
     */
    public function __construct()
    {
        Parent::__construct();
    
        $this->data['map_id'] = '';
        $this->data['coordinateX'] = 0;
        $this->data['coordinateY'] = 0;
        $this->data['mapCoordinateX'] = 0;
        $this->data['mapCoordinateY'] = 0;
        $this->data['cellId'] = '';
        $this->data['tileTypeId'] = 1;
        $this->data['name'] = 'Initial tile';
        $this->data['description'] = 'Initial tile';
        $this->data['height'] = 0;
    }

    /**
     * Set String Type
     *
     * @return self
     */
    public function setStrType($name)
    {
        $this->data['name'] = $name;

        return $this;
    }

    /**
     * set Tile Display Type
     */
    public function setTileDisplayType($tileDisplayType)
    {
        $this->data['tileDisplayType'] = $tileDisplayType;
        $this->name = $tileDisplayType;
    }

    /**
     * Return the parent cell's Y axis coordinates.
     *
     * @return integer
     */
    public function getCellY()
    {
        return ($this->mapCoordinateY - $this->coordinateY) / 2;
    }

    /**
     * Return the parent cell's X axis coordinates.
     *
     * @return integer
     */
    public function getCellX()
    {
        return ($this->mapCoordinateX - $this->coordinateX) / 2;
    }

    /**
     * Returns the value of Id.
     *
     * @return mixed
     */
    public function getId()
    {
        if (isset($this->data['id'])) {
            return $this->data['id'];
        }

        return false;
    }

    /**
     * Gets the value of Id.
     *
     * @return mixed
     */
    public function hasId()
    {
        return ((isset($this->data['id']) === true) && ($this->data['id']> 0));
    }

    /**
     * Sets the value of the map Id.
     *
     * @param mixed $mapId the map id
     *
     * @return self
     */
    public function setMapId($mapId)
    {
        $this->data['map_id'] = $mapId;

        return $this;
    }

    /**
     * Sets the value of the cell Id.
     *
     * @param int $cellId the cell id
     * 
     * @return App\Helpers\MapDatabase\Tile
     */
    public function setCellId(int $cellId): \App\Helpers\MapDatabase\Tile
    {
        $this->data['cell_id'] = $cellId;

        return $this;
    }


    /**
     * Sets the value of the cell Id.
     * 
     * @return bool
     */
    public function hasCellId(): bool
    {
        return isset($this->data['cell_id']);
    }


    /**
     * Sets the value of the x MapCoordinate
     *
     * @param mixed $coordValue the Tiles x coordinate of the whole
     *
     * @return self
     */
    public function setMapCoordinateX($coordValue)
    {
        $this->data['mapCoordinateX'] = $coordValue;

        return $this;
    }

    /**
     * Sets the value of the y Coordinate
     *
     * @param mixed $coordValue the Tiles y coordinate of the whole
     *
     * @return self
     */
    public function setMapCoordinateY($coordValue)
    {
        $this->data['mapCoordinateY'] = $coordValue;

        return $this;
    }

    /**
     * Return the Map database id.
     *
     * @return MapId This is a MapId Object
     */
    public function getMapId()
    {
        if (isset($this->data['id'])) {
            return $this->data['id'];
        }

        return false;
    }

    /**
     * Save this record.
     */
    public function save()
    {
        if ($this->getMapId() == false) {
            $tileRecord = new TempTile();
        } else {
            $tileRecord = TempTile::find($this->getId());
        }

        $tileRecord->fill($this->data);

        if ($this->hasCellId() === false) {
            echo '<pre>';
            print_r($this);
            echo '</pre>';
        }
        $status = $tileRecord->save();

        if ($status) {
            foreach ($tileRecord as $key => $value) {
                $this->data[$key] = $value;
            }

            return $this->getId();
        }

        return $status;
    }

    /**
     * Gets the value of tileHelper.
     *
     * @return mixed
     */
    public function getHelper()
    {
        return $this->tileHelper;
    }

    /**
     * Sets the value of tileHelper.
     *
     * @param mixed The tile helper
     *
     * @return self
     */
    public function setHelper($tileHelper)
    {
        $this->tileHelper = $tileHelper;

        return $this;
    }

    /**
     * Returns the StrType from the helper object.
     */
    public function getStrType()
    {
        if($this->tileHelper) {
            return $this->tileHelper->getStrType();
        }

        $this->triggerError("Member variable tileHelper wasn't defined");
    }

    /**
     * Just returns the Type Id from the tile Helper
     */
    public function getStrTypeId()
    {
        if($this->tileHelper) {
            return $this->tileHelper->getStrTypeId();
        }

        $this->triggerError("Member variable tileHelper wasn't defined");
    }

    /**
     * getCellIntYaxisCoordinate
     *
     * @param integer $intYaxisCoordinate
     *
     * @return integer
     */
    public function getCellIntYaxisCoordinate($intYaxisCoordinate)
    {
        if($this->tileHelper) {
            return $this->tileHelper->getCellIntYaxisCoordinate($intYaxisCoordinate);
        }

        $this->triggerError("Member variable tileHelper wasn't defined");

        return -1;
    }

    /**
     * getCellIntXaxisCoordinate
     *
     * @param integer $intXaxisCoordinate
     *
     * @return integer
     */
    public function getCellIntXaxisCoordinate($intXaxisCoordinate)
    {
        if($this->tileHelper) {
            return $this->tileHelper->getCellIntXaxisCoordinate($intXaxisCoordinate);
        }

        $this->triggerError("Member variable tileHelper wasn't defined");

        return -1;
    }

    /**
     * getIntXaxisCoordinate
     *
     * @return integer
     */
    public function getIntXaxisCoordinate()
    {
        if($this->tileHelper) {
            return $this->tileHelper->getIntXaxisCoordinate();
        }

        $this->triggerError("Member variable tileHelper wasn't defined");
        
        return -1;
    }

    /**
     * getIntYaxisCoordinate
     *
     * @return integer
     */
    public function getIntYaxisCoordinate()
    {
        if($this->tileHelper) {
            return $this->tileHelper->getIntYaxisCoordinate();
        }

        $this->triggerError("Member variable tileHelper wasn't defined");

        return -1;
    }

    /**
     * Checks to see if this type is not water.
     *
     * @return boolean
     */
    public function notWater()
    {
        if($this->tileHelper) {
            return ($this->getStrType() != 'inner-WaterTile');
        }

        return $this->triggerError("Member variable tileHelper wasn't defined");
    }

    /**
     * Checks to see if this type is water.
     *
     * @return boolean
     */
    public function isWater()
    {
        if($this->tileHelper) {
            return ($this->getStrType() == 'inner-WaterTile');
        }

        return $this->triggerError("Member variable tileHelper wasn't defined");
    }

    /**
     * Checks to see if this type is not water.
     *
     * @return boolean
     */
    public function notRocky()
    {
        if ($this->tileTypeId != 2) {
            return (($this->tileTypeId > 15) || ($this->tileTypeId < 4));
        }

        return false;        
    }

    /**
     * Checks to see if this type is water.
     *
     * @return boolean
     */
    public function isRocky()
    {
        return ((($this->tileTypeId > 3) && ($this->tileTypeId < 16)) || ($this->tileTypeId == 2));
    }


    /**
     * Given an array, populate data in this Tile
     * example of what I'm expecting
     * [
     *   "id": 4
     *   "name": "Initial Tile"
     *   "description": "Initial Tile"
     *   "coordinateX": 0
     *   "coordinateY": 0
     *   "map_id": 1
     *   "tileType_id": 1
     *  ]
     *
     * @param array $data associateive array of data
     * 
     * @return App\Helpers\MapDatabase\Tile
     */
    public function populateFromStdClass($stdClassData): \App\Helpers\MapDatabase\Tile
    {
        // Set field values based on values passed in by parameters.
        foreach ($stdClassData as $field => $value) {
            $this->set($field, $value);
        }

        return $this;
    }
    
    /**
     * Trying to move static calls to Repository classes.
     * but this function is going to be an exception.
     *
     * @return App\Helpers\MapDatabase\Tile
     */
    public static function findByMapCoordinates($mapId, $mapCoordinateX, $mapCoordinateY): \App\Helpers\MapDatabase\Tile
    {
        return TileRepository::findByMapCoordinates($mapId, $mapCoordinateX, $mapCoordinateY);
    }

    /**
     * Add the error to stack of error messages to look at later.
     * I think at some point I'll have a function that will actually
     * use an eloquent way to display the error messages with laravel reverb.
     *
     * @param string $errorMessage
     *
     * @return void
     */
    protected function triggerError($errorMessage)
    {
        $this->error[] = $errorMessage;

        return $errorMessage;
    }


    /**
     * Magical
     * If a member variable isn't found then use data array.
     */
    public function __set($variableName, $value)
    {
        if (array_key_exists($variableName, $this->data)) {
            
            // Function set exists in parent class db_record.
            return $this->set($variableName, $value);
        } else {
            
            return $this->$variableName = $value;
        }
    }

    /**
     * Function used to change protected variable data.
     * 
     * @param mixed $indexName The index of the member variable data that were setting.
     * @param mixed $value     The value were changing it to.
     * 
     * @return self
     */
    public function set($indexName, $value)
    {
        $this->data[$indexName] = $value;

        return $this;
    }
}