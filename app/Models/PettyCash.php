<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PettyCash extends Model
{
    protected $table = 'petty_cash';

    protected $fillable = [
        'shift_id',
        'user_id',
        'amount',
        'type',
        'reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
