<?php

use App\Filament\Resources\Posts\PostResource;

test('resource exposes useful attributes to global search', function (): void {
    $attributes = PostResource::getGloballySearchableAttributes();

    expect($attributes)->toBe(['title', 'slug', 'category', 'author']);
});
