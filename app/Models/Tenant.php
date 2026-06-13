<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;
    protected $connection = 'mysql';
    protected $fillable = ['subdomain', 'name', 'upi_id', 'is_active', 'expires_at', 'logo', 'tagline', 'address', 'city', 'state', 'pincode', 'phone', 'whatsapp_number', 'gst_number', 'dine_in_enabled', 'takeaway_enabled', 'home_delivery_enabled', 'cash_enabled', 'online_enabled', 'disable_home_page', 'starting_token', 'floor_plans', 'enabled_menus'];

    protected $casts = [
        'dine_in_enabled' => 'boolean',
        'takeaway_enabled' => 'boolean',
        'home_delivery_enabled' => 'boolean',
        'cash_enabled' => 'boolean',
        'online_enabled' => 'boolean',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'disable_home_page' => 'boolean',
        'floor_plans' => 'array',
        'enabled_menus' => 'array',
    ];

    /**
     * Check if a sidebar menu key is enabled for this tenant's store admin.
     *
     * Returns true when:
     *  - enabled_menus is null (all menus on — backward-compatible default)
     *  - OR the key exists in the enabled_menus array
     *
     * Super Admin bypass is handled in the Blade template, not here.
     */
    public function isMenuEnabled(string $key): bool
    {
        if (is_null($this->enabled_menus)) {
            return true; // null = all menus enabled
        }
        return in_array($key, $this->enabled_menus);
    }
}
