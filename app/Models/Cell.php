<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Cell extends Model
{
    use HasUuids;

    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'coordinateX', 'coordinateY', 'height', 'map_id', 'cellType_id'];

    protected $table = 'cell';

    public $timestamps = false;

    protected $keyType = 'string';

    public $incrementing = false;
}