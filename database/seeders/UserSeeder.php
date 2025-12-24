<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $roles = [
            'Administrator',
            'Manager',
            'User',
        ];

        foreach ($roles as $role) {
            for ($i = 1; $i <= 2; $i++) {
                DB::table('users')->insert([
                    'email' => strtolower($role) . $i . '@example.com',
                    'name' => ucfirst($role) . ' ' . $i,
                    'role' => $role,
                    'password' => Hash::make('password123'),
                    'active' => true,
                    'created_at' => $now,
                ]);
            }
        }
    }
}
