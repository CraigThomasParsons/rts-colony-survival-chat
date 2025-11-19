<?php
namespace App\Services;
use App\Models\Game;
class JobScheduler { protected $workGivers=[]; public function __construct(array $workGivers=[]){$this->workGivers=$workGivers;} public function scan(Game $game): void { foreach($this->workGivers as $wg) $wg->provideTasks($game); } }
