<?php

namespace App\Helpers;

use App\Models\Todo;
use App\Models\Task;

/**
 *
 */
class TodoFactory
{
    /**
     * Construct a new Task item and pass it into a new todo item.
     *
     * @param String $strTaskDescription
     * @return Todo
     */
    function initializeTodoListItem(String $strTaskDescription)
    {
        if ($strTaskDescription != '' && strlen($strTaskDescription) > 0) {
            $task = new Task();
            $task->name = 'new todo';
            $task->description = $this->task;
            $task->title = 'new backlog item';
            $task->user_id = 1;
            $task->workLeft = 100;
            $task->setStatusToDefault();
            $todo = new Todo($task);
        }

        return $todo;
    }
}