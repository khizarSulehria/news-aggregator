<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AggregatorController extends Controller
{
    public function aggregate(): JsonResponse
    {
        return response()->json([
            'message' => 'News aggregation service has been removed',
            'data' => []
        ], 501);
    }

    public function aggregateFromSource(string $sourceSlug): JsonResponse
    {
        return response()->json([
            'message' => 'News aggregation service has been removed',
            'data' => []
        ], 501);
    }

    public function status(): JsonResponse
    {
        $stats = [
            'total_articles' => \App\Models\Article::count(),
            'total_sources' => \App\Models\NewsSource::active()->count(),
            'latest_articles' => collect(\App\Models\Article::orderBy('published_at', 'desc')
                ->limit(5)
                ->get(['id', 'title', 'published_at', 'news_source_id']))
                ->map(function($article) {
                    $articleArray = $article->toArray();
                    $articleArray['news_source'] = $article->getNewsSource();
                    return $articleArray;
                }),
            'sources_status' => \App\Models\NewsSource::active()
                ->withCount('articles')
                ->get(['id', 'name', 'slug', 'is_active'])
        ];

        return response()->json([
            'data' => $stats
        ]);
    }
} 