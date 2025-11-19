<?php
namespace App\Helpers;

/**
 * This is a port of Ken Perlin's "Improved Noise"
 *     http://mrl.nyu.edu/~perlin/noise/
 *
 * Originally from http://therandomuniverse.blogspot.com/2007/01/perlin-noise-your-new-best-friend.html
 * but the site appears to be down, so here is a mirror of it
 */
class NoiseMaker
{
    protected $arrGrid;
    protected $arrPermutation;
    protected $seed;
    protected $DefaultSize = 64;
    
    public function __construct($seed = null)
    {
        // Initialize the Grid array.
        $this->arrGrid = array();

        // Default permutation array.
        $this->arrPermutation = array( 151,160,137,91,90,15,
        131,13,201,95,96,53,194,233,7,225,140,36,103,30,69,142,8,99,37,240,21,10,23,
        190, 6,148,247,120,234,75,0,26,197,62,94,252,219,203,117,35,11,32,57,177,33,
        88,237,149,56,87,174,20,125,136,171,168, 68,175,74,165,71,134,139,48,27,166,
        77,146,158,231,83,111,229,122,60,211,133,230,220,105,92,41,55,46,245,40,244,
        102,143,54, 65,25,63,161, 1,216,80,73,209,76,132,187,208, 89,18,169,200,196,
        135,130,116,188,159,86,164,100,109,198,173,186, 3,64,52,217,226,250,124,123,
        5,202,38,147,118,126,255,82,85,212,207,206,59,227,47,16,58,17,182,189,28,42,
        223,183,170,213,119,248,152, 2,44,154,163, 70,221,153,101,155,167, 43,172,9,
        129,22,39,253, 19,98,108,110,79,113,224,232,178,185, 112,104,218,246,97,228,
        251,34,242,193,238,210,144,12,191,179,162,241, 81,51,145,235,249,14,239,107,
        49,192,214, 31,181,199,106,157,184, 84,204,176,115,121,50,45,127, 4,150,254,
        138,236,205,93,222,114,67,29,24,72,243,141,128,195,78,66,215,61,156,180,212
        );

        //Populate it
        for ($i = 0; $i < 256; $i++) {
            $this->arrGrid[256 + $i] = $this->arrGrid[$i] = $this->arrPermutation[$i];
        }

        if (is_string($seed)) {
            $intSeed = '';
            foreach (str_split($seed) as $value) {
                $intSeed .= ord($value);
            }
            $seed = (double) ('0.' . $intSeed);
            $seed = $seed * 1.912345616324118;
        }

        //And set the seed
        if ($seed === null) {
            $this->seed = time();
        } else {
            $this->seed = $seed;
        }
    }

    public function noise($x, $y, $z, $size = null)
    {
        if ($size == null) {
            $size = $this->DefaultSize;
        }

        //Set the initial value and initial size
        $value = 0.0;
        $initialSize = $size;

        //Add finer and finer hues of smoothed noise together
        while ($size >= 1) {

            $value += $this->smoothNoise($x / $size, $y / $size, $z / $size) * $size;
            $size /= 2.0;

        }

        //Return the result over the initial size
        return $value / $initialSize;
    }

    //This function determines what cube the point passed resides in
    //and determines its value.
    public function smoothNoise($x, $y, $z)
    {
        //Offset each coordinate by the seed value
        $x += $this->seed;
        $y += $this->seed;
        $z += $this->seed;

        $orig_x = $x;
        $orig_y = $y;
        $orig_z = $z;

        $X1 = (int) floor($x) & 255; // FIND UNIT CUBE THAT
        $Y1 = (int) floor($y) & 255; // CONTAINS POINT.
        $Z1 = (int) floor($z) & 255;

        $x -= floor($x); // FIND RELATIVE X,Y,Z
        $y -= floor($y); // OF POINT IN CUBE.
        $z -= floor($z);

        $u = $this->fade($x); // COMPUTE FADE CURVES
        $v = $this->fade($y); // FOR EACH OF X,Y,Z.
        $w = $this->fade($z);

        if (!isset($this->arrGrid[$X1])) {
            $this->arrGrid[$X1] = 0;
        }

        $A = $this->arrGrid[$X1] + $Y1;
        if (!isset($this->arrGrid[$A])) {
            $this->arrGrid[$A] = 0;
        }

        $AA = $this->arrGrid[$A] + $Z1;

        if (!isset($this->arrGrid[$A + 1])) {
            $this->arrGrid[$A + 1] = 0;
        }

        $AB = $this->arrGrid[$A + 1] + $Z1; // HASH COORDINATES OF

        if (!isset($this->arrGrid[$X1 + 1])) {
            $this->arrGrid[$X1 + 1] = 0;
        }

        $B = $this->arrGrid[$X1 + 1] + $Y1;

        if (!isset($this->arrGrid[$B])) {
            $this->arrGrid[$B] = 0;
        }

        $BA = $this->arrGrid[$B] + $Z1;

        if (!isset($this->arrGrid[$B + 1])) {
            $this->arrGrid[$B + 1] = 0;
        }

        $BB = $this->arrGrid[$B + 1] + $Z1; // THE 8 CUBE CORNERS,


        // Making sure not to get undefined offsets.
        if (!isset($this->arrGrid[$AA])) {
            $this->arrGrid[$AA] = 0;
        }

        if (!isset($this->arrGrid[$BA])) {
            $this->arrGrid[$BA] = 0;
        }

        if (!isset($this->arrGrid[$AB])) {
            $this->arrGrid[$AB] = 0;
        }

        if (!isset($this->arrGrid[$BB])) {
            $this->arrGrid[$BB] = 0;
        }


        if (!isset($this->arrGrid[$BB + 1])) {
            $this->arrGrid[$BB + 1] = 0;
        }

        if (!isset($this->arrGrid[$AA + 1])) {
            $this->arrGrid[$AA + 1] = 0;
        }

        if (!isset($this->arrGrid[$BA + 1])) {
            $this->arrGrid[$BA + 1] = 0;
        }

        if (!isset($this->arrGrid[$AB + 1])) {
            $this->arrGrid[$AB + +1] = 0;
        }

        //Interpolate between the 8 points determined and add blended results from 8 corners of a cube.
        $result = $this->lerp($w, 
            $this->lerp($v, 
                $this->lerp($u, 
                    $this->grad($this->arrGrid[$AA], $x, $y, $z),
                    $this->grad($this->arrGrid[$BA], $x - 1, $y, $z)),
                $this->lerp($u, $this->grad($this->arrGrid[$AB], $x, $y - 1, $z),
                    $this->grad($this->arrGrid[$BB], $x - 1, $y - 1, $z))),
            $this->lerp($v,
                $this->lerp($u,
                    $this->grad($this->arrGrid[$AA + 1], $x, $y, $z - 1),
                    $this->grad($this->arrGrid[$BA + 1], $x - 1, $y, $z - 1)),
                $this->lerp($u,
                    $this->grad($this->arrGrid[$AB + 1], $x, $y - 1, $z - 1),
                    $this->grad($this->arrGrid[$BB + 1], $x - 1, $y - 1, $z - 1)
                    )
                )
            );

        return $result;
    }

