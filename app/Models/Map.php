<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Map extends Model
{
    /** @use HasFactory<\Database\Factories\MapFactory> */
    use HasFactory;
    use HasUuids;

    protected $table = 'map';

    /**
     * UUID primary key
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'coordinateX',
        'coordinateY',
        'mapstatuses_id',
        'state',
        'next_step',
        'is_generating',
        'seed',
        'status',
        'validated_at',
        'started_at',
        'validation_errors',
    ];

    // Allow mass assignment of is_generating lock flag
    protected $casts = [
        'is_generating' => 'boolean',
        'validated_at' => 'datetime',
        'started_at' => 'datetime',
        'validation_errors' => 'array',
    ];

    /**
     * Games associated with this map (many-to-many).
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_map');
    }
}
