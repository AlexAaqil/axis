<?php

namespace App\Models\Tasks;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $guarded = [];

    protected $casts = [
        'priority' => TaskPriority::class,
        'status' => TaskStatus::class,
        'deadline' => 'date',
        'date_started' => 'date',
        'date_finished' => 'date',
    ];

    public function subTasks(): HasMany
    {
        return $this->hasMany(SubTask::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
