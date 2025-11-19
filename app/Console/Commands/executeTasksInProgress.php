<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\Status as TaskStatus;

class executeTasksInProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:execute-tasks-in-progress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attempt at setting up a game loop';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inProgressTaskStatus = TaskStatus::firstWhere('name', TaskStatus::STARTED);

        // Grab all the tasks that are in progress.
        $inProgressTasks = $inProgressTaskStatus->tasks()->get();

        foreach ($inProgressTasks as $task) {
            $task->decrementWorkLeft();
            $task->save();
        }
    }
}