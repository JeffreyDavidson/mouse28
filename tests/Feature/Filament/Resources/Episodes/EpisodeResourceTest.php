<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('resource exposes useful attributes to global search', function (): void {
    expect(EpisodeResource::getGloballySearchableAttributes())->toBe(['title', 'slug', 'episode_number']);
});
