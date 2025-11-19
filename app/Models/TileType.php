<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TileType extends Model
{
    /** @use HasFactory<\Database\Factories\TileTypeFactory> */
    use HasFactory;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $timestamps = true;

    protected $table = 'tileType';
}
