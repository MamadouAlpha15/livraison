<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class EmployeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $email = 'employe@gmail.com';
        $password = '12345678';

        User::updateOrCreate(
            ['email' => $email],

            [
                'name' => 'employer',
                'password' => bcrypt($password),
                'role' => 'employe',
                'email_verified_at' => now(),
            ]
        );
    }
}
