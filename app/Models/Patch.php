<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patch extends Model
{
    protected $fillable = ['instruction', 'diff', 'status', 'applied_at'];
}
