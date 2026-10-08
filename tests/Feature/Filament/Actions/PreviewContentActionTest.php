<?php

use App\Filament\Actions\PreviewContentAction;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    actingAsAdmin();
});

dataset('previewable drafts', [
    'post' => [EditPost::class, fn (): Post => Post::factory()
        ->draft()
        ->create(), 'preview.post', 'post'],
    'guide' => [EditGuide::class, fn (): Guide => Guide::factory()
        ->draft()
        ->create(), 'preview.guide', 'guide'],
    'episode' => [EditEpisode::class, fn (): Episode => Episode::factory()
        ->draft()
        ->create(), 'preview.episode', 'episode'],
]);

test('edit pages use the shared preview action with an eye icon', function (string $editPage, Post|Guide|Episode $draft): void {
    livewire($editPage, ['record' => $draft->getRouteKey()])
        ->assertActionExists('preview', fn (Action $action): bool => $action instanceof PreviewContentAction
            && $action->getIcon() === Heroicon::OutlinedEye);
})->with('previewable drafts');

test('the preview action opens a 24-hour signed preview link in a new tab', function (string $editPage, Post|Guide|Episode $draft, string $previewRoute, string $routeParameter): void {
    Date::setTestNow(Date::now());

    livewire($editPage, ['record' => $draft->getRouteKey()])
        ->assertActionVisible('preview')
        ->assertActionHasUrl('preview', URL::temporarySignedRoute($previewRoute, Date::now()->addHours(24), [$routeParameter => $draft]))
        ->assertActionShouldOpenUrlInNewTab('preview');
})->with('previewable drafts');
