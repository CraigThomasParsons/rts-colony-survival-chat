<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Tile extends Model
{
    use HasUuids;

    protected $connection = 'mysql';

    protected $table = 'tile';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'coordinateX', 'coordinateY', 'mapCoordinateX', 'mapCoordinateY', 'cell_id', 'map_id', 'tileType_id'];

    public $timestamps = false;

    protected $keyType = 'string';

    public $incrementing = false;
}