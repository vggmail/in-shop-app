<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentAttempt extends Model {
    protected $fillable = [
        "order_id", "txnid", "mihpayid", "status", "amount", 
        "hash_string", "calculated_hash", "received_hash", 
        "request_data", "response_data", "error_message"
    ];
    protected $casts = [
        "request_data" => "array",
        "response_data" => "array"
    ];
    public function order() { return $this->belongsTo(Order::class); }
}
