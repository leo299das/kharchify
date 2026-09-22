<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'income_category_id',
        'source',
        'amount',
        'payment_method',
        'transaction_id',
        'income_date',
        'note',
    ];

    protected $casts = [
        'amount' => 'float',
        'income_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(IncomeCategory::class, 'income_category_id');
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->category?->name ?? 'Other Income';
    }

    public function getCategoryIconAttribute(): string
    {
        return $this->category?->icon ?? '💵';
    }
}
