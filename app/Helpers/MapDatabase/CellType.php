<?php
namespace App\Helpers\MapDatabase;

class CellType extends MapModel
{
    const LAND_ID     = 1;
    const WATER_ID    = 3;
    const MOUNTAIN_ID = 2;
    const TREE_ID     = 4;

    const CELL_TYPE_TABLE_NAME  = 'cellType';
}
