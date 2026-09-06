<?php

use App\Filament\Resources\Episodes\EpisodeResource;

test('resource exposes useful attributes to global search', function (): void {
    expect(EpisodeResource::getGloballySearchableAttributes())->toBe(['title', 'slug', 'episode_number']);
});
