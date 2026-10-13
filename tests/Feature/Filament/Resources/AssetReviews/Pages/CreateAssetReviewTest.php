<?php

use App\Enums\AssetReviewStatus;
use App\Filament\Resources\AssetReviews\AssetReviewResource;
use App\Filament\Resources\AssetReviews\Pages\CreateAssetReview;
use App\Models\AssetReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()
    ->use(RefreshDatabase::class);

test('admins can render the asset review creation page', function (): void {
    actingAsAdmin();

    get(AssetReviewResource::getUrl('create'))
        ->assertOk();
});

test('creating a review saves private images and starts pending', function (): void {
    Storage::fake('local');
    Storage::fake('public');
    actingAsAdmin();
    $page = livewire(CreateAssetReview::class)
        ->fillForm(['title' => 'Sample checklist', 'version' => 'v1', 'files' => [UploadedFile::fake()
            ->image('card.png')]]);

    $page->call('create');

    $page->assertHasNoFormErrors();
    $record = AssetReview::query()
        ->sole();
    expect($record->status)
        ->toBe(AssetReviewStatus::Pending)
        ->and($record->files)
        ->toHaveCount(1)
        ->and($record->files[0])
        ->toStartWith('asset-reviews/');
    Storage::disk('local')
        ->assertExists($record->files[0]);
    Storage::disk('public')
        ->assertMissing($record->files[0]);
});

test('non administrators cannot access review creation', function (): void {
    actingAs(User::factory()
        ->create());

    get(AssetReviewResource::getUrl('create'))
        ->assertForbidden();
});

test('HTML uploads cannot become review artwork', function (): void {
    Storage::fake('local');
    actingAsAdmin();
    $page = livewire(CreateAssetReview::class)
        ->fillForm(['title' => 'Sample checklist', 'version' => 'v1', 'files' => [UploadedFile::fake()
            ->create('preview.html', 1, 'text/html')]]);

    $page->call('create');

    $page->assertHasFormErrors(['files']);
    expect(AssetReview::query()
        ->count())
        ->toBe(0);
});
