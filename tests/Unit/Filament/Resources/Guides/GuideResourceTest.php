<?php

use App\Filament\Resources\Guides\GuideResource;

test('resource exposes useful attributes to global search', function (): void {
    expect(GuideResource::getGloballySearchableAttributes())->toBe(['title', 'slug', 'category', 'author']);
});
