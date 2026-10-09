<?php

use App\Enums\ContentType;
use App\Filament\Concerns\ListsContentByStatus;
use App\Filament\Resources\Episodes\Pages\ListEpisodes;
use App\Filament\Resources\Guides\Pages\ListGuides;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    actingAsAdmin();
});

dataset('content list pages', [
    'posts' => [ListPosts::class, fn (): PostFactory => Post::factory()],
    'guides' => [ListGuides::class, fn (): GuideFactory => Guide::factory()],
    'episodes' => [ListEpisodes::class, fn (): EpisodeFactory => Episode::factory()],
]);

test('content list pages build their tabs and header from the shared concern', function (string $listPage): void {
    expect(class_uses_recursive($listPage))->toContain(ListsContentByStatus::class);
})->with([
    'posts' => ListPosts::class,
    'guides' => ListGuides::class,
    'episodes' => ListEpisodes::class,
]);

/** @param  array<string, string>  $tabs */
test('content lists offer status tabs, with a review due tab only for sourced content', function (ListPosts|ListGuides|ListEpisodes $page, array $tabs): void {
    expect(array_map(fn (Tab $tab): string|Htmlable|null => $tab->getLabel(), $page->getTabs()))->toBe($tabs);
})->with([
    'posts' => [fn (): ListPosts => app(ListPosts::class), ['all' => 'All', 'attention' => 'Needs attention', 'drafts' => 'Drafts', 'scheduled' => 'Scheduled', 'published' => 'Published', 'review-due' => 'Review due']],
    'guides' => [fn (): ListGuides => app(ListGuides::class), ['all' => 'All', 'attention' => 'Needs attention', 'drafts' => 'Drafts', 'scheduled' => 'Scheduled', 'published' => 'Published', 'review-due' => 'Review due']],
    'episodes' => [fn (): ListEpisodes => app(ListEpisodes::class), ['all' => 'All', 'attention' => 'Needs attention', 'drafts' => 'Drafts', 'scheduled' => 'Scheduled', 'published' => 'Published']],
]);

test('the published tab leaves out scheduled content', function (string $listPage, PostFactory|GuideFactory|EpisodeFactory $factory): void {
    $published = $factory->create();
    $scheduled = $factory
        ->scheduled()
        ->create();

    livewire($listPage)
        ->set('activeTab', 'published')
        ->assertCanSeeTableRecords([$published])
        ->assertCanNotSeeTableRecords([$scheduled]);
})->with('content list pages');

test('the header shows the content type title, description and create link', function (ContentType $type, string $subtitle, string $createLabel): void {
    get($type->resource()::getUrl())
        ->assertOk()
        ->assertSeeInOrder([$type->pluralLabel(), $subtitle, 'Published', 'Drafts', $type->resource()::getUrl('create'), $createLabel]);
})->with([
    'posts' => [ContentType::Post, 'Create and manage your blog content', 'New Post'],
    'guides' => [ContentType::Guide, 'Manage your accessibility guides', 'New Guide'],
    'episodes' => [ContentType::Episode, 'Manage your podcast episodes', 'New Episode'],
]);
