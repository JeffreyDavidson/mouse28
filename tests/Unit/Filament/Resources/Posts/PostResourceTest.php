<?php

use App\Filament\Resources\Posts\PostResource;

test('resource exposes useful attributes to global search', function (): void {
    expect(PostResource::getGloballySearchableAttributes())->toBe(['title', 'slug', 'category', 'author']);
});
