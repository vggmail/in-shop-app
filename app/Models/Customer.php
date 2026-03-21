<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Customer extends Model {
    protected $fillable = ["name", "phone", "email", "total_orders", "total_purchase"];
    public function orders() { return $this->hasMany(Order::class); }
}
