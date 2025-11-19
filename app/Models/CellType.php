<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CellType extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    // By default, everything should be basic passible land.
    const BASIC_LAND = 'Land';

    // Basic not passible land type.
    const MOUNTAIN = 'Mountain';

    // Flooded area, lake, sea or river.
    const WATER = 'Water';

    // Forrested area, not passible either.
    const TREE = 'Tree';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description'];

    public $timestamps = true;

    protected $table = 'cellType';
}