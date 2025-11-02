<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CachedResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'query',
        'properties_ids',
    ];

    protected $casts = [
        'properties_ids' => 'array',
    ];
}
