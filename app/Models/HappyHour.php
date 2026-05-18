<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HappyHour extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'discount_percent',
        'start_time',
        'end_time',
        'days_of_week',
        'is_active'
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Check if this Happy Hour is currently active.
     */
    public function isActiveNow(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check Day of Week
        $currentDay = date('l'); // e.g. 'Monday'
        $days = array_map('trim', explode(',', $this->days_of_week));
        if (!in_array($currentDay, $days)) {
            return false;
        }

        // Check Time
        $currentTime = date('H:i:s');
        return ($currentTime >= $this->start_time && $currentTime <= $this->end_time);
    }
}
