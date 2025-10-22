<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'client@gmail.com';
        $password = '12345678';

        User::updateOrCreate(
            ['email' => $email],

            [
                'name' => 'clien',
                'password' => bcrypt($password),
                'role' => 'client',
                'email_verified_at' => now(),
            ]
        );
    }
}
