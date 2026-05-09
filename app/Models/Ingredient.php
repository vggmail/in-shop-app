<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'stock_quantity',
        'min_stock_level',
        'cost_per_unit',
        'is_active'
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:3',
        'min_stock_level' => 'decimal:3',
        'cost_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function recipes()
    {
        return $this->hasMany(ItemIngredient::class);
    }
}
