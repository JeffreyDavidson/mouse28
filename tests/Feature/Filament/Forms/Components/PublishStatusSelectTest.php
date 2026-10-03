<?php

use App\Enums\PublishStatus;
use App\Filament\Forms\Components\PublishStatusSelect;
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
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

covers(PublishStatusSelect::class);

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->admin()->create()));

dataset('status edit pages', [
    'post' => [fn () => Post::factory()->credited(), EditPost::class],
    'guide' => [fn () => Guide::factory()->credited(), EditGuide::class],
    'episode' => [fn () => Episode::factory(), EditEpisode::class],
    'newsletter issue' => [fn () => NewsletterIssue::factory(), EditNewsletterIssue::class],
]);

test('create forms offer only pre-publication statuses', function (string $page, array $statuses): void {
    livewire($page)
        ->assertFormFieldExists('status', fn (Select $field): bool => array_keys($field->getOptions()) === $statuses)
        ->assertSchemaStateSet(['status' => PublishStatus::Draft->value]);
})->with([
    'post' => [CreatePost::class, ['draft', 'in_review']],
    'guide' => [CreateGuide::class, ['draft', 'in_review']],
    'episode' => [CreateEpisode::class, ['draft']],
    'newsletter issue' => [CreateNewsletterIssue::class, ['draft', 'in_review']],
]);

test('a published status submitted through the form is rejected', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory, string $page): void {
    $content = $factory->draft()->createOne();

    livewire($page, ['record' => $content->getRouteKey()])
        ->fillForm(['status' => PublishStatus::Published])
        ->call('save')
        ->assertHasFormErrors(['status']);

    expect($content->refresh()->publishStatus())->toBe(PublishStatus::Draft);
})->with('status edit pages');

test('live and scheduled statuses are locked while the rest of the form still saves', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory, string $page, int $days, PublishStatus $status): void {
    $content = $factory->draft()->createOne(['published_at' => Date::now()->addDays($days)]);
    $content->publish();

    livewire($page, ['record' => $content->getRouteKey()])
        ->assertFormFieldDisabled('status')
        ->assertSchemaStateSet(['status' => $status->value])
        ->fillForm(['title' => 'Edited title'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($content->refresh())
        ->title->toBe('Edited title')
        ->and($content->publishStatus())->toBe($status);
})->with('status edit pages')->with([
    'published' => [-1, PublishStatus::Published],
    'scheduled' => [1, PublishStatus::Scheduled],
]);

test('a forged draft status never unpublishes live content', function (): void {
    $post = Post::factory()->create(['status' => PublishStatus::Published]);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['status' => PublishStatus::Draft])
        ->call('save');

    expect($post->refresh()->publishStatus())->toBe(PublishStatus::Published);
});

test('an editor can move a draft into review', function (): void {
    $post = Post::factory()->draft()->credited()->create();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['status' => PublishStatus::InReview])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->refresh()->publishStatus())->toBe(PublishStatus::InReview);
});
