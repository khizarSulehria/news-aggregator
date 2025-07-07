<?php

namespace App\Services\impl;

use App\Models\Article;
use App\Models\NewsSource;
use App\Services\NewsServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class GuardianService implements NewsServiceInterface
{
    private string $apiKey;
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
                Log::error('Failed to store Guardian article', [
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
                'external_id' => $article['id'] ?? null,
                'title' => $article['webTitle'] ?? '',
                'description' => $article['fields']['trailText'] ?? '',
                'content' => $article['fields']['bodyText'] ?? '',
                'url' => $article['webUrl'] ?? '',
                'image_url' => $article['fields']['thumbnail'] ?? null,
                'author' => $article['fields']['byline'] ?? null,
                'category' => $this->extractCategory($article),
                'tags' => $this->extractTags($article),
                'published_at' => $article['webPublicationDate'] ?? now(),
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
        return $article['sectionName'] ?? null;
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
        
        if (!empty($article['sectionName'])) {
            $tags[] = $article['sectionName'];
        }

        if (!empty($article['pillarName'])) {
            $tags[] = $article['pillarName'];
        }

        return $tags;
    }
} 