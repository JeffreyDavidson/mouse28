<?php

use App\Filament\Resources\Episodes\EpisodeResource;

test('resource exposes useful attributes to global search', function (): void {
    $attributes = EpisodeResource::getGloballySearchableAttributes();

    expect($attributes)->toBe(['title', 'slug', 'episode_number']);
});
