<?php

namespace App\Services\impl;

use App\Models\Article;
use App\Models\NewsSource;
use App\Services\NewsServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class NYTimesService implements NewsServiceInterface
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
     * Fetch articles from the external news service
     *
     * @return array Array of fetched articles
     */
    public function fetchArticlesFromService(): array
    {
        try {
            $client = new Client();
            $apiKey = $this->apiKey;
        
            $response = $client->get($this->newsSource->api_url . '/search/v2/articlesearch.json', [
                'query' => [
                    'api-key' => $apiKey,
                    'q' => 'news', // Search query
                    'sort' => 'newest',
                    'fl' => 'headline,abstract,byline,web_url,multimedia,pub_date,section_name,subsection_name,des_facet,uri',
                ]
            ]);
        
            $data = json_decode($response->getBody(), true);

            // Check if the response is successful (status code 200-299)
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return $this->transformArticles($data['response']['docs'] ?? []);
            }

            Log::error('NYTimes API request failed', [
                'status' => $response->getStatusCode(),
                'body' => $response->getBody()->getContents(),
                'source' => $this->newsSource->name
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('NYTimes service error', [
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
                Log::error('Failed to store NYTimes article', [
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
                'external_id' => $article['uri'] ?? null,
                'title' => $article['headline']['main'] ?? '',
                'description' => $article['abstract'] ?? '',
                'content' => $article['abstract'] ?? '', // NYTimes API doesn't provide full content
                'url' => $article['web_url'] ?? '',
                'image_url' => $this->extractImageUrl($article),
                'author' => $this->extractAuthor($article),
                'category' => $this->extractCategory($article),
                'tags' => $this->extractTags($article),
                'published_at' => $article['pub_date'] ?? now(),
            ];
        }, $articles);
    }

    /**
     * Extract image URL from article data
     *
     * @param array $article Raw article data
     * @return string|null Image URL or null
     */
    private function extractImageUrl(array $article): ?string
    {
        // NYTimes API provides multimedia array with images
        if (!empty($article['multimedia'])) {
            $article['multimedia']['default']['url'];
        }
        
        return null;
    }

    /**
     * Extract author from article data
     *
     * @param array $article Raw article data
     * @return string|null Author or null
     */
    private function extractAuthor(array $article): ?string
    {
        if (!empty($article['byline']['original'])) {
            // Remove "By " prefix if present
            return preg_replace('/^By\s+/', '', $article['byline']['original']);
        }
        
        return null;
    }

    /**
     * Extract category from article data
     *
     * @param array $article Raw article data
     * @return string|null Category or null
     */
    private function extractCategory(array $article): ?string
    {
        return $article['section_name'] ?? null;
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
        
        if (!empty($article['section_name'])) {
            $tags[] = $article['section_name'];
        }

        if (!empty($article['subsection_name'])) {
            $tags[] = $article['subsection_name'];
        }

        // Add keywords if available
        if (!empty($article['des_facet'])) {
            $tags = array_merge($tags, array_slice($article['des_facet'], 0, 5)); // Limit to 5 tags
        }

        return array_unique($tags);
    }
} 