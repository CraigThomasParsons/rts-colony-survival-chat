<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Settlement model - represents placed settlements (villages, ruins)
 */
class Settlement extends Model
{
    protected $fillable = ['game_id','name','type','x','y','meta'];
    protected $casts = ['meta' => 'array'];
}
