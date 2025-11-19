<?php
namespace App\Helpers\MapDatabase;

/**
 * Represents 1 single square Map of a Rts game's entire world.
 * This should be moved to helpers, Create a Map class in the model folder
 * to used exclusivly for loading and saving the Map record.
 */
class Map extends MapModel
{
    /**
     * Should Empty all Cells for this map.
     * Initialize the basic data of 
     */
    public function __construct()
    {
        parent::__construct();

        // Initial values.
        $this->data['id'] = 1;
        $this->data['coordinateX'] = 0;
        $this->data['coordinateY'] = 0;
        $this->data['name'] = 'Initial Map';
        $this->data['description'] = 'Initial Map Description';
        $this->data['state'] = 'Cell Processing started';
    }

    /**
     * I'm not sure if this is going to be needed anymore.
     */
    // public function emptyTiles()
    // {
    // }

    /**
     * Should Empty all Cells for this map.
     * I'm not sure if this is going to be needed anymore.
     */
    // public function emptyCells()
    // {
    // }

    /**
     * Gets the value of Id.
     * I was getting the string id back when using this function.
     * Therefore I'm going to use intval to fix anything coming out of getId.
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
     * Return the Map database id.
     *
     * @return MapId This is a MapId Object
     */
    public function getMapId()
    {
        if (isset($this->data['_id'])) {
            return $this->data['_id'];
        }

        return false;
    }

    /**
     * Save this record.
     */
    public function save()
    {
        if ($this->getMapId() == false) {
            $mapRecord = new TempMap();
        } else {
            $mapRecord = TempMap::find($this->getMapId());
        }

        foreach ($this->data as $key => $value) {
            $mapRecord->$key = $value;
        }

        $status = $mapRecord->save();

        if ($status) {
            foreach ($mapRecord as $key => $value) {
                $this->data[$key] = $value;
            }
            return $this->id;
        }

        return $status;
    }
}
