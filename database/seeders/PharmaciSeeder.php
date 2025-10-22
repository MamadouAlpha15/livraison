<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class PharmaciSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          $email = 'pharmacie@gmail.com';
        $password = '12345678';

        User::updateOrCreate(
            ['email' => $email],

            [
                'name' => 'pharmacie',
                'password' => bcrypt($password),
                'role' => 'vendeur',
                'email_verified_at' => now(),
            ]
        );
    }
}
