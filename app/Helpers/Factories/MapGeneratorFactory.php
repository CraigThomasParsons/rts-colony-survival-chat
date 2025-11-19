<?php
namespace App\Helpers\Factories;

/**
 * The name of this class MapGeneratorFactory is not accurate.
 * But for lack of a better name
 * The idea of this class was to use the files in a directory
 * to dynamically list different map generators to display in a dropdown menu.
 */
class MapGeneratorFactory
{
    protected $arrListGenerators = array();

    const MAP_GENERATORS_FOLDER = 'MapGenerators';

    const MAP_GENERATORS_LOCATION = '/../'.SELF::MAP_GENERATORS_FOLDER;

    /**
     * Runs load Generators.
     */
    public function __construct()
    {
        $this->loadGenerators();
    }

    /**
     * Reads all the files in the MapGenerator and initializes all of them.
     * I really only use one method and that is a Fault-line height generator.
     */
    public function loadGenerators()
    {
        if ($handle = opendir(__DIR__ . self::MAP_GENERATORS_LOCATION)) {

            /* This is the correct way to loop over the directory. */
            while (false !== ($strEntry = readdir($handle))) {
                if ($strEntry != '.' && $strEntry != '..') {
                    $className = basename($strEntry, '.php');

                    $strClassDeclaration = 'App\Helpers\\' . SELF::MAP_GENERATORS_FOLDER . '\\' . $className;
                    $this->arrListGenerators[$className] = new $strClassDeclaration;
                }
            }
            closedir($handle);
        }
    }

    /**
     * Gets the value of arrListGenerators.
     *
     * @return mixed
     */
    public function getArrListGenerators()
    {
        return $this->arrListGenerators;
    }

    /**
     * Get the Generator.
     *
     * @return mixed
     */
    public function getGenerator($strIndex)
    {
        return $this->arrListGenerators[$strIndex];
    }

    /**
     * Sets the value of arrListGenerators.
     *
     * @param mixed $arrListGenerators the arr list generators
     *
     * @return self
     */
    public function setArrListGenerators($arrListGenerators)
    {
        $this->arrListGenerators = $arrListGenerators;

        return $this;
    }

    /**
     * Check hasStrDropDownDisplayName function before adding to dropdown list.
     *
     * @return array
     */
    public function getSelectArray()
    {
        // Initialize the array were returning
        $arrReturn = array();

        foreach ($this->arrListGenerators as $key => $currGen) {
            if ($currGen->hasStrDropDownDisplayName()) {
                // Array keys will be the class name.
                $arrReturn[$key] = $currGen->getStrDropDownDisplayName();
            }
        }

        return $arrReturn;
    }
}
