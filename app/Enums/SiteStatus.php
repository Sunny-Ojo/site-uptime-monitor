<?php

namespace App\Enums;

enum SiteStatus: string
{
    case PENDING = 'pending';
    case UP = 'up';
    case DOWN = 'down';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::UP => 'Up',
            self::DOWN => 'Down',
        };
    }
}
