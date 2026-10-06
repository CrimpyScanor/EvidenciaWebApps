<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Joel Miller',
            'email' => 'Joel@tlou.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Miles Morales',
            'email' => 'Miles2@Spiderman.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Satoru Gojo',
            'email' => 'Satoru@jujutsu.com',
            'password' => Hash::make('password123'),
        ]);
    }
}
