<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'joel@example.test'],
            [
                'name' => 'Joel Miller',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'miles@example.test'],
            [
                'name' => 'Miles Morales',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'satoru@example.test'],
            [
                'name' => 'Satoru Gojo',
                'password' => Hash::make('password123'),
                'is_active' => false,
            ]
        );

        User::factory()->count(7)->create();
    }
}
