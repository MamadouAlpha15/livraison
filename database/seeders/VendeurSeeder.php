<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class VendeurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'vendeur@gmail.com';
        $password = '12345678';
        User::updateOrCreate(
            ['email' => $email],

            [
                'name' => 'Vendeur',
                'password' => bcrypt($password),
                'role' => 'vendeur',
                'email_verified_at' => now(),
            ]
        );
    }
}
