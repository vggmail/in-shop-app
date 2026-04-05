<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderItem extends Model {
    protected $fillable = ["order_id", "item_id", "item_variant_id", "price", "quantity", "total"];
    public function order() { return $this->belongsTo(Order::class); }
    public function item() { return $this->belongsTo(Item::class); }
    public function variant() { return $this->belongsTo(ItemVariant::class, "item_variant_id"); }
    public function extras() { return $this->hasMany(OrderItemExtra::class); }
}