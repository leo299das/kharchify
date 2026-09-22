<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SplitGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'currency',
        'icon',
        'invite_code',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($group) {
            if (empty($group->invite_code)) {
                $group->invite_code = strtoupper(Str::random(8));
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members()
    {
        return $this->hasMany(SplitGroupMember::class, 'group_id');
    }

    public function expenses()
    {
        return $this->hasMany(SplitExpense::class, 'group_id')->orderBy('expense_date', 'desc')->orderBy('id', 'desc');
    }

    public function settlements()
    {
        return $this->hasMany(SplitSettlement::class, 'group_id')->orderBy('settled_at', 'desc')->orderBy('id', 'desc');
    }

    public function getTotalExpensesAttribute(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    public function getTotalSettlementsAttribute(): float
    {
        return (float) $this->settlements()->sum('amount');
    }

    public function getTypeBadgeAttribute(): array
    {
        return match($this->type) {
            'trip' => ['label' => '✈️ Trip / Vacation', 'bg' => 'bg-sky-50 text-sky-800 border-sky-200', 'icon' => '✈️'],
            'apartment' => ['label' => '🏠 Roommates / Rent', 'bg' => 'bg-amber-50 text-amber-800 border-amber-200', 'icon' => '🏠'],
            'couple' => ['label' => '❤️ Couple / Shared', 'bg' => 'bg-rose-50 text-rose-800 border-rose-200', 'icon' => '❤️'],
            'event' => ['label' => '🎉 Event / Party', 'bg' => 'bg-purple-50 text-purple-800 border-purple-200', 'icon' => '🎉'],
            default => ['label' => '👥 Friends & Group', 'bg' => 'bg-indigo-50 text-indigo-800 border-indigo-200', 'icon' => '👥'],
        };
    }

    public function getMemberForUser(?int $userId, ?string $email = null): ?SplitGroupMember
    {
        if ($userId) {
            $member = $this->members()->where('user_id', $userId)->first();
            if ($member) return $member;
        }

        if ($email) {
            $member = $this->members()->whereRaw('LOWER(email) = ?', [strtolower(trim($email))])->first();
            if ($member) return $member;
        }

        return null;
    }
}
