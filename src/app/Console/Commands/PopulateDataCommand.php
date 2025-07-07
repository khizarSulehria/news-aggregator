<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PopulateDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:populate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate database with sample data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Populating database with sample data...');
        
        try {
            // Run seeders
            $this->call('db:seed');
            
            $this->info('✅ Database populated successfully!');
            $this->line('');
            $this->line('Sample data created:');
            $this->line('  • 3 News Sources (MySQL)');
            $this->line('  • 10 Articles (MongoDB)');
            $this->line('  • 5 Users with Preferences (MongoDB)');
            $this->line('');
            $this->line('Test users created:');
            $this->line('  • test@example.com (password: password)');
            $this->line('  • business@example.com (password: password)');
            $this->line('  • sports@example.com (password: password)');
            $this->line('  • environment@example.com (password: password)');
            $this->line('  • tech@example.com (password: password)');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to populate data: ' . $e->getMessage());
            return 1;
        }
    }
} 