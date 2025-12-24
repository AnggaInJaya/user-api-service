<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // setiap user punya 1–5 order
            $count = rand(1, 5);

            for ($i = 0; $i < $count; $i++) {
                Order::create([
                    'user_id' => $user->id,
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);
            }
        }
    }
}
