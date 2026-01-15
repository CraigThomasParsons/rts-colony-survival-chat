<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BandcampLibraryUrl extends Model
{
    protected $fillable = [
        'url',
        'status',
        'last_synced_at',
        'error_message',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];
}
