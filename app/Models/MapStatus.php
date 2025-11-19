<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapStatus extends Model
{
    const CREATED_EMPTY = 'Map_Created_Initialized_Empty';

    const CELL_PROCESSING_STARTED = 'Cell_Process_Started';

    const CELL_PROCESSING_FINNISHED = 'Cell_Process_Completed';

    const TILE_PROCESSING_STARTED = 'Tile_Process_Started';

    const TILE_PROCESSING_STOPPED = 'Tile_Process_Completed';

    const TREE_FIRST_STEP = 'Tree_Process_Started';

    const TREE_2ND_COMPLETED = 'Tree_Second_Step';

    const TREE_3RD_STARTED = 'Tree_Three_Step';

    const TREE_GEN_COMPLETED = 'Tree_Process_Completed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description'];

    protected $connection = 'mysql';
}
