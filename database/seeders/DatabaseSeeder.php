<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        Trip::factory()
            ->count(3)
            ->for($user)
            ->has(Activity::factory()->count(2), 'activities')
            ->create();
    }
}
