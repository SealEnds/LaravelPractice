<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'omegaboss',
            'email' => 'omegaboss@ownvibes.com',
            'password' => Hash::make('6za7]wz]Za#1'),
            'email_verified_at' => now(),
            'remember_token' => '',
        ]);
    }
}
