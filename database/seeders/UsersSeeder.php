<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // If an admin user already exists, do not recreate the default bootstrap user
        if (User::where('role', 'admin')->exists()) {
            return;
        }

        User::create([
            'name' => 'Administrator',
            'email' => 'admin@pharmcare.local',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '',
            'is_active' => true,
        ]);
    }
}
