<?php

use App\Models\Activity;
use App\Models\Trip;

test('a trip returns only its own activities and each activity resolves its trip', function () {
    $trip = Trip::factory()->create();
    $otherTrip = Trip::factory()->create();
    $activities = Activity::factory()->count(2)->for($trip)->create([
        'title' => 'Museum visit',
        'starts_at' => '2026-12-01 10:00:00',
    ]);
    Activity::factory()->for($otherTrip)->create([
        'title' => 'Dinner',
        'starts_at' => '2026-12-01 19:00:00',
    ]);

    $result = $trip->activities()->orderBy('id')->get();

    expect($result->modelKeys())->toBe($activities->modelKeys());
    expect($result->first()->trip->id)->toBe($trip->id);
});
test('the factory schedules an activity within its trip dates', function () {
    $trip = Trip::factory()->create([
        'start_date' => '2026-10-10',
        'end_date' => '2026-10-12',
    ]);

    $activity = Activity::factory()->for($trip)->create()->fresh();

    expect($activity->trip_id)->toBe($trip->id);

    expect(strtotime($activity->starts_at))
        ->toBeGreaterThanOrEqual(strtotime('2026-10-10'))
        ->toBeLessThanOrEqual(strtotime('2026-10-12'));
});
test('deleting a trip removes only its own activities', function () {
    $trip = Trip::factory()->create();
    $otherTrip = Trip::factory()->create();

    $activity = Activity::factory()->for($trip)->create();
    $otherActivity = Activity::factory()->for($otherTrip)->create();

    $trip->delete();

    $this->assertModelMissing($trip);
    $this->assertModelMissing($activity);
    $this->assertModelExists($otherTrip);
    $this->assertModelExists($otherActivity);
});
