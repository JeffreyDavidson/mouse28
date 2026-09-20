<?php

use App\Filament\Resources\Episodes\EpisodeResource;

test('episode resource searches by title slug and episode number', function (): void {
    $attributes = EpisodeResource::getGloballySearchableAttributes();

    expect($attributes)->toBe(['title', 'slug', 'episode_number']);
});
