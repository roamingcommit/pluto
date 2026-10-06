<?php

use App\Models\Activity;
use App\Models\Trip;
use App\Models\User;

function activityTestTrip(): Trip
{
    return Trip::factory()->create([
        'start_date' => '2026-10-10', 'end_date' => '2026-10-12',
    ]);
}

dataset('activity endpoints', [
    'create' => ['get', 'create'],
    'store' => ['post', 'store'],
    'show' => ['get', 'show'],
    'edit' => ['get', 'edit'],
    'update' => ['patch', 'update'],
    'destroy' => ['delete', 'destroy'],
]);

describe('activity access', function () {
    test('guests are sent to login without changing activities', function (string $method, string $action) {
        $trip = activityTestTrip();
        $activity = Activity::factory()->for($trip)->create();
        $original = $activity->getAttributes();
        $parameters = in_array($action, ['create', 'store']) ? [$trip] : [$trip, $activity];

        $this->{$method}(route('trips.activities.'.$action, $parameters))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('activities', $original);
        $this->assertDatabaseCount('activities', 1);
    })->with('activity endpoints');

    test('other users are forbidden without changing activities', function (string $method, string $action) {
        $trip = activityTestTrip();
        $activity = Activity::factory()->for($trip)->create();
        $original = $activity->getAttributes();
        $parameters = in_array($action, ['create', 'store']) ? [$trip] : [$trip, $activity];

        $this->actingAs(User::factory()->create())
            ->{$method}(route('trips.activities.'.$action, $parameters))
            ->assertForbidden();

        $this->assertDatabaseHas('activities', $original);
        $this->assertDatabaseCount('activities', 1);
    })->with('activity endpoints');

    test('an activity cannot be accessed through a different trip even with the same owner', function (string $method, string $action) {
        $trip = activityTestTrip();
        $otherTrip = Trip::factory()->for($trip->user)->create();
        $activity = Activity::factory()->for($otherTrip)->create();
        $original = $activity->getAttributes();

        $this->actingAs($trip->user)->{$method}(route('trips.activities.'.$action, [$trip, $activity]))
            ->assertNotFound();

        $this->assertDatabaseHas('activities', $original);
    })->with([
        ['get', 'show'], ['get', 'edit'], ['patch', 'update'], ['delete', 'destroy'],
    ]);

    test('missing activities return not found', function (string $method, string $action) {
        $trip = activityTestTrip();

        $this->actingAs($trip->user)->{$method}(route('trips.activities.'.$action, [$trip, 999]))
            ->assertNotFound();
    })->with([
        ['get', 'show'], ['get', 'edit'], ['patch', 'update'], ['delete', 'destroy'],
    ]);
});

describe('activity pages', function () {
    test('trip details list only their activities in time order with working links', function () {
        $trip = activityTestTrip();
        $late = Activity::factory()->for($trip)->create(['title' => 'Evening dinner', 'starts_at' => '2026-10-11 19:00:00']);
        $early = Activity::factory()->for($trip)->create(['title' => 'Morning museum', 'starts_at' => '2026-10-11 09:00:00']);
        Activity::factory()->create(['title' => 'Unrelated private activity']);

        $this->actingAs($trip->user)->get(route('trips.show', $trip))
            ->assertOk()
            ->assertSeeInOrder(['Morning museum', 'Evening dinner'])
            ->assertDontSee('Unrelated private activity')
            ->assertSee(route('trips.activities.show', [$trip, $early]))
            ->assertSee(route('trips.activities.show', [$trip, $late]))
            ->assertSee(route('trips.activities.create', $trip));
    });

    test('empty trips offer an add activity link and owners can open the form', function () {
        $trip = activityTestTrip();

        $this->actingAs($trip->user)->get(route('trips.show', $trip))
            ->assertSee('No activities yet.');
        $this->get(route('trips.activities.create', $trip))
            ->assertOk()->assertSee('Save Activity')
            ->assertSee('name="_token"', false)
            ->assertSee('min="2026-10-10T00:00"', false)
            ->assertSee('max="2026-10-12T23:59"', false)
            ->assertSee('action="'.route('trips.activities.store', $trip).'"', false);
    });

    test('activity pages escape text and prefill the edit form', function () {
        $trip = activityTestTrip();
        $activity = Activity::factory()->for($trip)->create([
            'title' => '<script>title</script>',
            'location' => '<script>location</script>',
            'notes' => '</textarea><script>notes</script>',
            'starts_at' => '2026-10-11 09:30:00',
        ]);

        $this->actingAs($trip->user)->get(route('trips.activities.show', [$trip, $activity]))
            ->assertOk()->assertSee($activity->title)->assertSee($activity->location)->assertSee($activity->notes)
            ->assertDontSee('<script>title</script>', false)
            ->assertDontSee('<script>location</script>', false)
            ->assertDontSee('<script>notes</script>', false)
            ->assertSee('11 Oct 2026, 09:30')
            ->assertSee(route('trips.activities.edit', [$trip, $activity]))
            ->assertSee('return confirm(', false)
            ->assertSee('name="_token"', false)
            ->assertSee('value="DELETE"', false);
        $this->get(route('trips.activities.edit', [$trip, $activity]))
            ->assertOk()->assertSee($activity->title)->assertSee($activity->location)->assertSee($activity->notes)
            ->assertDontSee('<script>title</script>', false)
            ->assertDontSee('<script>location</script>', false)
            ->assertDontSee('<script>notes</script>', false)
            ->assertSee('value="2026-10-11T09:30"', false)
            ->assertSee('value="PATCH"', false)
            ->assertSee('action="'.route('trips.activities.update', [$trip, $activity]).'"', false);
        $this->get(route('trips.show', $trip))
            ->assertSee($activity->title)->assertSee($activity->location)
            ->assertDontSee('<script>title</script>', false)
            ->assertDontSee('<script>location</script>', false);
    });
});

