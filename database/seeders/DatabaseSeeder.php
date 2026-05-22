<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Visit;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::firstOrCreate(
        //     ['email' => 'admin@example.com'],
        //     [
        //         'name' => 'Administrator',
        //         'password' => bcrypt('12345678'),
        //         'email_verified_at' => now(),
        //         'is_admin'=>1
        //     ]
        // );

        Visit::factory()->member()->count(60)->create();
        Visit::factory()->guest()->count(30)->create();

    }
}
