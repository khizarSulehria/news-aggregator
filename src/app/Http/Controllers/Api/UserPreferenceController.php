<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserPreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $preferences = $request->user()->preferences;
        
        return response()->json([
            'data' => $preferences ?? new UserPreference()
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'selected_sources' => 'nullable|array',
            'selected_sources.*' => 'integer|exists:news_sources,id',
            'selected_categories' => 'nullable|array',
            'selected_categories.*' => 'string|max:100',
            'selected_authors' => 'nullable|array',
            'selected_authors.*' => 'string|max:100',
            'excluded_sources' => 'nullable|array',
            'excluded_sources.*' => 'integer|exists:news_sources,id',
            'excluded_categories' => 'nullable|array',
            'excluded_categories.*' => 'string|max:100',
        ]);

        $preferences = UserPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->only([
                'selected_sources',
                'selected_categories',
                'selected_authors',
                'excluded_sources',
                'excluded_categories',
            ])
        );

        return response()->json([
            'message' => 'Preferences updated successfully',
            'data' => $preferences
        ]);
    }

    public function addSelectedSource(Request $request): JsonResponse
    {
        $request->validate([
            'source_id' => 'required|integer|exists:news_sources,id'
        ]);

        $preferences = UserPreference::firstOrCreate(['user_id' => $request->user()->id]);
        $selectedSources = $preferences->selected_sources ?? [];
        
        if (!in_array($request->source_id, $selectedSources)) {
            $selectedSources[] = $request->source_id;
            $preferences->update(['selected_sources' => $selectedSources]);
        }

        return response()->json([
            'message' => 'Source added to preferences',
            'data' => $preferences
        ]);
    }

    public function removeSelectedSource(Request $request): JsonResponse
    {
        $request->validate([
            'source_id' => 'required|integer|exists:news_sources,id'
        ]);

        $preferences = UserPreference::where('user_id', $request->user()->id)->first();
        
        if ($preferences) {
            $selectedSources = $preferences->selected_sources ?? [];
            $selectedSources = array_diff($selectedSources, [$request->source_id]);
            $preferences->update(['selected_sources' => array_values($selectedSources)]);
        }

        return response()->json([
            'message' => 'Source removed from preferences',
            'data' => $preferences ?? new UserPreference()
        ]);
    }

    public function addSelectedCategory(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'required|string|max:100'
        ]);

        $preferences = UserPreference::firstOrCreate(['user_id' => $request->user()->id]);
        $selectedCategories = $preferences->selected_categories ?? [];
        
        if (!in_array($request->category, $selectedCategories)) {
            $selectedCategories[] = $request->category;
            $preferences->update(['selected_categories' => $selectedCategories]);
        }

        return response()->json([
            'message' => 'Category added to preferences',
            'data' => $preferences
        ]);
    }

    public function removeSelectedCategory(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'required|string|max:100'
        ]);

        $preferences = UserPreference::where('user_id', $request->user()->id)->first();
        
        if ($preferences) {
            $selectedCategories = $preferences->selected_categories ?? [];
            $selectedCategories = array_diff($selectedCategories, [$request->category]);
            $preferences->update(['selected_categories' => array_values($selectedCategories)]);
        }

        return response()->json([
            'message' => 'Category removed from preferences',
            'data' => $preferences ?? new UserPreference()
        ]);
    }

    public function addSelectedAuthor(Request $request): JsonResponse
    {
        $request->validate([
            'author' => 'required|string|max:100'
        ]);

        $preferences = UserPreference::firstOrCreate(['user_id' => $request->user()->id]);
        $selectedAuthors = $preferences->selected_authors ?? [];
        
        if (!in_array($request->author, $selectedAuthors)) {
            $selectedAuthors[] = $request->author;
            $preferences->update(['selected_authors' => $selectedAuthors]);
        }

        return response()->json([
            'message' => 'Author added to preferences',
            'data' => $preferences
        ]);
    }

    public function removeSelectedAuthor(Request $request): JsonResponse
    {
        $request->validate([
            'author' => 'required|string|max:100'
        ]);

        $preferences = UserPreference::where('user_id', $request->user()->id)->first();
        
        if ($preferences) {
            $selectedAuthors = $preferences->selected_authors ?? [];
            $selectedAuthors = array_diff($selectedAuthors, [$request->author]);
            $preferences->update(['selected_authors' => array_values($selectedAuthors)]);
        }

        return response()->json([
            'message' => 'Author removed from preferences',
            'data' => $preferences ?? new UserPreference()
        ]);
    }
} 