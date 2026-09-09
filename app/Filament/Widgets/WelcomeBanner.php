<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeBanner extends Widget
{
    #[\Override]
    protected static ?int $sort = -3;

    #[\Override]
    protected int|string|array $columnSpan = 'full';

    #[\Override]
    protected string $view = 'filament.widgets.welcome-banner';
}
