<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userEmail = 'john.doe@domain.com';

        // if (User::where('email', $userEmail)->doesntExist()) {
        User::create([
            'name' => 'John Doe',
            'email' => $userEmail,
            'password' => bcrypt('password'),
        ]);
        // }
    }
}
