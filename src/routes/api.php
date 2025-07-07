<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\UserPreferenceController;
use App\Http\Controllers\Api\AggregatorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Public routes
Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index']);
    Route::get('/latest', [ArticleController::class, 'latest']);
    Route::get('/categories', [ArticleController::class, 'categories']);
    Route::get('/authors', [ArticleController::class, 'authors']);
    Route::get('/sources', [ArticleController::class, 'sources']);
    Route::get('/{article}', [ArticleController::class, 'show']);
});

Route::prefix('aggregator')->group(function () {
    Route::get('/status', [AggregatorController::class, 'status']);
    Route::post('/aggregate', [AggregatorController::class, 'aggregate']);
    Route::post('/aggregate/{sourceSlug}', [AggregatorController::class, 'aggregateFromSource']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('preferences')->group(function () {
        Route::get('/', [UserPreferenceController::class, 'show']);
        Route::put('/', [UserPreferenceController::class, 'update']);
        
        // Source preferences
        Route::post('/sources/add', [UserPreferenceController::class, 'addSelectedSource']);
        Route::delete('/sources/remove', [UserPreferenceController::class, 'removeSelectedSource']);
        
        // Category preferences
        Route::post('/categories/add', [UserPreferenceController::class, 'addSelectedCategory']);
        Route::delete('/categories/remove', [UserPreferenceController::class, 'removeSelectedCategory']);
        
        // Author preferences
        Route::post('/authors/add', [UserPreferenceController::class, 'addSelectedAuthor']);
        Route::delete('/authors/remove', [UserPreferenceController::class, 'removeSelectedAuthor']);
    });
}); 