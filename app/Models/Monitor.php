<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\SiteStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monitor extends Model
{
    protected $attributes = [
        'status' => 'pending',
        'check_interval' => 5,
        'threshold' => 3,
    ];

    protected $fillable = [
        'url',
        'check_interval',
        'threshold',
        'status',
        'last_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SiteStatus::class,
            'last_checked_at' => 'datetime',
        ];
    }

    /**
     * Get the check history for this monitor.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(CheckHistory::class);
    }

    /**
     * Calculate the uptime percentage.
     */
    public function getUptimePercentageAttribute(): ?float
    {
        $total = $this->histories()->count();

        if ($total === 0) {
            return null;
        }

        $upCount = $this->histories()->where('is_up', true)->count();

        return round(($upCount / $total) * 100, 2);
    }
}
