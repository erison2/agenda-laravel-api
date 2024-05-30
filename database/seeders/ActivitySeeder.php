<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Activity::create([
            'title' => 'Meeting',
            'type' => 'Work',
            'description' => 'Project meeting',
            'user_id' => 1, // Assume que o ID do usuário criado no UserSeeder é 1
            'start_date' => '2023-05-01 10:00:00',
            'end_date' => '2023-05-01 11:00:00',
            'status' => 'open',
        ]);

        Activity::create([
            'title' => 'Lunch Break',
            'type' => 'Personal',
            'description' => 'Lunch with friends',
            'user_id' => 1,
            'start_date' => '2023-05-01 12:00:00',
            'end_date' => '2023-05-01 13:00:00',
            'status' => 'open',
        ]);

        Activity::create([
            'title' => 'Workout',
            'type' => 'Exercise',
            'description' => 'Evening gym session',
            'user_id' => 1,
            'start_date' => '2023-05-01 18:00:00',
            'end_date' => '2023-05-01 19:00:00',
            'status' => 'open',
        ]);
    }
}
