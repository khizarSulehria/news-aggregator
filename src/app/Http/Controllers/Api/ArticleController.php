<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'author' => 'nullable|string|max:100',
            'source_id' => 'nullable|integer|exists:news_sources,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = Article::query();

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('author')) {
            $query->where('author', 'like', '%' . $request->author . '%');
        }

        if ($request->filled('source_id')) {
            $query->where('news_source_id', $request->source_id);
        }

        if ($request->filled('start_date')) {
            $query->where('published_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('published_at', '<=', $request->end_date);
        }

        // Apply user preferences if authenticated
        if ($request->user()) {
            $userPreferences = $request->user()->preferences;
            if ($userPreferences) {
                if (!empty($userPreferences->selected_sources)) {
                    $query->whereIn('news_source_id', $userPreferences->selected_sources);
                }
                
                if (!empty($userPreferences->excluded_sources)) {
                    $query->whereNotIn('news_source_id', $userPreferences->excluded_sources);
                }
                
                if (!empty($userPreferences->selected_categories)) {
                    $query->whereIn('category', $userPreferences->selected_categories);
                }
                
                if (!empty($userPreferences->excluded_categories)) {
                    $query->whereNotIn('category', $userPreferences->excluded_categories);
                }
                
                if (!empty($userPreferences->selected_authors)) {
                    $query->whereIn('author', $userPreferences->selected_authors);
                }
            }
        }

        $perPage = $request->get('per_page', 20);
        $articles = $query->orderBy('published_at', 'desc')->paginate($perPage);

        // Attach NewsSource manually
        $data = collect($articles->items())->map(function($article) {
            $articleArray = $article->toArray();
            $articleArray['news_source'] = $article->getNewsSource();
            return $articleArray;
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
                'from' => $articles->firstItem(),
                'to' => $articles->lastItem(),
            ]
        ]);
    }

    public function show(Article $article): JsonResponse
    {
        // Attach NewsSource manually
        $articleArray = $article->toArray();
        $articleArray['news_source'] = $article->getNewsSource();
        return response()->json([
            'data' => $articleArray
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Article::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return response()->json([
            'data' => $categories
        ]);
    }

    public function authors(): JsonResponse
    {
        $authors = Article::select('author')
            ->whereNotNull('author')
            ->distinct()
            ->pluck('author')
            ->filter()
            ->values();

        return response()->json([
            'data' => $authors
        ]);
    }

    public function sources(): JsonResponse
    {
        $sources = \App\Models\NewsSource::active()
            ->select('id', 'name', 'slug')
            ->get();

        return response()->json([
            'data' => $sources
        ]);
    }

    public function latest(): JsonResponse
    {
        $articles = Article::with('newsSource')
            ->orderBy('published_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $articles
        ]);
    }
} 