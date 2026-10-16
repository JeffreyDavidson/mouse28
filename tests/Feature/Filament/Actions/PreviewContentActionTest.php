<?php

use App\Filament\Actions\PreviewContentAction;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Models\Guide;
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
    'guide' => [EditGuide::class, fn (): Guide => Guide::factory()
        ->draft()
        ->create(), 'preview.guide', 'guide'],
]);

test('the guide edit page uses the shared preview action with an eye icon', function (string $editPage, Guide $draft): void {
    livewire($editPage, ['record' => $draft->getRouteKey()])
        ->assertActionExists('preview', fn (Action $action): bool => $action instanceof PreviewContentAction
            && $action->getIcon() === Heroicon::OutlinedEye);
})->with('previewable drafts');

test('the preview action opens a 24-hour signed preview link in a new tab', function (string $editPage, Guide $draft, string $previewRoute, string $routeParameter): void {
    Date::setTestNow(Date::now());

    livewire($editPage, ['record' => $draft->getRouteKey()])
        ->assertActionVisible('preview')
        ->assertActionHasUrl('preview', URL::temporarySignedRoute($previewRoute, Date::now()->addHours(24), [$routeParameter => $draft]))
        ->assertActionShouldOpenUrlInNewTab('preview');
})->with('previewable drafts');
