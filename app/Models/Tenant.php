<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = ['subdomain', 'name', 'upi_id', 'is_active', 'logo', 'tagline', 'address', 'city', 'state', 'pincode', 'phone', 'gst_number', 'dine_in_enabled', 'takeaway_enabled', 'home_delivery_enabled', 'cash_enabled', 'online_enabled'];

    protected $casts = [
        'dine_in_enabled' => 'boolean',
        'takeaway_enabled' => 'boolean',
        'home_delivery_enabled' => 'boolean',
        'cash_enabled' => 'boolean',
        'online_enabled' => 'boolean',
    ];
}
