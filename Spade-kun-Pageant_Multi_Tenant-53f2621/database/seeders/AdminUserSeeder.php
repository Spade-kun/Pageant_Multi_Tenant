<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Spade Kun',
            'email' => '2201104232@student.buksu.edu.ph',
            'password' => Hash::make('Sp4d3kun015'),
            'role' => 'admin',
        ]);

        $this->command->info('Admin user created successfully. Email: admin@example.com, Password: password');
    }
} 