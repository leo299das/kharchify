<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SplitSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'created_by',
        'from_member_id',
        'to_member_id',
        'amount',
        'payment_method',
        'transaction_ref',
        'notes',
        'settled_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'settled_at' => 'date',
    ];

    public function group()
    {
        return $this->belongsTo(SplitGroup::class, 'group_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fromMember()
    {
        return $this->belongsTo(SplitGroupMember::class, 'from_member_id');
    }

    public function toMember()
    {
        return $this->belongsTo(SplitGroupMember::class, 'to_member_id');
    }
}
