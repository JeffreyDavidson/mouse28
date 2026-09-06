<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContactTopic: string implements HasLabel
{
    case General = 'general';
    case Accessibility = 'accessibility';
    case Collaboration = 'collaboration';
    case Guest = 'guest';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::General => 'General Question',
            self::Accessibility => 'Park Accessibility',
            self::Collaboration => 'Collaboration / Sponsorship',
            self::Guest => 'Podcast Guest',
            self::Other => 'Other',
        };
    }

    public function publicLabel(): string
    {
        return match ($this) {
            self::Accessibility => 'Park Accessibility Question',
            self::Guest => 'Guest on the Podcast',
            default => $this->getLabel(),
        };
    }
}
