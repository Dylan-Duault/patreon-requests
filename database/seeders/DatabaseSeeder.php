<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VideoRequest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create an admin user
        $admin = User::factory()->admin()->activePatron(1000)->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create active patrons with video requests
        $patrons = User::factory(5)->activePatron(500)->create();
        $highTierPatrons = User::factory(3)->activePatron(1000)->create();

        // Create video requests for patrons
        foreach ($patrons as $patron) {
            VideoRequest::factory(rand(1, 3))->create([
                'user_id' => $patron->id,
            ]);
        }

        foreach ($highTierPatrons as $patron) {
            VideoRequest::factory(rand(2, 4))->create([
                'user_id' => $patron->id,
            ]);
        }

        // Create some completed requests
        VideoRequest::factory(5)->completed()->create([
            'user_id' => $patrons->random()->id,
        ]);

        // Create a former patron (no active requests)
        User::factory(2)->formerPatron()->create();

        // Create regular users (non-patrons)
        User::factory(3)->create();
    }
}

