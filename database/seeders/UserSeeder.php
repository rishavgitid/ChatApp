<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Rishav Kumar',
            'email' => 'rishavkumar3738@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Kumar@123'),
        ]);
    }
}
