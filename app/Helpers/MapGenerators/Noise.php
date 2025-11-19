<?php
namespace App\Helpers\MapGenerators;

use App\Helpers\Base\BaseMapGenerator;

/**
 * Use Perlin noise to generate 2d height maps and tiles.
 */
class Noise extends BaseMapGenerator
{
    protected $strDropDownDisplayName = 'Perlin Noise';
}
