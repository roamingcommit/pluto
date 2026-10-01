<?php

use App\Models\Trip;
use App\Models\User;

test('owners see existing values and an edit link', function () {
    $user = User::factory()->create();
    $trip = Trip::factory()->for($user)->create([
        'title' => 'Berlin "weekend"',
        'hotel' => '<script>alert(1)</script>',
        'start_date' => '2026-10-10 12:00:00',
        'end_date' => '2026-10-12 12:00:00',
    ]);

    $this->actingAs($user)->get(route('trips.edit', $trip))
        ->assertOk()
        ->assertSee('value="Berlin &quot;weekend&quot;"', false)
        ->assertSee('value="2026-10-10"', false)
        ->assertSee('value="2026-10-12"', false)
        ->assertSee('value="&lt;script&gt;alert(1)&lt;/script&gt;"', false)
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('action="'.route('trips.update', $trip).'"', false)
        ->assertSee('value="PATCH"', false);
    $this->get(route('trips.show', $trip))
        ->assertSee('href="'.route('trips.edit', $trip).'"', false);
});

test('owners can update a trip without changing its owner or creating a duplicate', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trip = Trip::factory()->for($user)->create();
    $input = ['title' => 'Updated weekend', 'start_date' => '2026-12-01', 'end_date' => '2026-12-03', 'hotel' => 'New Hotel'];

    $response = $this->actingAs($user)->patch(route('trips.update', $trip), $input + ['user_id' => $otherUser->id]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('trips.show', $trip));
    $this->assertDatabaseCount('trips', 1);
    $this->assertDatabaseHas('trips', $input + ['id' => $trip->id, 'user_id' => $user->id]);
});

test('owners can clear a hotel and save a same day trip', function (array $hotelInput) {
    $user = User::factory()->create();
    $trip = Trip::factory()->for($user)->create(['hotel' => 'Old Hotel']);
    $input = ['title' => 'Day trip', 'start_date' => '2026-12-01', 'end_date' => '2026-12-01'];

    $response = $this->actingAs($user)->patch(route('trips.update', $trip), $input + $hotelInput);

    $response->assertSessionHasNoErrors()->assertRedirect(route('trips.show', $trip));
    $this->assertDatabaseHas('trips', $input + ['id' => $trip->id, 'hotel' => null]);
})->with(['omitted' => [[]], 'blank' => [['hotel' => '']]]);

test('guests cannot open the editor or update trips', function () {
    $trip = Trip::factory()->create();
    $original = $trip->getAttributes();

    $this->get(route('trips.edit', $trip))->assertRedirect(route('login'));
    $this->patch(route('trips.update', $trip), ['title' => 'Changed'])->assertRedirect(route('login'));

    $this->assertDatabaseHas('trips', $original);
});

test('other users cannot open the editor or change a trip', function () {
    $trip = Trip::factory()->create();
    $otherUser = User::factory()->create();
    $original = $trip->getAttributes();

    $this->actingAs($otherUser)->get(route('trips.edit', $trip))->assertForbidden();
    $this->patch(route('trips.update', $trip), [
        'title' => 'Changed', 'start_date' => '2026-12-01', 'end_date' => '2026-12-02',
    ])->assertForbidden();

    $this->assertDatabaseHas('trips', $original);
});

test('missing trips return not found for editing and updating', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/trips/999/edit')->assertNotFound();
    $this->patch('/trips/999', [])->assertNotFound();
});

test('invalid edits retain all inputs and show errors without changing the trip', function () {
    $user = User::factory()->create();
    $trip = Trip::factory()->for($user)->create();
    $original = $trip->getAttributes();

    $response = $this->actingAs($user)->followingRedirects()->from(route('trips.edit', $trip))
        ->patch(route('trips.update', $trip), [
            'title' => 'Attempted title', 'hotel' => 'Attempted hotel',
            'start_date' => '2026-12-03', 'end_date' => '2026-12-01',
        ]);

    $response->assertOk()
        ->assertSee('The end date field must be a date after or equal to start date.')
        ->assertSee('value="Attempted title"', false)
        ->assertSee('value="Attempted hotel"', false)
        ->assertSee('value="2026-12-03"', false)
        ->assertSee('value="2026-12-01"', false);
    $this->assertDatabaseHas('trips', $original);
});

test('required update fields cannot be omitted', function () {
    $user = User::factory()->create();
    $trip = Trip::factory()->for($user)->create();
    $original = $trip->getAttributes();

    $response = $this->actingAs($user)->from(route('trips.edit', $trip))->patch(route('trips.update', $trip), []);

    $response->assertRedirect(route('trips.edit', $trip))->assertSessionHasErrors([
        'title' => 'The title field is required.',
        'start_date' => 'The start date field is required.',
        'end_date' => 'The end date field is required.',
    ]);
    $this->assertDatabaseHas('trips', $original);
});

test('invalid update values leave the saved trip unchanged', function (string $field, mixed $value, string $message) {
    $user = User::factory()->create();
    $trip = Trip::factory()->for($user)->create();
    $original = $trip->getAttributes();
    $input = ['title' => 'Weekend', 'start_date' => '2026-12-01', 'end_date' => '2026-12-03', 'hotel' => 'Hotel'];
    $input[$field] = $value;

    $response = $this->actingAs($user)->from(route('trips.edit', $trip))->patch(route('trips.update', $trip), $input);

    $response->assertRedirect(route('trips.edit', $trip))->assertSessionHasErrors([$field => $message]);
    $this->assertDatabaseHas('trips', $original);
})->with([
    'non text title' => ['title', ['invalid'], 'The title field must be a string.'],
    'long title' => ['title', str_repeat('a', 256), 'The title field must not be greater than 255 characters.'],
    'invalid start date' => ['start_date', 'invalid', 'The start date field must be a valid date.'],
    'invalid end date' => ['end_date', 'invalid', 'The end date field must be a valid date.'],
    'non text hotel' => ['hotel', ['invalid'], 'The hotel field must be a string.'],
    'long hotel' => ['hotel', str_repeat('a', 256), 'The hotel field must not be greater than 255 characters.'],
]);
