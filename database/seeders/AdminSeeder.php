<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $email = 'admin@gmail.com';
        $password = '12345678';

        User::updateOrCreate(
            ['email' => $email],

            [
                'name' => 'admin',
                'password' => bcrypt($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
