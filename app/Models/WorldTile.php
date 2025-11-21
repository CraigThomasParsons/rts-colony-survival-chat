<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorldTile extends Model
{
    use HasFactory;

    protected $table = 'tiles';

    protected $fillable = [
        'map_id',
        'x',
        'y',
        'terrain',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
