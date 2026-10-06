<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Trip;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'title' => fake()->randomElement([
                'Museum visit',
                'Walking tour',
                'Dinner reservation',
            ]),
            'starts_at' => function (array $attributes) {
                $trip = Trip::findOrFail($attributes['trip_id']);

                return fake()->dateTimeBetween(
                    $trip->start_date,
                    $trip->end_date
                );
            },
            'location' => fake()->optional()->streetAddress(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
