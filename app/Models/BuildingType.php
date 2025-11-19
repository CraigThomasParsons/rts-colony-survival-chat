<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingType extends Model
{

    protected $table = 'buildtypes';

    // IF no requirements then the requirement should be 1;
    const BUILDING_DEFAULT_REQUIREMENT = 1;
    
    const TOWN_HALL = 'Town Hall';

    const TOWER = 'Tower';

    const FARM = 'Farm';

    const BARRACKS = 'Barracks';

    const LUMBER_MILL = 'Lumber Mill';

    const BLACK_SMITH = 'Black Smith';

    const STABLES = 'Stables';

    const TEMPLE = 'Temple';
}
