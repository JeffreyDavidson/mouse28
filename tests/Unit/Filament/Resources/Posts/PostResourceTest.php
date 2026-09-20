<?php

use App\Filament\Resources\Posts\PostResource;

test('post resource searches by title slug category and author', function (): void {
    $attributes = PostResource::getGloballySearchableAttributes();

    expect($attributes)->toBe(['title', 'slug', 'category', 'author']);
});
