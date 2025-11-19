<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cell extends Model
{
    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'coordinateX', 'coordinateY', 'height', 'map_id', 'cellType_id'];

    protected $table = 'cell';

    public $timestamps = false;
}