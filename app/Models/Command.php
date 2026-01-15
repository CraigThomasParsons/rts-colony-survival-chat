<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class Command extends Model { protected $guarded=[]; protected $casts=['payload'=>'array']; }
