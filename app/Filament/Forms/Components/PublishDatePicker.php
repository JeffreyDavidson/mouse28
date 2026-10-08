<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Enums\PublishStatus;
use App\Support\DisplayTimezone;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\Model;

/**
 * Publish date field for publishable content. Filament's default timezone is the site's
 * display timezone, so the date is entered in that timezone and stored in UTC. Live and
 * scheduled content must keep a date: its status is locked on the form, so clearing the
 * date would take it offline while it still shows as published.
 */
class PublishDatePicker extends DateTimePicker
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->required(fn (?Model $record): bool => in_array(
                $record?->getAttribute('status'),
                [PublishStatus::Published, PublishStatus::Scheduled],
                true,
            ))
            ->hint(fn (): string => DisplayTimezone::label());
    }
}
