<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Order extends Model {
    use LogsActivity, SoftDeletes;
    protected $fillable = ["order_number", "customer_id", "coupon_id", "order_type", "table_number", "delivery_address", "total_amount", "discount_amount", "grand_total", "payment_method", "payment_status", "status", "note", "source"];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}