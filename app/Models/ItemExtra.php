<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ItemExtra extends Model {
    protected $fillable = ["item_id", "name", "price"];
    public function item() { return $this->belongsTo(Item::class); }
}