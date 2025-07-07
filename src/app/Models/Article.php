<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class Article extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'articles';

    protected $fillable = [
        'news_source_id',
        'external_id',
        'title',
        'description',
        'content',
        'url',
        'image_url',
        'author',
        'category',
        'tags',
        'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
    ];

    // Remove the newsSource() relationship method
    // Add a helper method to fetch the NewsSource from MySQL
    public function getNewsSource()
    {
        return \App\Models\NewsSource::find($this->news_source_id);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByAuthor($query, $author)
    {
        return $query->where('author', $author);
    }

    public function scopeBySource($query, $sourceId)
    {
        return $query->where('news_source_id', $sourceId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('published_at', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to apply various filters.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithFilters($query, array $filters)
    {
        // Filter by news source if provided
        if (!empty($filters['news_source_id'])) {
            $query->where('news_source_id', $filters['news_source_id']);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('content', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Apply category filter
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        // Apply author filter
        if (!empty($filters['author'])) {
            $query->where('author', 'like', '%' . $filters['author'] . '%');
        }

        // Apply date range filters
        if (!empty($filters['start_date'])) {
            $query->where('published_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('published_at', '<=', $filters['end_date']);
        }

        // Apply user preferences if provided
        if (!empty($filters['user_preferences'])) {
            $preferences = $filters['user_preferences'];
            if (!empty($preferences['excluded_categories'])) {
                $query->whereNotIn('category', $preferences['excluded_categories']);
            }
            if (!empty($preferences['excluded_authors'])) {
                $query->whereNotIn('author', $preferences['excluded_authors']);
            }
        }

        return $query->orderBy('published_at', 'desc');
    }
} 