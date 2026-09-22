<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'color',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class, 'income_category_id');
    }

    /**
     * Default income categories available to all users.
     */
    public static function getDefaultCategories(): array
    {
        return [
            ['name' => 'Salary', 'icon' => '💼', 'color' => 'indigo'],
            ['name' => 'Business & Sales', 'icon' => '🛒', 'color' => 'emerald'],
            ['name' => 'Freelance & Gigs', 'icon' => '💻', 'color' => 'sky'],
            ['name' => 'Investments & Returns', 'icon' => '📈', 'color' => 'amber'],
            ['name' => 'Rental Income', 'icon' => '🏠', 'color' => 'purple'],
            ['name' => 'Gifts & Pocket Money', 'icon' => '🎁', 'color' => 'rose'],
            ['name' => 'Other Income', 'icon' => '💵', 'color' => 'slate'],
        ];
    }

    /**
     * Ensure default categories exist or retrieve all categories for user.
     */
    public static function getCategoriesForUser(?int $userId = null)
    {
        $categories = static::where(function ($q) use ($userId) {
            $q->whereNull('user_id');
            if ($userId) {
                $q->orWhere('user_id', $userId);
            }
        })->orderBy('name')->get();

        if ($categories->isEmpty()) {
            foreach (static::getDefaultCategories() as $def) {
                static::create([
                    'user_id' => null,
                    'name' => $def['name'],
                    'icon' => $def['icon'],
                    'color' => $def['color'],
                ]);
            }
            $categories = static::where(function ($q) use ($userId) {
                $q->whereNull('user_id');
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
            })->orderBy('name')->get();
        }

        return $categories;
    }
}
