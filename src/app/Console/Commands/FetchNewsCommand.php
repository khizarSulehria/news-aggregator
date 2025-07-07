<?php

namespace App\Console\Commands;

use App\Services\NewsServiceFactory;
use App\Services\NewsServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch 
                            {--source= : Specific source slug to fetch from}
                            {--dry-run : Show what would be fetched without storing}
                            {--logged : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news articles from configured sources using service implementations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourceSlug = $this->option('source');
        $isDryRun = $this->option('dry-run');
        $isVerbose = $this->option('logged');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No articles will be stored');
        }
        
        $this->info('📰 Starting news aggregation...');

        try {
            if ($sourceSlug) {
                // Fetch from specific source
                $this->fetchFromSpecificSource($sourceSlug, $isDryRun, $isVerbose);
            } else {
                // Fetch from all active sources
                $this->fetchFromAllSources($isDryRun, $isVerbose);
            }

            $this->info('✅ News aggregation completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ News aggregation failed: {$e->getMessage()}");
            Log::error('News aggregation command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Fetch articles from a specific news source
     */
    private function fetchFromSpecificSource(string $sourceSlug, bool $isDryRun, bool $isVerbose): void
    {
        $this->info("🎯 Fetching from source: {$sourceSlug}");

        $newsSource = \App\Models\NewsSource::where('slug', $sourceSlug)->first();
        
        if (!$newsSource) {
            throw new \Exception("News source '{$sourceSlug}' not found");
        }

        if (!$newsSource->is_active) {
            throw new \Exception("News source '{$sourceSlug}' is not active");
        }

        $service = NewsServiceFactory::create($newsSource);
        $this->processService($service, $newsSource, $isDryRun, $isVerbose);
    }

    /**
     * Fetch articles from all active news sources
     */
    private function fetchFromAllSources(bool $isDryRun, bool $isVerbose): void
    {
        $this->info('🌐 Fetching from all active sources...');

        $services = NewsServiceFactory::createAllActive();
        if (empty($services)) {
            $this->warn('⚠️  No active news sources found');
            return;
        }

        $totalStats = [
            'sources_processed' => 0,
            'total_articles_fetched' => 0,
            'total_articles_created' => 0,
            'total_articles_updated' => 0,
            'total_errors' => 0,
        ];

        foreach ($services as $service) {
            $newsSource = $service->getNewsSource();
            
            $this->info("📡 Processing: {$newsSource->name} ({$newsSource->slug})");
            
            $stats = $this->processService($service, $newsSource, $isDryRun, $isVerbose);
            
            // Aggregate statistics
            $totalStats['sources_processed']++;
            $totalStats['total_articles_fetched'] += $stats['articles_fetched'];
            $totalStats['total_articles_created'] += $stats['articles_created'];
            $totalStats['total_articles_updated'] += $stats['articles_updated'];
            $totalStats['total_errors'] += count($stats['errors']);
        }

        // Display summary
        $this->displaySummary($totalStats, $isDryRun);
    }

    /**
     * Process a single news service
     */
    private function processService(NewsServiceInterface $service, $newsSource, bool $isDryRun, bool $isVerbose): array
    {
        $startTime = microtime(true);

        try {
            // Fetch articles from the service
            $this->line("  📥 Fetching articles...");
            $articles = $service->fetchArticlesFromService();
            if (empty($articles)) {
                $this->warn("  ⚠️  No articles fetched from {$newsSource->name}");
                return [
                    'articles_fetched' => 0,
                    'articles_created' => 0,
                    'articles_updated' => 0,
                    'errors' => [],
                    'source' => $newsSource->name
                ];
            }

            $this->info("  ✅ Fetched " . count($articles) . " articles from {$newsSource->name}");

            if ($isVerbose) {
                foreach ($articles as $index => $article) {
                    $this->line("    " . ($index + 1) . ". {$article['title']}");
                }
            }

            if ($isDryRun) {
                $this->info("  🔍 DRY RUN: Would store " . count($articles) . " articles");
                return [
                    'articles_fetched' => count($articles),
                    'articles_created' => count($articles),
                    'articles_updated' => 0,
                    'errors' => [],
                    'source' => $newsSource->name
                ];
            }

            // Store articles in the database
            $this->line("  💾 Storing articles...");
            $stats = $service->storeArticles($articles);
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->info("  ✅ Stored articles in {$duration}s");
            $this->line("    - Created: {$stats['articles_created']}");
            $this->line("    - Updated: {$stats['articles_updated']}");
            
            if (!empty($stats['errors'])) {
                $this->warn("    - Errors: " . count($stats['errors']));
                if ($isVerbose) {
                    foreach ($stats['errors'] as $error) {
                        $this->error("      • {$error['title']}: {$error['error']}");
                    }
                }
            }

            return $stats;

        } catch (\Exception $e) {
            $this->error("  ❌ Error processing {$newsSource->name}: {$e->getMessage()}");
            Log::error("Failed to process news source", [
                'source' => $newsSource->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'articles_fetched' => 0,
                'articles_created' => 0,
                'articles_updated' => 0,
                'errors' => [['title' => 'Service Error', 'error' => $e->getMessage()]],
                'source' => $newsSource->name
            ];
        }
    }

    /**
     * Display summary of the aggregation process
     */
    private function displaySummary(array $stats, bool $isDryRun): void
    {
        $this->newLine();
        $this->info('📊 AGGREGATION SUMMARY');
        $this->line('─' . str_repeat('─', 50));
        
        if ($isDryRun) {
            $this->line("🔍 DRY RUN - No articles were actually stored");
        }
        
        $this->line("Sources processed: {$stats['sources_processed']}");
        $this->line("Articles fetched: {$stats['total_articles_fetched']}");
        $this->line("Articles created: {$stats['total_articles_created']}");
        $this->line("Articles updated: {$stats['total_articles_updated']}");
        $this->line("Errors encountered: {$stats['total_errors']}");
        
        if ($stats['total_errors'] > 0) {
            $this->warn("⚠️  Some errors occurred during aggregation. Check logs for details.");
        }
    }
} 