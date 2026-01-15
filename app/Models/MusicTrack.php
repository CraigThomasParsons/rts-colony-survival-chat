<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MusicTrack extends Model
{
    protected $fillable = [
        'artist',
        'album',
        'title',
        'file_path',
        'duration',
        'track_number',
    ];
}
