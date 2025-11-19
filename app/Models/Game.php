<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class Game extends Model { protected $guarded=[]; public function map(){ return $this->hasOne(Map::class); } public function units(){ return $this->hasMany(Unit::class); } public function players(){ return $this->hasMany(Player::class); } public function colonists(){ return $this->hasMany(\App\Models\Colonist::class); } }
