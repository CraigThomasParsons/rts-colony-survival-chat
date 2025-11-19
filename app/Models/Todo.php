<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Simple view model to help with the todolist.
 * Trying to move more meta functionality here instead in the database models.
 * A todo is simply a task on the backlog.
 */
class Todo
{
    protected $task;

    /**
     * Making a simple adapter for a task.
     *
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Set this todo item to status done.
     *
     * @return void
     */
    public function complete()
    {
        // To be implemented
    }

    /**
     * Factory method to set all the defaults for the new task.
     *
     * @return void
     */
    public function createTodoListItem()
    {
        // To be implemented by factory class TodoFactory
    }

    /**
     * Depending on what the status is set to
     * return a style setting for the div containing the todo item.
     *
     * @return string
     */
    public function getStyle()
    {
        $strStyle = '';

        if ($this->task->status->name == 'complete') {
            $strStyle = 'text-decoration: line-through';
        }

        return $strStyle;
    }
}
