<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use App\Concerns\HasUuid;
use App\Enums\Priority;
use App\Enums\Status;

class Objective extends Model
{
    use HasFactory;
    use HasUuid;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'date',
        'priority' => Priority::class,
        'status' => Status::class
    ];

    /**
     * Resolve route binding for both ID and UUID.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (is_numeric($value)) {
            return $this->where('id', $value)->firstOrFail();
        }
        return $this->where('uuid', $value)->firstOrFail();
    }

    public function quarter(): BelongsTo
    {
        return $this->belongsTo(Quarter::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            Status::DONE->value,
            Status::ARCHIVED->value,
        ]);
    }

    public function scopeByPriority($query, Priority $priority)
    {
        return $query->where('priority', $priority->value);
    }

    public function isOverdue(): bool
    {
        if (!$this->due_date instanceof Carbon) {
            return false;
        }

        return $this->due_date->isPast() && $this->status !== Status::DONE;
    }

    public function getPriorityLabelAttribute(): string
    {
        return $this->priority->label();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }
}
