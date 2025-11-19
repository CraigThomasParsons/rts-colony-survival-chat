<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\Todo;
use App\Models\Status;

use Livewire\Component;

class TodoList extends Component
{
    /**
     * List of tasks given to the current user.
     *
     * @var todos
     */
    public $todos;

    public $task;

    function mount()
    {
        $this->fetchTodos();
    }

    /**
     * Grab the task records from the database and populate this todolist.
     */
    public function fetchTodos()
    {
        $this->todos = Task::all()->reverse();
    }

    /**
     * Insert a new task item from the table.
     */
    function addTodo()
    {
        if ($this->task != '') {
            $todo = new Task();
            $todo->description = $this->task;
            $todo->name = 'new todo';
            $todo->title = 'new backlog item';
            $todo->user_id = 1;
            $todo->workLeft = 100;
            $todo->setStatusToDefault();
            $todo->save();
            $this->task = '';
        }

        // Refresh the list.
        $this->fetchTodos();
    }

    /**
     * Change the status to done.
     *
     * @param Task $todo
     *
     * @return void
     */
    function markAsDone(Task $todo)
    {
        $todo->setStatusToComplete();
        $todo->save();

        $this->fetchTodos();
    }

    /**
     * Deletes the todo record from the database
     *
     * @param Task $todo
     *
     * @return void
     */
    function remove(Task $todo)
    {
        $todo->delete();
    }

    public function render()
    {
        return view('livewire.todo-list');
    }
}
