<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'user_id',
        'opening_balance',
        'closing_balance',
        'expected_balance',
        'status',
        'opened_at',
        'closed_at'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function petty_cash_logs()
    {
        return $this->hasMany(PettyCash::class);
    }

    public function orders()
    {
        // Orders placed during this shift
        // Since we don't have shift_id in orders yet, we'll filter by time
        return Order::where('created_at', '>=', $this->opened_at)
                    ->where(function($q) {
                        if ($this->closed_at) {
                            $q->where('created_at', '<=', $this->closed_at);
                        }
                    });
    }
}
