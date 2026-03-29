<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = ['subdomain', 'name', 'upi_id', 'is_active', 'logo', 'tagline', 'address', 'city', 'state', 'pincode', 'phone', 'gst_number'];
}
