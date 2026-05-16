<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemIngredient extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'item_id',
        'variant_id',
        'ingredient_id',
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'decimal:3'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function variant()
    {
        return $this->belongsTo(ItemVariant::class, 'variant_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
