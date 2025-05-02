<?php

namespace App\Models\Tasks;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $guarded = [];

    protected $casts = [
        'priority' => TaskPriority::class,
        'status' => TaskStatus::class,
        'deadline' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
