<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Map extends Model
{
    /** @use HasFactory<\Database\Factories\MapFactory> */
    use HasFactory;

    protected $table = 'map';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'coordinateX', 'coordinateY', 'mapstatuses_id'];

    /**
     * Games associated with this map (many-to-many).
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_map');
    }
}
