<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Task extends Model
{
    use HasFactory;

    const IN_PROGRESS = 3;

    protected $fillable = ['title', 'description', 'order', 'status_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * @param Status $status
     *
     * @return Task This task
     */
    public function setStatus(Status $status)
    {
        $this->status_id = $status->id;

        return $this;
    }

    /**
     * Set the status to completed
     *
     * @return Task This task
     */
    public function setStatusToComplete()
    {
        $this->setStatus(
            Status::firstWhere('name', Status::DONE)
        );

        return $this;
    }

    /**
     * Remove the amount of work left by the amount of workers working on it.
     *
     * @return void
     */
    public function decrementWorkLeft()
    {
        // Left here as a reminder that we will need to set this to the amount of workers linked to this task
        $numWorkers = 1;
        $this->workLeft = $this->workLeft - $numWorkers;

        if ($this->workLeft <= 0) {
            $this->workLeft = 0;
            $this->setStatusToComplete();
        }
    }

    /**
     * Set the status to the default status.
     *
     * @return void
     */
    public function setStatusToDefault()
    {
        $this->setStatus(
            Status::firstWhere('name', Status::DEFAULT_NAME)
        );
    }

    /**
     * Depending on what the status is set to
     * return a style setting for the div containing the todo item
     *
     * @return string
     */
    public function getTextStyle() {

        $strStyle = '';

        if ($this->status->name == 'complete') {
            $strStyle = 'text-decoration: line-through';
        }

        return $strStyle;
    }
}
