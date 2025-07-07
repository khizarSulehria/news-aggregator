# News Services Architecture

This document explains the news service architecture and how to use the new command-based system for fetching articles from multiple news sources.

## Overview

The news aggregation system uses a service-oriented architecture with the following components:

- **NewsServiceInterface**: Defines the contract for all news service implementations
- **NewsServiceFactory**: Creates service instances based on news source configuration
- **Service Implementations**: Concrete implementations for each news source (NewsAPI, Guardian, etc.)
- **FetchNewsCommand**: Console command that uses the interface to fetch from all sources

## Architecture Benefits

1. **Scalability**: Easy to add new news sources without modifying existing code
2. **Maintainability**: Each service implementation is isolated and follows the same interface
3. **Testability**: Services can be easily mocked and tested independently
4. **Flexibility**: Can fetch from specific sources or all active sources

## Using the FetchNewsCommand

### Basic Usage

```bash
# Fetch from all active news sources
php artisan news:fetch

# Fetch from a specific source
php artisan news:fetch --source=newsapi

# Dry run (show what would be fetched without storing)
php artisan news:fetch --dry-run

# Verbose output (show detailed information)
php artisan news:fetch --verbose

# Combine options
php artisan news:fetch --source=guardian --dry-run --verbose
```

### Command Options

- `--source=`: Specify a particular news source slug to fetch from
- `--dry-run`: Show what would be fetched without actually storing articles
- `--verbose`: Display detailed output including article titles and error details

### Example Output

```
📰 Starting news aggregation...
🌐 Fetching from all active sources...
📡 Processing: NewsAPI.org (newsapi)
  📥 Fetching articles...
  ✅ Fetched 100 articles from NewsAPI.org
  💾 Storing articles...
  ✅ Stored articles in 2.34s
    - Created: 95
    - Updated: 5

📡 Processing: The Guardian (guardian)
  📥 Fetching articles...
  ✅ Fetched 50 articles from The Guardian
  💾 Storing articles...
  ✅ Stored articles in 1.87s
    - Created: 48
    - Updated: 2

📊 AGGREGATION SUMMARY
──────────────────────────────────────────────────
Sources processed: 2
Articles fetched: 150
Articles created: 143
Articles updated: 7
Errors encountered: 0
✅ News aggregation completed successfully!
```

## Adding New News Sources

To add a new news source, follow these steps:

### 1. Create the Service Implementation

Create a new class in `src/app/Services/impl/` that implements `NewsServiceInterface`:

```php
<?php

namespace App\Services\impl;

use App\Models\Article;
use App\Models\NewsSource;
use App\Services\NewsServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YourNewsService implements NewsServiceInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://api.yournews.com';
    private NewsSource $newsSource;

    public function __construct(NewsSource $newsSource)
    {
        $this->newsSource = $newsSource;
        $this->apiKey = $newsSource->api_key;
    }

    public function getNewsSource(): NewsSource
    {
        return $this->newsSource;
    }

    public function getArticlesWithFilters(array $filters): Builder
    {
        // Implementation for filtering articles
        $query = Article::with('newsSource')
            ->where('news_source_id', $this->newsSource->id);

        // Apply filters...
        return $query->orderBy('published_at', 'desc');
    }

    public function fetchArticlesFromService(): array
    {
        // Implementation for fetching from your API
        try {
            $response = Http::get($this->baseUrl . '/articles', [
                'api_key' => $this->apiKey,
                // Add your API parameters
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->transformArticles($data['articles'] ?? []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('YourNews API error', [
                'error' => $e->getMessage(),
                'source' => $this->newsSource->name
            ]);
            return [];
        }
    }

    public function storeArticles(array $articles): array
    {
        // Implementation for storing articles
        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($articles as $articleData) {
            try {
                // Check if article exists and update/create accordingly
                $existingArticle = Article::where('external_id', $articleData['external_id'])
                    ->where('news_source_id', $this->newsSource->id)
                    ->first();

                if ($existingArticle) {
                    $existingArticle->update($articleData);
                    $updated++;
                } else {
                    Article::create(array_merge($articleData, [
                        'news_source_id' => $this->newsSource->id,
                    ]));
                    $created++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'title' => $articleData['title'] ?? 'Unknown',
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'articles_fetched' => count($articles),
            'articles_created' => $created,
            'articles_updated' => $updated,
            'errors' => $errors,
            'source' => $this->newsSource->name
        ];
    }

    private function transformArticles(array $articles): array
    {
        // Transform your API response to standardized format
        return array_map(function ($article) {
            return [
                'external_id' => $article['id'] ?? null,
                'title' => $article['title'] ?? '',
                'description' => $article['description'] ?? '',
                'content' => $article['content'] ?? '',
                'url' => $article['url'] ?? '',
                'image_url' => $article['image_url'] ?? null,
                'author' => $article['author'] ?? null,
                'category' => $article['category'] ?? null,
                'tags' => $article['tags'] ?? [],
                'published_at' => $article['published_at'] ?? now(),
            ];
        }, $articles);
    }
}
```

### 2. Update the Factory

Add your service to the `NewsServiceFactory`:

```php
public static function create(NewsSource $newsSource): NewsServiceInterface
{
    return match ($newsSource->slug) {
        'newsapi' => new NewsApiService($newsSource),
        'guardian' => new GuardianService($newsSource),
        'yournews' => new YourNewsService($newsSource), // Add this line
        default => throw new InvalidArgumentException("No service implementation found for source: {$newsSource->slug}")
    };
}
```

### 3. Add the News Source to Database

Add a record to the `news_sources` table:

```sql
INSERT INTO news_sources (name, slug, api_url, api_key, config, is_active) 
VALUES ('Your News', 'yournews', 'https://api.yournews.com', 'your-api-key', '{}', true);
```

Or create a seeder entry:

```php
[
    'name' => 'Your News',
    'slug' => 'yournews',
    'api_url' => 'https://api.yournews.com',
    'api_key' => env('YOURNEWS_API_KEY'),
    'config' => [],
    'is_active' => true,
]
```

### 4. Test Your Implementation

```bash
# Test with dry run
php artisan news:fetch --source=yournews --dry-run --verbose

# Test actual fetching
php artisan news:fetch --source=yournews
```

## Service Interface Requirements

All service implementations must implement these methods:

- `getNewsSource()`: Return the associated NewsSource model
- `getArticlesWithFilters(array $filters)`: Return a query builder for filtered articles
- `fetchArticlesFromService()`: Fetch articles from the external API
- `storeArticles(array $articles)`: Store articles in the database

## Error Handling

The system includes comprehensive error handling:

- API failures are logged but don't stop the entire process
- Individual article storage errors are tracked and reported
- Services that fail to instantiate are skipped with warnings
- All errors are logged for debugging

## Scheduling

You can schedule the command to run automatically:

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    // Run every hour
    $schedule->command('news:fetch')->hourly();
    
    // Or run every 30 minutes
    $schedule->command('news:fetch')->everyThirtyMinutes();
}
```

## Monitoring

Monitor the aggregation process by:

1. Checking the command output for errors
2. Reviewing Laravel logs for detailed error information
3. Using the `--verbose` flag for detailed output
4. Checking the database for new articles

## Best Practices

1. **API Rate Limiting**: Implement appropriate delays between API calls
2. **Error Handling**: Always handle API failures gracefully
3. **Data Validation**: Validate API responses before processing
4. **Logging**: Log important events and errors for debugging
5. **Testing**: Test your service implementation thoroughly before deployment 