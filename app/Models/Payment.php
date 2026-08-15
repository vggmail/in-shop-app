<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model {
    protected $fillable = ["order_id", "method", "amount", "date", "status", "transaction_id", "refund_id", "refund_status", "refund_amount"];
    public function order() { return $this->belongsTo(Order::class); }
}
