<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SplitExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'created_by',
        'paid_by_member_id',
        'title',
        'amount',
        'currency',
        'category',
        'split_type',
        'expense_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'expense_date' => 'date',
    ];

    public function group()
    {
        return $this->belongsTo(SplitGroup::class, 'group_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payer()
    {
        return $this->belongsTo(SplitGroupMember::class, 'paid_by_member_id');
    }

    public function participants()
    {
        return $this->hasMany(SplitExpenseParticipant::class, 'split_expense_id');
    }

    public function getCategoryIconAttribute(): string
    {
        return match(strtolower($this->category ?? '')) {
            'food', 'food & drinks', 'dining' => '🍔',
            'transport', 'travel', 'cab', 'flights' => '🚕',
            'rent', 'accommodation', 'hotel', 'villa' => '🏠',
            'utilities', 'bills', 'wifi', 'electricity' => '⚡',
            'tickets', 'entertainment', 'movies' => '🎟️',
            'shopping', 'groceries' => '🛒',
            default => '📝',
        };
    }
}
