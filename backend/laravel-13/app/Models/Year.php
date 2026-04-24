<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Concerns\HasUuid;

class Year extends Model
{
    use HasFactory;
    use HasUuid;

    protected $guarded = [];
}
