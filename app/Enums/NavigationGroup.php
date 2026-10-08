<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The admin menu sections, listed in the order the panel shows them.
 *
 * Groups carry no icon: Filament refuses a group icon when the group's items have icons, and every item has one.
 */
enum NavigationGroup: string implements HasLabel
{
    case Content = 'content';
    case Communication = 'communication';
    case Settings = 'settings';

    public function getLabel(): string
    {
        return match ($this) {
            self::Content => 'Content',
            self::Communication => 'Communication',
            self::Settings => 'Settings',
        };
    }
}
