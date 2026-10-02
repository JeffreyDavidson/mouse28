<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SocialPlatform: string implements HasLabel
{
    case Instagram = 'instagram';
    case TikTok = 'tiktok';
    case Facebook = 'facebook';
    case YouTube = 'youtube';
    case X = 'x';
    case Bluesky = 'bluesky';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Instagram => 'Instagram',
            self::TikTok => 'TikTok',
            self::Facebook => 'Facebook',
            self::YouTube => 'YouTube',
            self::X => 'X / Twitter',
            self::Bluesky => 'Bluesky',
            self::Other => 'Other',
        };
    }
}
