<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users
        User::create([
            'name' => 'Test Agency',
            'email' => 'test@manntravel.com',
            'password' => 'password123',
        ]);

        User::create([
            'name' => 'Demo User',
            'email' => 'demo@manntravel.com',
            'password' => 'demo1234',
        ]);
    }
}
