<?php

use App\Enums\ContentType;
use App\Filament\Widgets\WelcomeBanner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('welcome banner links to create each content type in order', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    $links = livewire(WelcomeBanner::class)->instance()
        ->getCreateLinks();

    expect(array_column($links, 'label'))->toBe(['New Post', 'New Episode', 'New Guide'])
        ->and(array_column($links, 'url'))
        ->toBe(array_map(fn (ContentType $type): string => $type->resource()::getUrl('create'), ContentType::cases()));
});

test('welcome banner renders a create button for each content type', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(WelcomeBanner::class)
        ->assertSee(['New Post', 'New Episode', 'New Guide']);
});
