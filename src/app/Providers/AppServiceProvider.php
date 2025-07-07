<?php

namespace App\Providers;

use App\Services\NewsServiceFactory;
use App\Services\NewsServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(NewsServiceInterface::class, function ($app) {
            // Get the current news source from the request or use a default
            // This can be enhanced based on your routing or context
            $newsSource = \App\Models\NewsSource::where('slug', 'newsapi')->first();
            
            if (!$newsSource) {
                throw new \Exception('News source not found');
            }
            
            return NewsServiceFactory::create($newsSource);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
