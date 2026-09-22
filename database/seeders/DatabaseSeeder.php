<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed default demo user if not already present
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'plan' => 'pro',
            ]
        );

        $this->call([
            CategorySeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
