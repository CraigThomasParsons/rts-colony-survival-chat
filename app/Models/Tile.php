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
    protected $fillable = ['name', 'description', 'coordinateX', 'coordinateY', 'mapCoordinateX', 'mapCoordinateY', 'cell_id', 'map_id', 'tileType_id', 'has_trees'];

    public $timestamps = false;

    protected $keyType = 'string';

    public $incrementing = false;

    public function tileType()
    {
        return $this->belongsTo(TileType::class, 'tileType_id');
    }

    public function map() {
        return $this->belongsTo(Map::class);
    }
}