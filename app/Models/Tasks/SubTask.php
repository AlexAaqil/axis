<?php

namespace App\Models\Tasks;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;

class SubTask extends Model
{
    protected $guarded = [];

    protected $casts = [
        'priority' => TaskPriority::class,
        'status' => TaskStatus::class,
    ];

    public function task()
    {
        return $this->belongsTo(SubTask::class);
    }
}
