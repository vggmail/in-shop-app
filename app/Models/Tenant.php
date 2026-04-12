<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;
    protected $connection = 'mysql';
    protected $fillable = ['subdomain', 'name', 'upi_id', 'is_active', 'expires_at', 'logo', 'tagline', 'address', 'city', 'state', 'pincode', 'phone', 'whatsapp_number', 'gst_number', 'dine_in_enabled', 'takeaway_enabled', 'home_delivery_enabled', 'cash_enabled', 'online_enabled'];

    protected $casts = [
        'dine_in_enabled' => 'boolean',
        'takeaway_enabled' => 'boolean',
        'home_delivery_enabled' => 'boolean',
        'cash_enabled' => 'boolean',
        'online_enabled' => 'boolean',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
