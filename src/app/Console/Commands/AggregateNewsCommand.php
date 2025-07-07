<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AggregateNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:aggregate {--source= : Specific source slug to aggregate from}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate news articles from configured sources';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->error('News aggregation service has been removed. This command is no longer available.');
        return 1;
    }
} 