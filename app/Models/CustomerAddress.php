<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;
    protected $fillable = ['customer_id', 'street_address', 'city', 'state', 'pincode', 'label', 'is_default'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
