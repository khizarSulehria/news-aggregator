<?php

namespace App\Services;

use App\Models\NewsSource;
use Illuminate\Database\Eloquent\Builder;

interface NewsServiceInterface
{
    /**
     * Get the news source associated with this service
     *
     * @return NewsSource
     */
    public function getNewsSource(): NewsSource;

    /**
     * Fetch articles from the external news service
     *
     * @return array Array of fetched articles
     */
    public function fetchArticlesFromService(): array;

    /**
     * Store articles in the database
     *
     * @param array $articles Array of articles to store
     * @return array Statistics about the storage operation (created, updated, errors)
     */
    public function storeArticles(array $articles): array;
} 