<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CouponUsage extends Model {
    protected $fillable = ["coupon_id", "order_id", "discount_amount"];
    public function coupon() { return $this->belongsTo(Coupon::class); }
    public function order() { return $this->belongsTo(Order::class); }
}
