<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Coupon extends Model {
    protected $fillable = ["code", "discount_percentage", "expiry_date", "min_bill_amount", "coupon_type", "show_on_home"];
}
