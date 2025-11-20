<?php
namespace App\Services;

use App\Models\Game;
use App\Models\Task;
use App\Models\Colonist;

/**
 * ColonistManager
 *
 * Assigns tasks to colonists based on proximity and priority.
 */
class ColonistManager {
    /** @var Pathfinder|null */
    protected ?Pathfinder $pathfinder;

    /**
     * @param Pathfinder|null $pf
     */
    public function __construct(Pathfinder $pf = null)
    {
        $this->pathfinder = $pf;
    }

    /**
     * Assign pending tasks to idle colonists.
     *
     * @param Game $game
     * @return void
     */
    public function assign(Game $game): void
    {
        $tasks = Task::where('game_id', $game->id)->where('status', 'pending')->orderByDesc('priority')->get();
        $colonists = Colonist::whereIn('player_id', $game->players()->pluck('id'))->get();

        foreach ($tasks as $task) {
            if ($task->assigned_colonist_id) {
                continue;
            }

            $bestColonist = null;
            $bestDistance = PHP_INT_MAX;

            foreach ($colonists as $colonist) {
                $colonistState = $colonist->state ?? [];
                if (($colonistState['action'] ?? null) !== 'idle') {
                    continue;
                }
                $colonistX = $colonistState['x'] ?? 0;
                $colonistY = $colonistState['y'] ?? 0;
                $taskPayload = $task->payload ?? [];
                $taskX = $taskPayload['x'] ?? ($taskPayload['tx'] ?? 0);
                $taskY = $taskPayload['y'] ?? ($taskPayload['ty'] ?? 0);
                $distance = sqrt(pow($taskX - $colonistX, 2) + pow($taskY - $colonistY, 2));
                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestColonist = $colonist;
                }
            }

            if ($bestColonist) {
                $task->assigned_colonist_id = $bestColonist->id;
                $task->status = 'assigned';
                $task->save();
                $state = $bestColonist->state ?? [];
                $state['action'] = 'moving_to_task';
                $state['target_task_id'] = $task->id;
                $bestColonist->state = $state;
                $bestColonist->save();
            }
        }
    }
}
// AI Notes:
// - This service assigns tasks to colonists.
// - It selects the closest idle colonist for each task.
