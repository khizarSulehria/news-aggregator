<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MongoDB\Client;

class TestMongoConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mongo:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test MongoDB connection';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing MongoDB connection...');
        
        try {
            $host = config('database.connections.mongodb.host');
            $port = config('database.connections.mongodb.port');
            $database = config('database.connections.mongodb.database');
            
            $this->line("Connecting to: {$host}:{$port}");
            $this->line("Database: {$database}");
            
            $client = new Client("mongodb://{$host}:{$port}");
            $client->listDatabases();
            
            $this->info('✅ MongoDB connection successful!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ MongoDB connection failed: ' . $e->getMessage());
            return 1;
        }
    }
} 