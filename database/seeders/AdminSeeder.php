<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'username' => 'Super Admin',
            'email' => 'admin@resepku.com', // Email Admin
            'password' => Hash::make('admin123'), // Password Admin
            'role' => 'admin', // Role kunci
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}