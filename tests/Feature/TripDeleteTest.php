<?php

use App\Models\Trip;
use App\Models\User;

test('owners can delete only the selected trip', function () {
    $user = User::factory()->create();
    $trip = Trip::factory()->for($user)->create();
    $anotherTrip = Trip::factory()->for($user)->create();
    $otherUserTrip = Trip::factory()->create();

    $response = $this->actingAs($user)->delete(route('trips.destroy', $trip));

    $response->assertRedirect(route('trips.index'));
    $this->assertModelMissing($trip);
    $this->assertModelExists($anotherTrip);
    $this->assertModelExists($otherUserTrip);
    $this->assertModelExists($user);
    $this->get(route('trips.show', $trip))->assertNotFound();
});

test('guests cannot delete trips', function () {
    $trip = Trip::factory()->create();

    $response = $this->delete(route('trips.destroy', $trip));

    $response->assertRedirect(route('login'));
    $this->assertModelExists($trip);
});

test('other users cannot delete a trip even with a forged owner field', function () {
    $trip = Trip::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)->delete(route('trips.destroy', $trip), ['user_id' => $otherUser->id]);

    $response->assertForbidden();
    $this->assertModelExists($trip);
});

test('deleting a missing trip returns not found', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete('/trips/999');

    $response->assertNotFound();
});

test('trip details provide a protected delete form with a confirmation warning', function () {
    $user = User::factory()->create();
    $trip = Trip::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('trips.show', $trip));

    $response->assertOk()
        ->assertSee('action="'.route('trips.destroy', $trip).'"', false)
        ->assertSee('name="_token"', false)
        ->assertSee('name="_method" value="DELETE"', false)
        ->assertSee("onsubmit=\"return confirm('Delete this trip permanently? This cannot be undone.');\"", false)
        ->assertSee('Delete Trip');
    $this->assertModelExists($trip);
});

test('the browser form method override deletes the selected trip', function () {
    $user = User::factory()->create();
    $trip = Trip::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('trips.destroy', $trip), ['_method' => 'DELETE']);

    $response->assertRedirect(route('trips.index'));
    $this->assertModelMissing($trip);
});
