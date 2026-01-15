<?php
namespace App\Helpers\MapDatabase;

use App\Models\Cell as TempCell;
use App\Models\CellType;
use App\Helpers\Coordinates;
use App\Helpers\MapDatabase\CellRepository;

/**
 * Sets up an initial Cell.
 * Cells are needed because it takes about 4 tiles to make a corner of anything.
 * The heightmap generator generally creates cell heights.
 * the individual tiles are then just peices of that puzzle.
 */
class Cell
{
    /**
     * array of the values I try to store in map model
     *
     * @var $data
     */
    protected $data;

    /**
     * array of errors
     *
     * @var $error
     */
    protected $error;

    /**
     * Initialize data to defaults
     */
    public function __construct()
    {
        $this->data['map_id'] = -1;
        $this->data['coordinateX'] = 0;
        $this->data['coordinateY'] = 0;
        $this->data['name'] = 'Initial Cell';
        $this->data['description'] = 'Initial Cell';
        $this->data['height'] = 0;
        $this->data['cellType_id'] = $this->getDefaultCellType();
    }

    /**
     * Return the current Y coordinate.
     *
     * @return integer
     */
    public function getY()
    {
        return $this->data['coordinateY'];
    }

    /**
     * Return the current X coordinate.
     *
     * @return integer
     */
    public function getX()
    {
        return $this->data['coordinateX'];
    }

    /**
     * Gets the value of Id.
     *
     * @return mixed
     */
    public function hasId()
    {
        return ((isset($this->data['id']) === true) && !empty($this->data['id']));
    }

    /**
     * Gets the value of Id.
     *
     * @return mixed
     */
    public function getId()
    {
        if (isset($this->data['id'])) {
            return $this->data['id'];
        } else {
            return false;
        }
    }

    /**
     * Sets the value of this cell's Id.
     *
     * @param mixed $id the cell Id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->data['id'] = $id;

        return $this;
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
     * Sets the value of the x Coordinate
     *
     * @param mixed $coordValue the Cells x coordinate
     *
     * @return self
     */
    public function setCoordinateX($coordValue)
    {
        $this->data['coordinateX'] = $coordValue;

        return $this;
    }

    /**
     * Sets the value of the y Coordinate
     *
     * @param mixed $coordValue the Cells y coordinate
     *
     * @return self
     */
    public function setCoordinateY($coordValue)
    {
        $this->data['coordinateY'] = $coordValue;

        return $this;
    }

    /**
     * Get the default celltype_id
     *
     * @return integer
     */
    protected function getDefaultCellType()
    {
        $cellType = CellType::firstWhere('name', CellType::BASIC_LAND);
        
        if (!$cellType) {
            // Fallback: try to get the first available CellType, or throw descriptive error
            $cellType = CellType::first();
            if (!$cellType) {
                throw new \RuntimeException(
                    'No CellType records found in database. Run: php artisan db:seed --class=CellTypeSeeder'
                );
            }
            \Log::warning("CellType '{CellType::BASIC_LAND}' not found, falling back to first CellType (id: {$cellType->id})");
        }
        
        return $cellType->id;
    }

    /**
     * Given an array, populate data in this Cell
     * example of what I'm expecting
     * [
     *   "id": 4
     *   "name": "Initial Cell"
     *   "description": "Initial Cell"
     *   "coordinateX": 0
     *   "coordinateY": 0
     *   "height": 0
     *   "map_id": 1
     *   "cellType_id": 1
     *  ]
     *
     * @param array $data associateive array of data
     * 
     * @return self
     */
    public function populateFromArray($arrData)
    {
        // Set field values based on values passed in by parameters.
        foreach ($arrData as $field => $value) {
            $this->set($field, $value);
        }

        return $this;
    }

    /**
     * Save this record.
     * return id.
     */
    public function save()
    {
        if ($this->hasId()) {
            $cellRecord = TempCell::find($this->getId());

        } else {
            $cellRecord = new TempCell();
        }

        $cellRecord->fill($this->data);

        $cellRecord->save();

        return $cellRecord->id;
    }

    /**
     * Trying to move static calls to Repository classes.
     * But this function is called in MapHelper.
     *
     * @return mixed
     */
    public static function findByCoordinates($mapId, $mapCoordinateX, $mapCoordinateY)
    {
        return CellRepository::findByCoordinates($mapId, $mapCoordinateX, $mapCoordinateY);
    }

    /**
     * Just adding tile locations based on these cell coordinates.
     */
    public function addTileLocations(&$arrTileLocations)
    {
        for ($firstOffset=0; $firstOffset < 2; $firstOffset++) {
            for ($secondOffset=0; $secondOffset < 2; $secondOffset++) {
                $x = ($this->getX() * 2) + $firstOffset;
                $y = ($this->getY() * 2) + $secondOffset;
                $arrTileLocations[$x][$y] = 1;
            }
        }
    }

    /**
     * If a member variable isn't found it will try to get it from data.
     */
    public function __get($name)
    {
        // If were looking for a possible getter function then remove underscores.
        // Don't worry about case because function calls are not case sensitive.
        $strFieldNameNoUnderscore = str_replace('_', '', $name);
        $strGetFunctionName       = 'get' . $strFieldNameNoUnderscore;
        
        // Use getters if they exist.
        if (method_exists($this, $strGetFunctionName)) {
            return $this->$strGetFunctionName($name);
            
            // Use the get function if a that field were looking for is in data.
        } else if (array_key_exists($name, $this->data)) {
            return $this->get($name);
        }
    }

    /**
     * Getter for anything
     * 
     * @param string $fieldName
     * 
     * @return mixed
     */
    public function get($fieldName)
    {
        return ($this->data[$fieldName]);
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

    /**
     * unsets $fieldName
     * 
     * @param string $fieldName
     * 
     * @return self
     */
    public function unsetField($fieldName)
    {
        unset($this->data[$fieldName]);

        return $this;
    }
}
