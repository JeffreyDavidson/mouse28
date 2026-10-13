<?php

use App\Enums\AssetReviewStatus;
use App\Filament\Resources\AssetReviews\AssetReviewResource;
use App\Filament\Resources\AssetReviews\Pages\EditAssetReview;
use App\Models\AssetReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()
    ->use(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
    Storage::disk('local')
        ->put('asset-reviews/card.png', UploadedFile::fake()
            ->image('card.png')
            ->getContent());
    actingAsAdmin();
});

test('the edit page shows saved artwork and approval controls', function (): void {
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);

    get(AssetReviewResource::getUrl('edit', ['record' => $record]))
        ->assertOk()
        ->assertSee('Approve')
        ->assertSee('Request changes')
        ->assertSeeHtml(route('filament.admin.assetReviews.file', ['assetReview' => $record, 'file' => 0]));
});

test('approval records the authenticated reviewer and optional feedback', function (): void {
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);
    $page = livewire(EditAssetReview::class, ['record' => $record->getRouteKey()]);

    $page->callAction('approve', data: ['review_notes' => 'Ready to use.']);

    $page->assertHasNoFormErrors();
    expect($record->refresh()
        ->status)
        ->toBe(AssetReviewStatus::Approved)
        ->and($record->reviewed_by)
        ->toBe(auth()->id())
        ->and($record->reviewed_at)
        ->not->toBeNull()
        ->and($record->review_notes)
        ->toBe('Ready to use.');
});

test('requesting changes requires feedback', function (): void {
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);
    $page = livewire(EditAssetReview::class, ['record' => $record->getRouteKey()]);

    $page->callAction('requestChanges', data: ['review_notes' => '']);

    $page->assertHasFormErrors(['review_notes' => 'required']);
    expect($record->refresh()
        ->status)
        ->toBe(AssetReviewStatus::Pending);
});

test('requesting changes saves the reviewer feedback', function (): void {
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);
    $page = livewire(EditAssetReview::class, ['record' => $record->getRouteKey()]);

    $page->callAction('requestChanges', data: ['review_notes' => 'Please shorten the heading.']);

    $page->assertHasNoFormErrors();
    expect($record->refresh()
        ->status)
        ->toBe(AssetReviewStatus::ChangesRequested)
        ->and($record->review_notes)
        ->toBe('Please shorten the heading.')
        ->and($record->reviewed_by)
        ->toBe(auth()->id());
});

test('saving a new version clears the previous approval', function (): void {
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);
    $page = livewire(EditAssetReview::class, ['record' => $record->getRouteKey()]);
    $page->callAction('approve');
    $page->fillForm(['version' => 'v2']);

    $page->call('save');

    $page->assertHasNoFormErrors();
    expect($record->refresh()
        ->status)
        ->toBe(AssetReviewStatus::Pending)
        ->and($record->reviewed_by)
        ->toBeNull()
        ->and($record->reviewed_at)
        ->toBeNull()
        ->and($record->revision)
        ->toBe(3);
});

test('a stale review cannot approve artwork changed by another editor', function (): void {
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);
    $page = livewire(EditAssetReview::class, ['record' => $record->getRouteKey()]);
    $record->update(['version' => 'v2']);

    $page->callAction('approve');

    $page->assertHasErrors(['review']);
    $page->assertNotified('Review not saved');
    expect($record->refresh()
        ->status)
        ->toBe(AssetReviewStatus::Pending);
});

test('missing preview files cannot receive approval', function (): void {
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/missing.png']]);
    $page = livewire(EditAssetReview::class, ['record' => $record->getRouteKey()]);

    $page->callAction('approve');

    $page->assertHasErrors(['review']);
    $page->assertNotified('Review not saved');
    expect($record->refresh()
        ->status)
        ->toBe(AssetReviewStatus::Pending);
});

test('replacing review images clears approval and keeps the old private original', function (): void {
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);
    $page = livewire(EditAssetReview::class, ['record' => $record->getRouteKey()]);
    $page->callAction('approve');
    $page->fillForm(['files' => [UploadedFile::fake()
        ->image('replacement.png')]]);

    $page->call('save');

    $page->assertHasNoFormErrors();
    expect($record->refresh()
        ->status)
        ->toBe(AssetReviewStatus::Pending)
        ->and($record->reviewed_by)
        ->toBeNull()
        ->and($record->files)
        ->not->toBe(['asset-reviews/card.png']);
    Storage::disk('local')
        ->assertExists('asset-reviews/card.png');
});

test('a stale editor cannot overwrite a newer review set', function (): void {
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);
    $page = livewire(EditAssetReview::class, ['record' => $record->getRouteKey()]);
    $page->fillForm(['version' => 'stale-edit']);
    $record->update(['version' => 'newer-edit']);

    $page->call('save');

    $page->assertHasFormErrors(['version']);
    expect($record->refresh()
        ->version)
        ->toBe('newer-edit');
});

test('an uploaded-file path from a different review cannot be substituted', function (): void {
    Storage::disk('local')
        ->put('asset-reviews/other.png', UploadedFile::fake()
            ->image('other.png')
            ->getContent());
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);
    $page = livewire(EditAssetReview::class, ['record' => $record->getRouteKey()]);
    $page->fillForm(['files' => ['asset-reviews/other.png']]);

    $page->call('save');

    $page->assertHasFormErrors(['files']);
    expect($record->refresh()
        ->files)
        ->toBe(['asset-reviews/card.png']);
});

test('unsaved changes must be saved and reviewed before approval', function (): void {
    $record = AssetReview::factory()->create(['files' => ['asset-reviews/card.png']]);
    $page = livewire(EditAssetReview::class, ['record' => $record->getRouteKey()]);
    $page->fillForm(['version' => 'v2']);

    $page->callAction('approve');

    $page->assertHasErrors(['review']);
    $page->assertNotified('Review not saved');
    expect($record->refresh()
        ->status)->toBe(AssetReviewStatus::Pending);
});
