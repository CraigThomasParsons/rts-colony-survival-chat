<?php
namespace App\Helpers\MapGenerators;

/**
 * FaultLine Map Generator
 * 
 * This class extends Anarchy to provide the complete terrain generation pipeline:
 * 1. Fault Line algorithm for large-scale terrain features
 * 2. Perlin noise for mid-scale variation
 * 3. Tree placement overlay
 * 4. Cell classification (water, mountain, passable, trees)
 * 
 * For a pure fault line heightmap generator without the full pipeline,
 * use FaultLineAlgorithm class directly.
 * 
 * @see FaultLineAlgorithm for the core fault line algorithm
 */
class FaultLine extends Anarchy
{
    /**
     * Called when an instance of FaultLine is instantiated.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->arrHistory = array();
        $this->arrHistory[] = 'Class FaultLine created at [' . (new \DateTime())->format('Y-m-d H:i:s') . ']' . "\n";
    }
}
