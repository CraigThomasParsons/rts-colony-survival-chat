<?php
namespace App\Services;

use App\Models\Player;
use App\Models\Unit;
use App\Models\ResourceNode;
use App\Models\Command;
use App\Models\Building;

/**
 * BotManager - DI-friendly, testable bot orchestrator.
 * Implements farming cycles and multi-worker coordination by creating Commands.
 */
class BotManager
{
    protected $patrolChance = 20;
    protected $attackRange = 2.0;

    public function __construct($patrolChance = 20, $attackRange = 2.0)
    {
        $this->patrolChance = $patrolChance;
        $this->attackRange = $attackRange;
    }

    public function tick($game)
    {
        $bots = Player::where('game_id', $game->id)->where('is_bot', true)->get();
        foreach ($bots as $bot) {
            $units = Unit::where('game_id', $game->id)->where('player_id', $bot->id)->get();
            foreach ($units as $unit) $this->decideForUnit($unit, $bot, $game);
        }
    }

    protected function decideForUnit($unit, $bot, $game)
    {
        $type = $unit->type ?? 'worker';
        if ($type === 'worker') $this->workerBehavior($unit, $bot, $game); else $this->combatBehavior($unit,$bot,$game);
    }

    protected function workerBehavior($unit, $bot, $game)
    {
        $state = $unit->state ?? [];
        $carrying = $state['carrying'] ?? 0;
        if ($carrying > 0) { $this->issueReturnCommand($unit,$bot,$game); return; }

        // find nearest resource node with amount
        $node = ResourceNode::where('map_id',$game->map->id)->where('amount','>',0)->orderByRaw('POW(x - ?,2)+POW(y - ?,2)', [$unit->x,$unit->y])->first();
        if ($node) {
            Command::create(['game_id'=>$game->id,'player_id'=>$bot->id,'command_type'=>'harvest','payload'=>json_encode(['unit_id'=>$unit->id,'resource_node_id'=>$node->id])]);
            return;
        }

        // otherwise small patrol chance
        if (rand(1,100) <= $this->patrolChance) {
            $tx = max(0, min((int)round($unit->x)+rand(-4,4),1000)); $ty = max(0, min((int)round($unit->y)+rand(-4,4),1000));
            Command::create(['game_id'=>$game->id,'player_id'=>$bot->id,'command_type'=>'move','payload'=>json_encode(['unit_id'=>$unit->id,'x'=>$tx,'y'=>$ty])]);
        }
    }

    protected function combatBehavior($unit,$bot,$game)
    {
        $closest = null; $closestDist = PHP_INT_MAX;
        $enemies = Unit::where('game_id',$game->id)->where('player_id','!=',$bot->id)->get();
        foreach ($enemies as $e) {
            $d = sqrt(pow($e->x-$unit->x,2)+pow($e->y-$unit->y,2));
            if ($d < $closestDist) { $closestDist = $d; $closest = $e; }
        }
        if ($closest && $closestDist <= $this->attackRange) {
            Command::create(['game_id'=>$game->id,'player_id'=>$bot->id,'command_type'=>'attack','payload'=>json_encode(['unit_id'=>$unit->id,'target_unit'=>$closest->id])]);
        }
    }

    protected function issueReturnCommand($unit,$bot,$game)
    {
        $drop = Building::where('player_id',$bot->id)->where('type','town_hall')->orderByRaw('ABS(x - ?) + ABS(y - ?)', [$unit->x,$unit->y])->first();
        if ($drop) { $tx=$drop->x; $ty=$drop->y; } else { $tx=10;$ty=10; }
        Command::create(['game_id'=>$game->id,'player_id'=>$bot->id,'command_type'=>'move','payload'=>json_encode(['unit_id'=>$unit->id,'x'=>$tx,'y'=>$ty])]);
    }
}
