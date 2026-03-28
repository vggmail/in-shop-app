<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model {
    use SoftDeletes;
    protected $fillable = ["code", "discount_percentage", "expiry_date", "min_bill_amount", "coupon_type", "show_on_home"];
}
