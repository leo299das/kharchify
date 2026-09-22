<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SplitExpenseParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'split_expense_id',
        'group_member_id',
        'share_amount',
        'percentage',
    ];

    protected $casts = [
        'share_amount' => 'float',
        'percentage' => 'float',
    ];

    public function expense()
    {
        return $this->belongsTo(SplitExpense::class, 'split_expense_id');
    }

    public function member()
    {
        return $this->belongsTo(SplitGroupMember::class, 'group_member_id');
    }
}
