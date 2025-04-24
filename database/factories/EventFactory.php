<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    { 
        $start = Carbon::instance(fake()->dateTimeBetween('+1 day', '+1 month'));
        $end = $start->copy()->addHours(fake()->numberBetween(1,5));
        return [
           'name' => fake()->sentence(4) . 'Event',
           'start_datetime' => $start,
           'end_datetime' => $end,
           'max_participants' => fake()->numberBetween(10, 100)
        ];
    }
}
