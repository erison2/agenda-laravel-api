<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'type' => 'Work',
            'description' => $this->faker->paragraph,
            'user_id' => \App\Models\User::factory(),
            'start_date' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(1)->addHours(2)->format('Y-m-d H:i:s'),
            'status' => 'open',
        ];
    }
}
