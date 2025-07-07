<?php

namespace App\Services;

use App\Models\NewsSource;
use App\Services\impl\NewsApiService;
use App\Services\impl\GuardianService;
use App\Services\impl\NYTimesService;
use InvalidArgumentException;

class NewsServiceFactory
{
    /**
     * Create a news service instance based on the news source
     *
     * @param NewsSource $newsSource
     * @return NewsServiceInterface
     * @throws InvalidArgumentException
     */
    public static function create(NewsSource $newsSource): NewsServiceInterface
    {
        return match ($newsSource->slug) {
            'newsapi' => new NewsApiService($newsSource),
            'guardian' => new GuardianService($newsSource),
            'nytimes' => new NYTimesService($newsSource),
            // Add more service implementations here as they are created
            // 'nytimes' => new NYTimesService($newsSource),
            default => throw new InvalidArgumentException("No service implementation found for source: {$newsSource->slug}")
        };
    }

    /**
     * Get all available service implementations for active news sources
     *
     * @return array Array of service instances
     */
    public static function createAllActive(): array
    {
        $activeSources = NewsSource::active()->get();
        $services = [];

        foreach ($activeSources as $source) {
            try {
                $services[] = self::create($source);
            } catch (InvalidArgumentException $e) {
                // Log the error but continue with other sources
                \Illuminate\Support\Facades\Log::warning("Failed to create service for source: {$source->slug}", [
                    'error' => $e->getMessage(),
                    'source' => $source->toArray()
                ]);
            }
        }

        return $services;
    }
} 