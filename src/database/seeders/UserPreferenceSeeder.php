<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;

class UserPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Create sample user preferences
        $preferences = [
            [
                'user_id' => $user->id,
                'selected_sources' => [1, 2], // NewsAPI and Guardian
                'selected_categories' => ['Technology', 'Science', 'Health'],
                'selected_authors' => ['Dr. Sarah Johnson', 'Dr. Emily Rodriguez'],
                'excluded_sources' => [],
                'excluded_categories' => ['Entertainment'],
            ],
            [
                'user_id' => User::firstOrCreate(
                    ['email' => 'business@example.com'],
                    [
                        'name' => 'Business User',
                        'password' => bcrypt('password'),
                    ]
                )->id,
                'selected_sources' => [1, 3], // NewsAPI and NYT
                'selected_categories' => ['Business', 'Economy', 'Politics'],
                'selected_authors' => ['Jennifer Williams', 'David Thompson'],
                'excluded_sources' => [],
                'excluded_categories' => ['Sports', 'Entertainment'],
            ],
            [
                'user_id' => User::firstOrCreate(
                    ['email' => 'sports@example.com'],
                    [
                        'name' => 'Sports Fan',
                        'password' => bcrypt('password'),
                    ]
                )->id,
                'selected_sources' => [1, 2, 3], // All sources
                'selected_categories' => ['Sports', 'Entertainment'],
                'selected_authors' => ['Robert Martinez', 'Lisa Anderson'],
                'excluded_sources' => [],
                'excluded_categories' => ['Politics', 'Economy'],
            ],
            [
                'user_id' => User::firstOrCreate(
                    ['email' => 'environment@example.com'],
                    [
                        'name' => 'Environment Advocate',
                        'password' => bcrypt('password'),
                    ]
                )->id,
                'selected_sources' => [2], // Guardian only
                'selected_categories' => ['Environment', 'Science', 'Health'],
                'selected_authors' => ['Michael Chen', 'Dr. Carlos Mendez'],
                'excluded_sources' => [],
                'excluded_categories' => ['Sports', 'Entertainment'],
            ],
            [
                'user_id' => User::firstOrCreate(
                    ['email' => 'tech@example.com'],
                    [
                        'name' => 'Tech Enthusiast',
                        'password' => bcrypt('password'),
                    ]
                )->id,
                'selected_sources' => [1, 2, 3], // All sources
                'selected_categories' => ['Technology', 'Science', 'Business'],
                'selected_authors' => ['Dr. Sarah Johnson', 'Tech Reporter'],
                'excluded_sources' => [],
                'excluded_categories' => ['Sports'],
            ],
        ];

        foreach ($preferences as $preferenceData) {
            UserPreference::create($preferenceData);
        }

        $this->command->info('Created ' . count($preferences) . ' sample user preferences in MongoDB.');
    }
} 