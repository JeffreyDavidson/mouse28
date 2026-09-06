<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PublicationStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case NeedsPublishDate = 'needs-publish-date';
    case Scheduled = 'scheduled';
    case Published = 'published';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::NeedsPublishDate => 'Needs publish date',
            self::Scheduled => 'Scheduled',
            self::Published => 'Published',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Published => 'success',
            self::Scheduled, self::NeedsPublishDate => 'warning',
            self::Draft => 'gray',
        };
    }
}
