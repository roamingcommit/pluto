<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('the demo user has three trips with two activities per trip after seeding', function () {
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseCount('trips', 3);
    $this->assertDatabaseCount('activities', 6);

    $user = User::where('email', 'test@example.com')->firstOrFail();
    $trips = $user->trips()->with('activities')->get();

    expect($trips)->toHaveCount(3);

    foreach ($trips as $trip) {
        expect($trip->activities)->toHaveCount(2);

        foreach ($trip->activities as $activity) {
            expect(strtotime($activity->starts_at))
                ->toBeGreaterThanOrEqual(strtotime($trip->start_date))
                ->toBeLessThanOrEqual(strtotime($trip->end_date));
        }
    }
});
