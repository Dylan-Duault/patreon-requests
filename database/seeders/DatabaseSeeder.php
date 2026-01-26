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
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create active patrons with video requests
        $patrons = User::factory(50)->activePatron(100)->create();
        $highTierPatrons = User::factory(15)->activePatron(300)->create();

        // Create video requests for patrons
        foreach ($patrons as $patron) {
            VideoRequest::factory(rand(0, 1))->create([
                'user_id' => $patron->id,
                'requested_at' => fake()->dateTimeBetween('-2 month', 'now')
            ]);
        }

        foreach ($highTierPatrons as $patron) {
            VideoRequest::factory(rand(0, 2))->create([
                'user_id' => $patron->id,
                'requested_at' => fake()->dateTimeBetween('-2 month', 'now')
            ]);
        }

        // Create some completed requests distributed among patrons
        $allPatrons = $patrons->merge($highTierPatrons);
        VideoRequest::factory(300)->completed()->create([
            'user_id' => fn () => $allPatrons->random()->id,
        ]);

        // Create a former patron (no active requests)
        User::factory(10)->formerPatron()->create();

        // Create regular users (non-patrons)
        User::factory(20)->create();
    }
}