describe('saving activities', function () {
    test('owners create an activity under the URL trip ignoring forged identifiers', function () {
        $trip = activityTestTrip();
        $otherTrip = Trip::factory()->create();

        $response = $this->actingAs($trip->user)->post(route('trips.activities.store', $trip), [
            'title' => 'Museum visit', 'starts_at' => '2026-10-11T09:30',
            'location' => 'Museum Island', 'notes' => 'Bring tickets.',
            'trip_id' => $otherTrip->id, 'id' => 999, 'user_id' => $otherTrip->user_id,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('trips.show', $trip))
            ->assertSessionHas('status', 'Activity added.');
        $this->assertDatabaseCount('activities', 1);
        $this->assertDatabaseHas('activities', [
            'trip_id' => $trip->id, 'title' => 'Museum visit',
            'starts_at' => '2026-10-11 09:30:00', 'location' => 'Museum Island', 'notes' => 'Bring tickets.',
        ]);
        $this->assertDatabaseMissing('activities', ['id' => 999]);
    });

    test('both trip date boundaries are allowed and optional fields can be omitted', function (string $time, string $storedTime) {
        $trip = activityTestTrip();

        $this->actingAs($trip->user)->post(route('trips.activities.store', $trip), [
            'title' => 'Boundary activity', 'starts_at' => $time,
        ])->assertSessionHasNoErrors()->assertRedirect(route('trips.show', $trip));

        $this->assertDatabaseHas('activities', [
            'trip_id' => $trip->id, 'starts_at' => $storedTime, 'location' => null, 'notes' => null,
        ]);
    })->with([
        'first minute' => ['2026-10-10T00:00', '2026-10-10 00:00:00'],
        'last minute' => ['2026-10-12T23:59', '2026-10-12 23:59:00'],
    ]);

    test('owners update an activity and clear optional fields without moving or duplicating it', function () {
        $trip = activityTestTrip();
        $otherTrip = Trip::factory()->create();
        $activity = Activity::factory()->for($trip)->create(['location' => 'Old location', 'notes' => 'Old notes']);

        $this->actingAs($trip->user)->patch(route('trips.activities.update', [$trip, $activity]), [
            'title' => 'New museum visit', 'starts_at' => '2026-10-12T23:59',
            'location' => '', 'notes' => '', 'trip_id' => $otherTrip->id, 'id' => 999,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('trips.activities.show', [$trip, $activity]))
            ->assertSessionHas('status', 'Activity updated.');

        $this->assertDatabaseCount('activities', 1);
        $this->assertDatabaseHas('activities', [
            'id' => $activity->id, 'trip_id' => $trip->id, 'title' => 'New museum visit',
            'starts_at' => '2026-10-12 23:59:00', 'location' => null, 'notes' => null,
        ]);
    });

    test('invalid activity values show useful errors without inserting a record', function (string $field, mixed $value, string $message) {
        $trip = activityTestTrip();
        $input = ['title' => 'Museum', 'starts_at' => '2026-10-11T09:30'];
        $input[$field] = $value;

        $this->actingAs($trip->user)->from(route('trips.activities.create', $trip))
            ->post(route('trips.activities.store', $trip), $input)
            ->assertRedirect(route('trips.activities.create', $trip))
            ->assertSessionHasErrors([$field => $message]);

        $this->assertDatabaseCount('activities', 0);
    })->with([
        'non text title' => ['title', [], 'The title field is required.'],
        'array title' => ['title', ['invalid'], 'The title field must be a string.'],
        'long title' => ['title', str_repeat('a', 256), 'The title field must not be greater than 255 characters.'],
        'invalid date' => ['starts_at', 'bad date', 'Choose a valid activity date and time.'],
        'impossible date' => ['starts_at', '2026-02-30T09:30', 'Choose a valid activity date and time.'],
        'before trip' => ['starts_at', '2026-10-09T23:59', 'The activity must be on or after the trip start date.'],
        'after trip' => ['starts_at', '2026-10-13T00:00', 'The activity must be on or before the trip end date.'],
        'array location' => ['location', ['invalid'], 'The location field must be a string.'],
        'long location' => ['location', str_repeat('a', 256), 'The location field must not be greater than 255 characters.'],
        'array notes' => ['notes', ['invalid'], 'The notes field must be a string.'],
        'long notes' => ['notes', str_repeat('a', 5001), 'The notes field must not be greater than 5000 characters.'],
    ]);

    test('title and time are required', function () {
        $trip = activityTestTrip();

        $this->actingAs($trip->user)->post(route('trips.activities.store', $trip), [])
            ->assertSessionHasErrors([
                'title' => 'The title field is required.',
                'starts_at' => 'The starts at field is required.',
            ]);

        $this->assertDatabaseCount('activities', 0);
    });

    test('invalid edits preserve attempted inputs but not overwrite the saved activity', function () {
        $trip = activityTestTrip();
        $activity = Activity::factory()->for($trip)->create();
        $original = $activity->getAttributes();

        $this->actingAs($trip->user)->followingRedirects()
            ->from(route('trips.activities.edit', [$trip, $activity]))
            ->patch(route('trips.activities.update', [$trip, $activity]), [
                'title' => 'Attempted museum', 'starts_at' => '2026-10-13T10:00',
                'location' => 'Attempted location', 'notes' => 'Attempted notes',
            ])
            ->assertSee('The activity must be on or before the trip end date.')
            ->assertSee('value="Attempted museum"', false)
            ->assertSee('value="2026-10-13T10:00"', false)
            ->assertSee('value="Attempted location"', false)
            ->assertSee('Attempted notes');

        $this->assertDatabaseHas('activities', $original);
    });
});

describe('deleting activities', function () {
    test('owners delete only the chosen activity and keep its trip', function () {
        $trip = activityTestTrip();
        $activity = Activity::factory()->for($trip)->create();
        $remaining = Activity::factory()->for($trip)->create();

        $this->actingAs($trip->user)->delete(route('trips.activities.destroy', [$trip, $activity]))
            ->assertRedirect(route('trips.show', $trip))
            ->assertSessionHas('status', 'Activity deleted.');

        $this->assertModelMissing($activity);
        $this->assertModelExists($remaining);
        $this->assertModelExists($trip);
    });
});

describe('trip date changes with activities', function () {
    test('trip dates cannot exclude a saved activity', function (string $start, string $end) {
        $trip = activityTestTrip();
        $activity = Activity::factory()->for($trip)->create(['starts_at' => '2026-10-11 09:30:00']);
        $original = $trip->getAttributes();

        $this->actingAs($trip->user)->patch(route('trips.update', $trip), [
            'title' => 'Changed trip', 'start_date' => $start, 'end_date' => $end,
        ])->assertSessionHasErrors([
            'end_date' => 'These dates would leave an activity outside the trip. Reschedule or remove that activity first.',
        ]);

        $this->assertDatabaseHas('trips', $original);
        $this->assertModelExists($activity);
    })->with([
        'start too late' => ['2026-10-12', '2026-10-13'],
        'end too early' => ['2026-10-09', '2026-10-10'],
    ]);

    test('a same day trip includes evening activities and ignores other trips activities', function () {
        $trip = activityTestTrip();
        Activity::factory()->for($trip)->create(['starts_at' => '2026-10-11 23:59:00']);
        Activity::factory()->create(['starts_at' => '2026-11-01 09:30:00']);

        $this->actingAs($trip->user)->patch(route('trips.update', $trip), [
            'title' => 'One day', 'start_date' => '2026-10-11', 'end_date' => '2026-10-11',
        ])->assertSessionHasNoErrors()->assertRedirect(route('trips.show', $trip));

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id, 'start_date' => '2026-10-11', 'end_date' => '2026-10-11',
        ]);
    });
});
