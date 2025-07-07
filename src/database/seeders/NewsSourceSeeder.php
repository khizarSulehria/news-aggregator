<?php

namespace Database\Seeders;

use App\Models\NewsSource;
use Illuminate\Database\Seeder;

class NewsSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'name' => 'NewsAPI.org',
                'slug' => 'newsapi',
                'api_url' => 'https://newsapi.org/v2',
                'api_key' => env('NEWSAPI_API_KEY', 'your-newsapi-key-here'),
                'config' => [
                    'country' => 'us',
                    'language' => 'en',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'The Guardian',
                'slug' => 'guardian',
                'api_url' => 'https://content.guardianapis.com',
                'api_key' => env('GUARDIAN_API_KEY', 'your-guardian-key-here'),
                'config' => [
                    'section' => 'news',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'The New York Times',
                'slug' => 'nytimes',
                'api_url' => 'https://api.nytimes.com/svc',
                'api_key' => env('NYTIMES_API_KEY', 'your-nytimes-key-here'),
                'config' => [
                    'section' => 'home',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($sources as $source) {
            NewsSource::updateOrCreate(
                ['slug' => $source['slug']],
                $source
            );
        }
    }
} 