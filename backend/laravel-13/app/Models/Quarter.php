<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Concerns\HasUuid;

class Quarter extends Model
{
    use HasFactory;
    use HasUuid;

    protected $guarded = [];

    protected $casts = [
        // convert dates to carbon instance
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function year(): BelongsTo
    {
        return $this->belongsTo(Year::class);
    }

    /**
     * Accessor: Get formatted date range for display.
     * 
     * Usage: $quarter->date_range (returns "Jan 1, 2025 - Mar 31, 2025")
     */
    public function getDateRangeAttribute(): string
    {
                // Add type hinting for Intelephense
        $start = $this->start_date instanceof Carbon ? $this->start_date : Carbon::parse($this->start_date);
        $end = $this->end_date instanceof Carbon ? $this->end_date : Carbon::parse($this->end_date);
        
        return $start->format('M j, Y') . ' - ' . $end->format('M j, Y');
    }
}
