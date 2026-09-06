<?php

use App\Filament\Resources\Guides\GuideResource;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('resource exposes useful attributes to global search', function (): void {
    expect(GuideResource::getGloballySearchableAttributes())->toBe(['title', 'slug', 'category', 'author']);
});
