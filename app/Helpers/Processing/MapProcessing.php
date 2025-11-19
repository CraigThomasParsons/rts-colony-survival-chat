<?php
namespace App\Helpers\Processing;

/**
 * Simple object I'm using to store all the details that are stored
 * in the mongodb database and save it in the mysql database as well.
 */
class MapProcessing
{
    /**
     * The Mongodb variable for Maps
     *
     * @var mongoMap
     */
    protected $mongoMap;

    /**
     * The Mysql variable for Maps
     *
     * @var mysql
     */
    protected $mysqlMap;

    /**
     * Save the state in each type of Map
     *   or mongo or mysql.
     *
     * @param string $strDescription
     *
     * @return void
     */
    protected function setState($strDescription) {
    
        // When I was just using mongo I simply just saved the state as a string.
        $mongoMap->setState($strDescription);

    }

    /**
     * Load the mongo database's map into mongoMap
     * Load the equivalent in mysql.
     *
     * @param integer $mapId
     */
    protected function loadMaps(int $mapId = 1) {

    }
}