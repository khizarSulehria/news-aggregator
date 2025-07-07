<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\NewsSource;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = NewsSource::all();
        
        if ($sources->isEmpty()) {
            $this->command->warn('No news sources found. Please run NewsSourceSeeder first.');
            return;
        }

        $articles = [
            [
                'news_source_id' => $sources->where('slug', 'newsapi')->first()->id ?? 1,
                'external_id' => 'newsapi_001',
                'title' => 'Breaking: Major Technology Breakthrough in AI Research',
                'description' => 'Scientists have announced a revolutionary breakthrough in artificial intelligence that could transform how we interact with technology.',
                'content' => 'A team of researchers from leading universities has developed a new AI model that demonstrates unprecedented capabilities in natural language processing and reasoning. The breakthrough, published in Nature today, shows significant improvements in understanding context and generating human-like responses.',
                'url' => 'https://example.com/ai-breakthrough',
                'image_url' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800',
                'author' => 'Dr. Sarah Johnson',
                'category' => 'Technology',
                'tags' => ['AI', 'Research', 'Technology', 'Breakthrough'],
                'published_at' => now()->subHours(2),
            ],
            [
                'news_source_id' => $sources->where('slug', 'guardian')->first()->id ?? 2,
                'external_id' => 'guardian_001',
                'title' => 'Climate Change: New Report Shows Accelerating Global Warming',
                'description' => 'Latest climate data reveals that global temperatures are rising faster than previously predicted, according to a comprehensive new study.',
                'content' => 'The Intergovernmental Panel on Climate Change (IPCC) has released its most comprehensive report yet, showing that global temperatures have increased by 1.1°C above pre-industrial levels. The report warns that without immediate action, we could reach the critical 1.5°C threshold within the next decade.',
                'url' => 'https://example.com/climate-report',
                'image_url' => 'https://images.unsplash.com/photo-1569163139394-de4e1c3123a9?w=800',
                'author' => 'Michael Chen',
                'category' => 'Environment',
                'tags' => ['Climate Change', 'Global Warming', 'Environment', 'IPCC'],
                'published_at' => now()->subHours(4),
            ],
            [
                'news_source_id' => $sources->where('slug', 'nytimes')->first()->id ?? 3,
                'external_id' => 'nytimes_001',
                'title' => 'Economy: Federal Reserve Announces New Interest Rate Policy',
                'description' => 'The Federal Reserve has announced a new approach to managing interest rates in response to changing economic conditions.',
                'content' => 'Federal Reserve Chair Jerome Powell announced today that the central bank will adopt a new framework for setting interest rates, allowing inflation to run moderately above the 2% target for some time. This change reflects the Fed\'s updated understanding of the relationship between unemployment and inflation.',
                'url' => 'https://example.com/fed-policy',
                'image_url' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800',
                'author' => 'Jennifer Williams',
                'category' => 'Business',
                'tags' => ['Federal Reserve', 'Interest Rates', 'Economy', 'Inflation'],
                'published_at' => now()->subHours(6),
            ],
            [
                'news_source_id' => $sources->where('slug', 'newsapi')->first()->id ?? 1,
                'external_id' => 'newsapi_002',
                'title' => 'Sports: Underdog Team Wins Championship in Historic Victory',
                'description' => 'In an unexpected turn of events, the underdog team has secured the championship title in a thrilling final match.',
                'content' => 'Against all odds, the underdog team has emerged victorious in the championship final, defeating the heavily favored defending champions. The victory marks the first time in 25 years that a team with such low pre-season expectations has won the title.',
                'url' => 'https://example.com/championship-victory',
                'image_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800',
                'author' => 'Robert Martinez',
                'category' => 'Sports',
                'tags' => ['Championship', 'Underdog', 'Victory', 'Sports'],
                'published_at' => now()->subHours(8),
            ],
            [
                'news_source_id' => $sources->where('slug', 'guardian')->first()->id ?? 2,
                'external_id' => 'guardian_002',
                'title' => 'Health: New Study Links Diet to Longevity',
                'description' => 'Research suggests that certain dietary patterns may significantly impact life expectancy and overall health outcomes.',
                'content' => 'A comprehensive study involving over 100,000 participants has found strong correlations between dietary patterns and longevity. The research, conducted over 20 years, shows that diets rich in plant-based foods, whole grains, and healthy fats are associated with longer life expectancy.',
                'url' => 'https://example.com/diet-longevity',
                'image_url' => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=800',
                'author' => 'Dr. Emily Rodriguez',
                'category' => 'Health',
                'tags' => ['Diet', 'Longevity', 'Health', 'Research'],
                'published_at' => now()->subHours(10),
            ],
            [
                'news_source_id' => $sources->where('slug', 'nytimes')->first()->id ?? 3,
                'external_id' => 'nytimes_002',
                'title' => 'Politics: Bipartisan Agreement Reached on Infrastructure Bill',
                'description' => 'Lawmakers from both parties have announced a breakthrough agreement on a comprehensive infrastructure spending package.',
                'content' => 'After months of negotiations, bipartisan lawmakers have reached an agreement on a $1.2 trillion infrastructure bill. The package includes funding for roads, bridges, broadband internet, and clean energy projects. The bill is expected to create millions of jobs and modernize the nation\'s infrastructure.',
                'url' => 'https://example.com/infrastructure-bill',
                'image_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800',
                'author' => 'David Thompson',
                'category' => 'Politics',
                'tags' => ['Infrastructure', 'Bipartisan', 'Politics', 'Legislation'],
                'published_at' => now()->subHours(12),
            ],
            [
                'news_source_id' => $sources->where('slug', 'newsapi')->first()->id ?? 1,
                'external_id' => 'newsapi_003',
                'title' => 'Entertainment: Award-Winning Director Announces New Project',
                'description' => 'The acclaimed filmmaker has revealed details about an upcoming project that promises to push creative boundaries.',
                'content' => 'Oscar-winning director has announced their next project, a groundbreaking film that combines traditional storytelling with cutting-edge technology. The project, which has been in development for three years, is expected to revolutionize the cinematic experience.',
                'url' => 'https://example.com/director-project',
                'image_url' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=800',
                'author' => 'Lisa Anderson',
                'category' => 'Entertainment',
                'tags' => ['Film', 'Director', 'Entertainment', 'Oscar'],
                'published_at' => now()->subHours(14),
            ],
            [
                'news_source_id' => $sources->where('slug', 'guardian')->first()->id ?? 2,
                'external_id' => 'guardian_003',
                'title' => 'Science: Discovery of New Species in Amazon Rainforest',
                'description' => 'Scientists have discovered a previously unknown species of wildlife in the depths of the Amazon rainforest.',
                'content' => 'A team of biologists has discovered a new species of frog in the Amazon rainforest. The discovery, made during a biodiversity survey, highlights the importance of preserving these ecosystems. The new species has unique characteristics that distinguish it from known relatives.',
                'url' => 'https://example.com/new-species',
                'image_url' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800',
                'author' => 'Dr. Carlos Mendez',
                'category' => 'Science',
                'tags' => ['New Species', 'Amazon', 'Science', 'Biodiversity'],
                'published_at' => now()->subHours(16),
            ],
            [
                'news_source_id' => $sources->where('slug', 'nytimes')->first()->id ?? 3,
                'external_id' => 'nytimes_003',
                'title' => 'Education: Universities Adopt Hybrid Learning Models',
                'description' => 'Higher education institutions are embracing hybrid learning approaches that combine online and in-person instruction.',
                'content' => 'Universities across the country are implementing hybrid learning models that combine the best of online and traditional classroom education. This approach offers students flexibility while maintaining the benefits of face-to-face interaction with professors and peers.',
                'url' => 'https://example.com/hybrid-learning',
                'image_url' => 'https://images.unsplash.com/photo-1523240794102-9ebd0b167d56?w=800',
                'author' => 'Professor Amanda Foster',
                'category' => 'Education',
                'tags' => ['Education', 'Hybrid Learning', 'Universities', 'Online'],
                'published_at' => now()->subHours(18),
            ],
            [
                'news_source_id' => $sources->where('slug', 'newsapi')->first()->id ?? 1,
                'external_id' => 'newsapi_004',
                'title' => 'Technology: Major Tech Company Unveils Revolutionary Product',
                'description' => 'A leading technology company has announced a breakthrough product that could change how we interact with digital devices.',
                'content' => 'The tech giant has unveiled its latest innovation, a device that combines artificial intelligence with augmented reality in ways never seen before. The product, which will be available next year, promises to revolutionize how we work, play, and communicate.',
                'url' => 'https://example.com/tech-innovation',
                'image_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=800',
                'author' => 'Tech Reporter',
                'category' => 'Technology',
                'tags' => ['Technology', 'Innovation', 'AI', 'AR'],
                'published_at' => now()->subHours(20),
            ],
        ];

        foreach ($articles as $articleData) {
            Article::create($articleData);
        }

        $this->command->info('Created ' . count($articles) . ' sample articles in MongoDB.');
    }
} 