<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorldMap extends Model
{
    use HasFactory;

    protected $table = 'maps';

    protected $fillable = [
        'game_id',
        'name',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
