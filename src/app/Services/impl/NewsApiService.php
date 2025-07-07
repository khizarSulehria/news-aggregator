<?php

namespace App\Services\impl;

use App\Models\Article;
use App\Models\NewsSource;
use App\Services\NewsServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class NewsApiService implements NewsServiceInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://newsapi.org/v2/';
    private NewsSource $newsSource;

    public function __construct(NewsSource $newsSource)
    {
        $this->newsSource = $newsSource;
        $this->apiKey = $newsSource->api_key;
    }

    /**
     * Get the news source associated with this service
     *
     * @return NewsSource
     */
    public function getNewsSource(): NewsSource
    {
        return $this->newsSource;
    }

    /**
     * Fetch articles from the external news service
     *
     * @return array Array of fetched articles
     */
    public function fetchArticlesFromService(): array
    {
        try {
   
            $client = new Client(['base_uri' => $this->baseUrl]);
            $apiKey = $this->apiKey;
        
            $response = $client->get('top-headlines', [
                'query' => [
                    'country' => 'us',
                    'category' => 'technology',
                    'pageSize' => 5,
                    'apiKey' => $apiKey
                ]
            ]);
        
            $news = json_decode($response->getBody(), true);

            // Check if the response is successful (status code 200-299)
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return $this->transformArticles($news['articles'] ?? []);
            }

            Log::error('NewsAPI request failed', [
                'status' => $response->getStatusCode(),
                'body' => $response->getBody()->getContents(),
                'source' => $this->newsSource->name
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('NewsAPI service error', [
                'error' => $e->getMessage(),
                'source' => $this->newsSource->name
            ]);
            return [];
        }
    }

    /**
     * Store articles in the database
     *
     * @param array $articles Array of articles to store
     * @return array Statistics about the storage operation (created, updated, errors)
     */
    public function storeArticles(array $articles): array
    {
        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($articles as $articleData) {
            try {
                // Check if article already exists by external_id
                $existingArticle = Article::where('external_id', $articleData['external_id'])
                    ->where('news_source_id', $this->newsSource->id)
                    ->first();

                if ($existingArticle) {
                    // Update existing article
                    $existingArticle->update([
                        'title' => $articleData['title'],
                        'description' => $articleData['description'],
                        'content' => $articleData['content'],
                        'url' => $articleData['url'],
                        'image_url' => $articleData['image_url'],
                        'author' => $articleData['author'],
                        'category' => $articleData['category'],
                        'tags' => $articleData['tags'],
                        'published_at' => $articleData['published_at'],
                    ]);
                    $updated++;
                } else {
                    // Create new article
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
                Log::error('Failed to store article', [
                    'article' => $articleData['title'] ?? 'Unknown',
                    'error' => $e->getMessage(),
                    'source' => $this->newsSource->name
                ]);
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

    /**
     * Transform raw API response to standardized article format
     *
     * @param array $articles Raw articles from API
     * @return array Transformed articles
     */
    private function transformArticles(array $articles): array
    {
        return array_map(function ($article) {
            return [
                'external_id' => $article['url'] ?? null,
                'title' => $article['title'] ?? '',
                'description' => $article['description'] ?? '',
                'content' => $article['content'] ?? '',
                'url' => $article['url'] ?? '',
                'image_url' => $article['urlToImage'] ?? null,
                'author' => $article['author'] ?? null,
                'category' => $this->extractCategory($article),
                'tags' => $this->extractTags($article),
                'published_at' => $article['publishedAt'] ?? now(),
            ];
        }, $articles);
    }

    /**
     * Extract category from article data
     *
     * @param array $article Raw article data
     * @return string|null Category or null
     */
    private function extractCategory(array $article): ?string
    {
        // NewsAPI doesn't provide categories in the response
        // Could implement category detection based on keywords
        return null;
    }

    /**
     * Extract tags from article data
     *
     * @param array $article Raw article data
     * @return array Array of tags
     */
    private function extractTags(array $article): array
    {
        $tags = [];
        
        if (!empty($article['source']['name'])) {
            $tags[] = $article['source']['name'];
        }

        return $tags;
    }
} 