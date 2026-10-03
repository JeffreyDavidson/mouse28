<?php

use App\Filament\Resources\Posts\PostResource;

test('post resource searches by title slug category name and author names', function (): void {
    $attributes = PostResource::getGloballySearchableAttributes();

    expect($attributes)->toBe(['title', 'slug', 'category.name', 'authors.name']);
});
