<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Order extends Model {
    use LogsActivity;
    protected $fillable = ["order_number", "customer_id", "order_type", "table_number", "total_amount", "discount_amount", "grand_total", "payment_method", "payment_status", "status", "note"];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}