<?php
namespace App\Events;
use Illuminate\Broadcasting\InteractsWithSockets; use Illuminate\Broadcasting\PrivateChannel; use Illuminate\Broadcasting\ShouldBroadcastNow;
class GameTicked implements ShouldBroadcastNow { use InteractsWithSockets; public $gameId; public $diff; public function __construct($gameId, $diff){$this->gameId=$gameId;$this->diff=$diff;} public function broadcastOn(){ return new PrivateChannel('game.'.$this->gameId);} public function broadcastWith(){ return ['diff'=>$this->diff]; } }
