<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Coupon extends Model {
    protected $fillable = ["code", "discount_type", "value", "min_order_amount", "expiry_date"];
}
