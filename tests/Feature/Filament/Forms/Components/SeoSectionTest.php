<?php

use App\Filament\Resources\Guides\Pages\CreateGuide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SeoSection;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\Pages\CreateEpisode;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\CreatePost;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()
    ->admin()
    ->create()));

test('the SEO section is collapsed and titled SEO', function (): void {
    $section = SeoSection::make();

    expect([$section->getHeading(), $section->getDescription(), $section->isCollapsed()])->toBe(['SEO', 'Search engine optimization', true]);
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
