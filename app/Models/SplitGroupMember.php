<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SplitGroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
        'name',
        'email',
        'phone',
        'upi_id',
        'is_admin',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(SplitGroup::class, 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paidExpenses()
    {
        return $this->hasMany(SplitExpense::class, 'paid_by_member_id');
    }

    public function expenseParticipations()
    {
        return $this->hasMany(SplitExpenseParticipant::class, 'group_member_id');
    }

    public function sentSettlements()
    {
        return $this->hasMany(SplitSettlement::class, 'from_member_id');
    }

    public function receivedSettlements()
    {
        return $this->hasMany(SplitSettlement::class, 'to_member_id');
    }

    /**
     * Calculate member's total paid, total share owed, and net balance in this group
     */
    public function getBalanceDetailsAttribute(): array
    {
        // 1. Amount paid by this member for group expenses
        $expensesPaid = (float) $this->paidExpenses()->sum('amount');

        // 2. Share of expenses this member owes
        $expensesShare = (float) $this->expenseParticipations()->sum('share_amount');

        // 3. Settlements paid to others by this member
        $settlementsSent = (float) $this->sentSettlements()->sum('amount');

        // 4. Settlements received from others by this member
        $settlementsReceived = (float) $this->receivedSettlements()->sum('amount');

        // Total credit = Expenses paid + settlements paid to others
        $totalCredit = $expensesPaid + $settlementsSent;

        // Total debit = Share of expenses owed + settlements received from others
        $totalDebit = $expensesShare + $settlementsReceived;

        // Net balance: positive = is owed money, negative = owes money
        $netBalance = round($totalCredit - $totalDebit, 2);

        return [
            'expenses_paid' => $expensesPaid,
            'expenses_share' => $expensesShare,
            'settlements_sent' => $settlementsSent,
            'settlements_received' => $settlementsReceived,
            'total_credit' => $totalCredit,
            'total_debit' => $totalDebit,
            'net_balance' => $netBalance,
            'status' => $netBalance > 0.01 ? 'owed' : ($netBalance < -0.01 ? 'owes' : 'settled'),
        ];
    }
}
