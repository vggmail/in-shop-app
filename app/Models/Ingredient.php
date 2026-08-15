<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'unit',
        'stock_quantity',
        'min_stock_level',
        'cost_per_unit',
        'is_active',
        'is_alcohol',
        'bottle_size_ml'
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:3',
        'min_stock_level' => 'decimal:3',
        'cost_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
        'is_alcohol' => 'boolean',
        'bottle_size_ml' => 'decimal:2'
    ];

    public function recipes()
    {
        return $this->hasMany(ItemIngredient::class);
    }

    public function wastages()
    {
        return $this->hasMany(BarWastage::class);
    }
}
