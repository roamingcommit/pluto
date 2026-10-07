<?php

use App\Models\User;

test('the form retains all fields and escapes text after invalid dates', function () {
    $user = User::factory()->create();
    $title = 'Berlin "weekend" <script>alert(1)</script>';
    $hotel = 'Hotel "Central" <img src=x onerror=alert(2)>';

    $response = $this->actingAs($user)->followingRedirects()->from('/trips/create')->post('/trips', [
        'title' => $title,
        'start_date' => '2026-10-12',
        'end_date' => '2026-10-10',
        'hotel' => $hotel,
    ]);

    $response->assertOk()
        ->assertSee('role="alert"', false)
        ->assertSee('Cancel')
        ->assertDontSee('cdn.jsdelivr.net/npm/@tailwindcss/browser', false)
        ->assertSee('name="title" value="'.e($title).'"', false)
        ->assertSee('name="start_date" value="2026-10-12"', false)
        ->assertSee('name="end_date" value="2026-10-10"', false)
        ->assertSee('name="hotel" value="'.e($hotel).'"', false)
        ->assertDontSee('<img src=x onerror=alert(2)>', false)
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('The end date field must be a date after or equal to start date.');
    $this->assertDatabaseCount('trips', 0);
});

test('trips belong to the signed in user even when another owner is submitted', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $input = ['title' => 'Berlin weekend', 'start_date' => '2026-12-01', 'end_date' => '2026-12-03', 'hotel' => 'Example Hotel'];

    $response = $this->actingAs($user)->post('/trips', $input + ['user_id' => $otherUser->id]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('trips.index'));
    $this->assertDatabaseCount('trips', 1);
    $this->assertDatabaseHas('trips', $input + ['user_id' => $user->id]);
});

test('same day trips can be saved without a hotel', function (array $hotelInput) {
    $user = User::factory()->create();
    $input = ['title' => 'Day trip', 'start_date' => '2026-12-01', 'end_date' => '2026-12-01'];

    $response = $this->actingAs($user)->post('/trips', $input + $hotelInput);

    $response->assertSessionHasNoErrors()->assertRedirect(route('trips.index'));
    $this->assertDatabaseCount('trips', 1);
    $this->assertDatabaseHas('trips', $input + ['hotel' => null, 'user_id' => $user->id]);
})->with(['omitted hotel' => [[]], 'empty hotel' => [['hotel' => '']]]);

test('guests cannot save trips', function () {
    $response = $this->post('/trips', ['title' => 'Day trip', 'start_date' => '2026-12-01', 'end_date' => '2026-12-01']);

    $response->assertRedirect(route('login'));
    $this->assertDatabaseCount('trips', 0);
});

test('required trip details cannot be omitted', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->from('/trips/create')->post('/trips', []);

    $response->assertRedirect('/trips/create')->assertSessionHasErrors([
        'title' => 'The title field is required.',
        'start_date' => 'The start date field is required.',
        'end_date' => 'The end date field is required.',
    ]);
    $this->assertDatabaseCount('trips', 0);
});

test('invalid trip details are rejected without saving', function (string $field, mixed $value, string $message) {
    $user = User::factory()->create();
    $input = ['title' => 'Day trip', 'start_date' => '2026-12-01', 'end_date' => '2026-12-02', 'hotel' => 'Example Hotel'];
    $input[$field] = $value;

    $response = $this->actingAs($user)->from('/trips/create')->post('/trips', $input);

    $response->assertRedirect('/trips/create')->assertSessionHasErrors([$field => $message]);
    $this->assertDatabaseCount('trips', 0);
})->with([
    'non text title' => ['title', ['invalid'], 'The title field must be a string.'],
    'long title' => ['title', str_repeat('a', 256), 'The title field must not be greater than 255 characters.'],
    'invalid start date' => ['start_date', 'invalid', 'The start date field must be a valid date.'],
    'invalid end date' => ['end_date', 'invalid', 'The end date field must be a valid date.'],
    'end before start' => ['end_date', '2026-11-30', 'The end date field must be a date after or equal to start date.'],
    'non text hotel' => ['hotel', ['invalid'], 'The hotel field must be a string.'],
    'long hotel' => ['hotel', str_repeat('a', 256), 'The hotel field must not be greater than 255 characters.'],
]);
