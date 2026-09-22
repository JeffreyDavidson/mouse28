<?php

use App\Filament\Resources\Guides\GuideResource;

test('guide resource searches by title slug category and author', function (): void {
    $attributes = GuideResource::getGloballySearchableAttributes();

    expect($attributes)->toBe(['title', 'slug', 'category', 'author']);
});
