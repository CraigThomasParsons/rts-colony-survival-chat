<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Default is todo, but also considered a backlog item as well.
    const DEFAULT_NAME = 'todo';

    // This item should show up in the current sprint.
    const READY = 'queued';

    // A worker has started work on this item.
    const STARTED = 'inprogress';

    // The worker has completed this item.
    const DONE = 'complete';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'title', 'slug', 'order'];

    public $timestamps = true;

    /**
     * Helper method to return styles for the text of task in a list.
     */
    public function getStyle()
    {

        $strStyle = '';

        if ($this->name == 'complete') {
            $strStyle = 'text-decoration: line-through';
        }

        return $strStyle;
    }

    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('order');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}