<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'user_preferences';

    protected $fillable = [
        'user_id',
        'selected_sources',
        'selected_categories',
        'selected_authors',
        'excluded_sources',
        'excluded_categories',
    ];

    protected $casts = [
        'selected_sources' => 'array',
        'selected_categories' => 'array',
        'selected_authors' => 'array',
        'excluded_sources' => 'array',
        'excluded_categories' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 