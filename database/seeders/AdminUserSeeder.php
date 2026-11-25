<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('HALCON_ADMIN_EMAIL', 'admin@halcon.local')],
            [
                'name' => 'Administrador HALCON',
                'password' => env('HALCON_ADMIN_PASSWORD', 'HalconAdmin2026!'),
                'role' => 'Administrator',
                'is_active' => true,
            ]
        );
    }
}
