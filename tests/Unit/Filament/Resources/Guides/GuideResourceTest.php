<?php

use App\Filament\Resources\Guides\GuideResource;

test('resource exposes useful attributes to global search', function (): void {
    $attributes = GuideResource::getGloballySearchableAttributes();

    expect($attributes)->toBe(['title', 'slug', 'category', 'author']);
});
