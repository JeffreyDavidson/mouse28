<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContentAuthor: string implements HasLabel
{
    case Jeffrey = 'jeffrey';
    case Cassie = 'cassie';
    case Both = 'both';

    public function getLabel(): string
    {
        return match ($this) {
            self::Jeffrey => 'Jeffrey Davidson',
            self::Cassie => 'Cassie Davidson',
            self::Both => 'Jeffrey & Cassie',
        };
    }

    public function initials(): string
    {
        return match ($this) {
            self::Jeffrey => 'JD',
            self::Cassie => 'CD',
            self::Both => 'J&C',
        };
    }
}
