<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarWastage extends Model
{
    protected $fillable = [
        'tenant_id',
        'ingredient_id',
        'type', // 'Breakage', 'Spill', 'Free Pour', 'Complimentary'
        'quantity', // in bottles/units
        'volume_ml', // equivalent volume
        'logged_by',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'volume_ml' => 'decimal:2',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
