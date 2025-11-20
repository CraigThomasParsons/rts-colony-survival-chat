<?php
namespace App\Services;
use App\Models\Game;
/**
 * JobScheduler
 *
 * Orchestrates the creation of tasks by WorkGivers.
 */
class JobScheduler {
    /** @var array $workGivers An array of objects that can provide tasks. */
    protected array $workGivers=[];

    /**
     * JobScheduler constructor.
     *
     * @param array $workGivers
     */
    public function __construct(array $workGivers=[]){
        $this->workGivers=$workGivers;
    }

    /**
     * Scan the game state and provide tasks for each work giver.
     *
     * @param Game $game
     * @return void
     */
    public function scan(Game $game): void { foreach($this->workGivers as $wg) $wg->provideTasks($game); } }
}
// AI Notes: This service schedules jobs for the colony.
