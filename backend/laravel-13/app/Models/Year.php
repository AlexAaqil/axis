<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\HasUuid;

class Year extends Model
{
    use HasFactory;
    use HasUuid;

    protected $guarded = [];

    public function quarters(): HasMany
    {
        return $this->hasMany(Quarter::class);
    }
}
