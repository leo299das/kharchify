<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\User;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        $defaultCategories = [
            'Food & Dining',
            'Rent & Housing',
            'Utilities & Bills',
            'Transport & Fuel',
            'Shopping & Groceries',
            'Entertainment & Leisure',
            'Health & Medical',
            'Salary / Income',
            'Education & Books',
            'Investments & Savings',
            'Miscellaneous',
        ];

        foreach ($users as $user) {
            foreach ($defaultCategories as $categoryName) {
                Category::firstOrCreate([
                    'user_id' => $user->id,
                    'name' => $categoryName,
                ]);
            }
        }
    }
}
