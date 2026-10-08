<?php

use App\Filament\Forms\Components\SlugInput;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Episodes\Pages\CreateEpisode;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Filament\Resources\Guides\Pages\CreateGuide;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Filament\Resources\NewsletterIssues\Pages\CreateNewsletterIssue;
use App\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\User;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\NewsletterIssueFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

covers(SlugInput::class);

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()
    ->admin()
    ->create()));

dataset('slug edit pages', [
    'post' => [fn () => Post::factory()->credited(), EditPost::class],
    'guide' => [fn () => Guide::factory()->credited(), EditGuide::class],
    'episode' => [fn () => Episode::factory(), EditEpisode::class],
    'newsletter issue' => [fn () => NewsletterIssue::factory(), EditNewsletterIssue::class],
]);

test('slugs must be present, lowercase words joined by single hyphens, and unused', function (string $page, Closure $existing, mixed $slug, string $rule): void {
    $existing();

    livewire($page)
        ->fillForm(['slug' => $slug])
        ->call('create')
        ->assertHasFormErrors(['slug' => $rule]);
})->with([
    'post' => [CreatePost::class, fn () => Post::factory()->create(['slug' => 'taken'])],
    'guide' => [CreateGuide::class, fn () => Guide::factory()->create(['slug' => 'taken'])],
    'episode' => [CreateEpisode::class, fn () => Episode::factory()->create(['slug' => 'taken'])],
    'newsletter issue' => [CreateNewsletterIssue::class, fn () => NewsletterIssue::factory()->create(['slug' => 'taken'])],
    'category' => [CreateCategory::class, fn () => Category::factory()->create(['slug' => 'taken'])],
])->with([
    'missing' => [null, 'required'],
    'uppercase' => ['Taken-Slug', 'regex'],
    'spaces' => ['taken slug', 'regex'],
    'repeated hyphens' => ['taken--slug', 'regex'],
    'too long' => [str_repeat('a', 256), 'max'],
    'already used' => ['taken', 'unique'],
]);

test('the slug of published content cannot be edited', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory, string $page): void {
    $content = $factory->createOne();

    livewire($page, ['record' => $content->getRouteKey()])
        ->assertFormFieldDisabled('slug');
})->with('slug edit pages');

test('the slug of a draft that was never published can be edited', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory, string $page): void {
    $content = $factory->draft()
        ->createOne();

    livewire($page, ['record' => $content->getRouteKey()])
        ->assertFormFieldEnabled('slug');
})->with('slug edit pages');
