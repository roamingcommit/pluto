<?php

namespace Database\Factories;

use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+6 months');
        $endDate = fake()->dateTimeBetween($startDate, '+1 year');

        return [
            'title' => fake()->city() . ' Trip',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'hotel' => fake()->optional()->company(),
            'user_id' => User::factory(),


        ];
    }
}
