<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'user_name' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'active' => '1',
        ]);
        User::factory()->count(3)->create();
        User::factory()->teacher()->count(2)->create();
    }
}