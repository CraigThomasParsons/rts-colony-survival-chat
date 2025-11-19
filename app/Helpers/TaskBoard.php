<?php
namespace App\Helpers;

use App\Models\Todo;

/**
 * My intention of this class was to move all the functions from the todolist
 * component to this helper class.
 */
class TaskBoard
{
    public $task = '';
    public $todos;

    public function fetchTodos() {
        $this->todos = Task::all()->reverse();
    }

    /**
     * Insert a new todo item from the table.
     *
     * @return void
     */
    function addTodo() {
        if ($this->task != '') {
            $todo = new Task();
            $todo->description = $this->task;
            $todo->save();

            // Clear the task text box.
            $this->task = '';

            // Refresh the list.
            $this->fetchTodos();
        }
    }

    /**
     * Change the status to done.
     *
     * @param Todo $todo
     *
     * @return void
     */
    function markAsDone(Todo $todo) {

        $todo->complete();
        $todo->status = 'done';
        $todo->save();

        $this->fetchTodos();
    }

    /**
     * Deletes the todo record from the database
     *
     * @param Todo $todo
     *
     * @return void
     */
    function remove(Todo $todo)
    {
        $todo->delete();
    }


    public function render()
    {
        return view('livewire.todo-list');
    }
}
