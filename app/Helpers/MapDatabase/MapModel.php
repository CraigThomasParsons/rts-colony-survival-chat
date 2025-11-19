<?php
namespace App\Helpers\MapDatabase;

use App\Models\MapStatus;
use App\Models\Map;

/**
 * This class should make it easier to save a record into a relational Map database.
 * The map database is going to be which ever database I decide to use because
 * I try different databases too often.
 * Originally this was created to help me save things into a Mongo Database.
 */
class MapModel
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
     * Map Status
     *
     * @var App\Models\MapStatus
     */
    protected $state;

    /**
     * constructor of Map Model.
     */
    public function __construct()
    {
        $this->data = array();
        $this->error = array();   
    }

    /**
     * save
     * Take all the data found in this instance and save it in the map table.
     *
     * @return int The id of the map record
     */
    public function save()
    {
        // Try to save the map record.
        if ((isset($this->data['id']) === true) && ($this->data['id'] > 0)) {
            $primaryKey = $this->data['id'];

            $map = Map::find($primaryKey);

        } else {
            // No map found, but we should have at least a unique name.
            $map = Map::firstOrNew([
                'name' => $this->data['name']
            ]);
        }
        $mapData = $this->data;
        unset($mapData['id']);
        $map->fill($mapData);

        $map->save();

        return $primaryKey;
    }

    /**
     * Given an array, populate data in this class.
     * 
     * @param array $post
     *
     * @return self
     */
    public function populateFromPost($post)
    {    
        // Set field values based on values in post.
        foreach ($post as $field => $value) {
            $this->set($field, $value);
        }

        return $this;
    }

    /**
     * Given an array, populate data in this class.
     * 
     * @param array $data
     * 
     * @return self
     */
    public function populateFromArray($data)
    {
        // Set field values based on values passed in by parameters.
        foreach ($data as $field => $value) {
            $this->set($field, $value);
        }

        return $this;
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
     * get All Data stored in this class.
     * 
     * @return array
     */
    public function getAllData()
    {
        return $this->data;
    }

    /**
     * Get values of the names of the field names passed in as array.
     * 
     * @param array $fields The names of the fields whose values you need.
     * 
     * @return array
     */
    public function getValues($fields)
    {
        $fieldValues = array();
        foreach ($fields as $field) {
            $fieldValues[] = $this->data[$field];
        }
        return $fieldValues;
    }

    /**
     * Gets the value of data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the value of data.
     *
     * @param mixed $data the data
     *
     * @return self
     */
    protected function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets the value of error.
     *
     * @param mixed $error the error
     *
     * @return self
     */
    protected function setError($error)
    {
        $this->error = $error;

        return $this;
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
     * Static method to insert a new row into the database using class str_class
     * and using the values in properties
     * eg:
     * <code>
     * Map::Insert( 'Car', array('make'=>'Citroen', 'model'=>'C4', 'colour'=>'Silver') );
     * </code>
     *
     * @static
     * @param  string  str_class  the name of the class/table
     * @param  array properties  array/hash of properties for object/row
     * @return boolean true or false depending upon whether insert is successful
     */
    public function insert($str_class, $properties)
    {
        //$object = Map::create($str_class, $properties);
        //return $object->save;
    }

    /**
     * Static method to update a row in the database using class str_class
     * and using the values in properties
     * eg:
     * <code>
     * Map::Update( 'Car', 1, array('make'=>'Citroen', 'model'=>'C4', 'colour'=>'Silver') );
     * </code>
     *
     * @static
     * @param  string  str_class  the name of the class/table
     * @param  int   id      the id of the row be updated.
     * @param  arrray  properties  array/hash of properties for object/row
     * @param  boolean true or false depending upon whether update is sucessful
     */
    public function update($str_class, $id, $properties)
    {
    	//todo
        //$object = Map::FindById($str_class, $id);
        //$object->populate($properties);
        //return $object->save();
    }

    /**
     * Sets all object properties via an array
     *
     * @param   array $arrVals  array of named values
     * @return  boolean true if $arrVals is valid array, false if not
     */
    public function populate($arrValues)
    {
        if (is_array($arrValues)) {
            foreach ($arrValues as $key => $val) {
                // This should work.
                // Since we do have __set making sure data will be populated.
                $this->$key = $val;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Deletes the object from the database
     * Very simple way of doing it.
     * If you want something more complex use Map::destroy
     * eg:
     * <code>
     * $car = Map::FindById('Car', 1);
     * $car->destroy();
     * </code>
     *
     * @return  boolean True on success, False on failure
     */
    public function delete()
    {
        dd($this->triggerError('delete is called but this function isn\'t implemented'));
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
     * Adds an error to the object. The existence of errors
     * ensures that $object->save() will return false and
     * will not attempt to persist the object to the database
     * This can be used for validation of the object.
     * e.g.
     * <code>
     * if( empty( $user->first_name ) ) $user->addError('first_name', 'First Name may not be blank!');
     * $user->save or print_r($user->getErrors)
     * </code>
     *
     * @param string strKey     The name of the invalid key/property/attribute
     * @param string strMessage a message, which you may want to report back to the user in due course
     * 
     * @return  void
     */
    public function addError($strKey, $strMessage)
    {
        if (!isset($this->error)) {
            $this->error = array();
        }
        $this->error[$strKey] = $strMessage;
    }

    /**
     * Gets an error on a specified attribute.
     *
     * @param string str_key Name of field/attribute/key
     * 
     * @return string Error Message. False if no error
     */
    public function getError($strKey)
    {
        if (isset($this->error[$strKey])) {
            return $this->error[$strKey];
        } else {
            return false;
        }
    }
    
    /**
     * Returns all errors.
     *
     * @return  array Array of errors, keyed by attribute.
     *          False if there are no errors.
     */
    public function getErrors()
    {
        if (isset($this->error) && is_array($this->error)) {
            return $this->error;
        } else {
            return false;
        }
    }

    /**
     * returns the status of this map.
     *
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sets the value of data.
     *
     * @param string $strName The name of the state.
     *
     * @return self
     */
    public function setState($strName = MapStatus::CREATED_EMPTY)
    {
        $mapStatus = MapStatus::firstWhere('name', $strName);

        if ($mapStatus !== null ) {
            $this->state = $mapStatus;
            $this->data['mapstatuses_id'] = $this->state->id;
        }

        return $this;
    }

}
