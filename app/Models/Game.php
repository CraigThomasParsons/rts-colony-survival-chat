<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Game
 *
 * Represents a single game instance, acting as the central hub for all
 * related entities like players, units, and the map.
 *
 * @property int $id
 * @property string|null $name The name of the game session.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Map|null $map The map associated with this game.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Unit> $units The units belonging to this game.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Player> $players The players participating in this game.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Colonist> $colonists The colonists within this game.
 */
class Game extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * Maps associated with the game (many-to-many).
     */
    public function maps(): BelongsToMany
    {
        return $this->belongsToMany(Map::class, 'game_map');
    }

    /**
     * Get all units belonging to the game.
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get all players in the game.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * Get all colonists in the game.
     */
    public function colonists(): HasMany
    {
        return $this->hasMany(Colonist::class);
    }
}
