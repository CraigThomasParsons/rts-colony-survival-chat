<?php
namespace App\Helpers\MapGenerators;

use App\Helpers\Base\BaseMapGenerator;

/**
 * Will use Fault Line map generator method.
 * Generating 2d height map.
 */
class Anarchy extends BaseMapGenerator
{
    protected $strDropDownDisplayName = 'Fault line / Perlin Noise / Fibonacci';
}
