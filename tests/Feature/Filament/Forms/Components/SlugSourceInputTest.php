<?php

use App\Filament\Forms\Components\SlugSourceInput;
use App\Filament\Resources\Episodes\Pages\CreateEpisode;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Filament\Resources\Guides\Pages\CreateGuide;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Filament\Resources\NewsletterIssues\Pages\CreateNewsletterIssue;
use App\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
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

covers(SlugSourceInput::class);

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->admin()->create()));

dataset('slug create pages', [
    'post' => [CreatePost::class],
    'guide' => [CreateGuide::class],
    'episode' => [CreateEpisode::class],
    'newsletter issue' => [CreateNewsletterIssue::class],
]);

test('a new title fills a blank slug', function (string $page): void {
    livewire($page)
        ->fillForm(['title' => 'Plan a Quiet Park Day'])
        ->assertSchemaStateSet(['slug' => 'plan-a-quiet-park-day']);
})->with('slug create pages');

test('a new title keeps a slug the editor already wrote', function (string $page): void {
    livewire($page)
        ->fillForm(['slug' => 'my-own-address'])
        ->fillForm(['title' => 'Plan a Quiet Park Day'])
        ->assertSchemaStateSet(['slug' => 'my-own-address']);
})->with('slug create pages');

test('changing the title of saved content keeps its slug', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory, string $page): void {
    $content = $factory->draft()->createOne(['slug' => 'original-address']);

    livewire($page, ['record' => $content->getRouteKey()])
        ->fillForm(['title' => 'A Better Title'])
        ->assertSchemaStateSet(['slug' => 'original-address']);
})->with([
    'post' => [fn () => Post::factory()->credited(), EditPost::class],
    'guide' => [fn () => Guide::factory()->credited(), EditGuide::class],
    'episode' => [fn () => Episode::factory(), EditEpisode::class],
    'newsletter issue' => [fn () => NewsletterIssue::factory(), EditNewsletterIssue::class],
]);