    public function fade($t)
    {
        return $t * $t * $t * (($t * (($t * 6) - 15)) + 10);
    }

    public function lerp($t, $a, $b)
    {
        //Make a weighted interpolaton between points
        return $a + $t * ($b - $a);
    }

    public function grad($hash, $x, $y, $z)
    {
        $h = $hash & 15; // CONVERT LO 4 BITS OF HASH CODE
        $u = $h < 8 ? $x : $y; // INTO 12 GRADIENT DIRECTIONS.
        $v = $h < 4 ? $y : ($h == 12 || $h == 14 ? $x : $z);
        
        return (($h & 1) == 0 ? $u : -$u) + (($h & 2) == 0 ? $v : -$v);
    }

    //This function I've added. It creates one dimension of noise.
    public function random1D($x)
    {
        $size = $this->DefaultSize;

        $x += $this->seed;

        $value = 0.0;
        $initialSize = $size = 3;

        while ($size >= 1) {
            $value += $this->smoothNoise($x * 3 / $size, 100 / $size, 100 / $size);
            $size--;
        }

        if ($value < -1) {
            $value = -1;
        }
        if ($value > 1) {
            $value = 1;
        }

        return $value;
    }

    /**
     * Same as random1D() only for 2 dimensions.
     *
     * @param integer $x
     * @param integer $y
     *
     * @return double.
     */
    public function random2D($x, $y)
    {
        if ($size === null) {
            $size = $this->DefaultSize;
        }

        $x += $this->seed;
        $y += $this->seed;

        $value = 0.0;
        $initialSize = $size = 3;

        while ($size >= 1) {
            $value += $this->smoothNoise($x * 3 / $size, $y * 3 / $size, 100 / $size);
            $size--;
        }

        if ($value < -1) {
            $value = -1;
        }
        if ($value > 1) {
            $value = 1;
        }

        return $value;
    }

    /**
     * Gets the value of arrGrid.
     *
     * @return mixed
     */
    public function getArrGrid()
    {
        return $this->arrGrid;
    }

    /**
     * Sets the value of arrGrid.
     *
     * @param mixed $arrGrid the arr grid
     *
     * @return self
     */
    public function setArrGrid($arrGrid)
    {
        $this->arrGrid = $arrGrid;

        return $this;
    }

    /**
     * Gets the value of permutation.
     *
     * @return mixed
     */
    public function getPermutation()
    {
        return $this->arrPermutation;
    }

    /**
     * Sets the value of permutation.
     *
     * @param mixed $permutation the permutation
     *
     * @return self
     */
    public function setPermutation($permutation)
    {
        $this->arrPermutation = $permutation;

        return $this;
    }

    /**
     * Gets the value of seed.
     *
     * @return mixed
     */
    public function getSeed()
    {
        return $this->seed;
    }

    /**
     * Sets the value of seed.
     *
     * @param mixed $seed the seed
     *
     * @return self
     */
    public function setSeed($seed)
    {
        $this->seed = $seed;

        return $this;
    }

    /**
     * Gets the value of DefaultSize.
     *
     * @return mixed
     */
    public function getDefaultSize()
    {
        return $this->DefaultSize;
    }

    /**
     * Sets the value of DefaultSize.
     *
     * @param mixed $DefaultSize the default size
     *
     * @return self
     */
    public function setDefaultSize($DefaultSize)
    {
        $this->DefaultSize = $DefaultSize;

        return $this;
    }
}
