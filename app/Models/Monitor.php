<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\SiteStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monitor extends Model
{
    protected $attributes = [
        'status' => 'pending',
        'check_interval' => 5,
        'threshold' => 3,
    ];

    protected $fillable = [
        'user_id',
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
     * Get the user that owns the monitor.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {

            $query->whereNull('last_checked_at')
                ->orWhereRaw(
                    '
                    TIMESTAMPDIFF(
                        MINUTE,
                        last_checked_at,
                        NOW()
                    ) >= check_interval
                    '
                );
        });
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
