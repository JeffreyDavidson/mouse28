<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ContentType;
use Filament\Widgets\Widget;

class WelcomeBanner extends Widget
{
    #[\Override]
    protected static ?int $sort = -3;

    #[\Override]
    protected int|string|array $columnSpan = 'full';

    #[\Override]
    protected string $view = 'filament.widgets.welcome-banner';

    /** @return list<array{type: ContentType, label: string, url: string}> */
    public function getCreateLinks(): array
    {
        return array_map(fn (ContentType $type): array => [
            'type' => $type,
            'label' => "New {$type->getLabel()}",
            'url' => $type->resource()::getUrl('create'),
        ], ContentType::cases());
    }
}
