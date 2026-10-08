<?php

use App\Filament\Forms\Components\SeoSection;
use App\Filament\Resources\Episodes\Pages\CreateEpisode;
use App\Filament\Resources\Guides\Pages\CreateGuide;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

covers(SeoSection::class);

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()
    ->admin()
    ->create()));

test('the SEO section is collapsed and titled SEO', function (): void {
    $section = SeoSection::make();

    expect($section->getHeading())->toBe('SEO');
    expect($section->getDescription())->toBe('Search engine optimization');
    expect($section->isCollapsed())->toBeTrue();
});

test('forms offer the SEO fields', function (string $page): void {
    livewire($page)
        ->assertFormFieldExists('seo.title')
        ->assertFormFieldExists('seo.description')
        ->assertFormFieldExists('seo.robots');
})->with([
    'post' => CreatePost::class,
    'guide' => CreateGuide::class,
    'episode' => CreateEpisode::class,
]);
