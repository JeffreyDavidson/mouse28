<?php

use App\Models\AssetReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()
    ->use(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
    Storage::disk('local')
        ->put('asset-reviews/card.png', UploadedFile::fake()
            ->image('card.png')
            ->getContent());
});

test('guests requesting review images are redirected to the panel login', function (): void {
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);
    $url = route('filament.admin.assetReviews.file', ['assetReview' => $record, 'file' => 0]);

    get($url)
        ->assertRedirect(route('filament.admin.auth.login'));
});

test('ordinary users cannot view private review images', function (): void {
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);
    actingAs(User::factory()
        ->create());

    get(route('filament.admin.assetReviews.file', ['assetReview' => $record, 'file' => 0]))
        ->assertForbidden();
});

test('an administrator can preview the private image without shared caching', function (): void {
    actingAsAdmin();
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);

    $response = get(route('filament.admin.assetReviews.file', ['assetReview' => $record, 'file' => 0]));

    $response->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertHeader('Content-Disposition', 'inline; filename=card.png')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    expect($response->headers->get('Cache-Control'))
        ->toContain('private')
        ->toContain('no-store');
});

test('an administrator can download the private image as an attachment', function (): void {
    actingAsAdmin();
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);

    $response = get(route('filament.admin.assetReviews.file', ['assetReview' => $record, 'file' => 0, 'download' => 1]));

    $response->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertHeader('Content-Disposition', 'attachment; filename=card.png')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    expect($response->headers->get('Cache-Control'))
        ->toContain('private')
        ->toContain('no-store');
});

test('a saved path cannot escape the review file directory', function (): void {
    actingAsAdmin();
    Storage::disk('local')
        ->put('secret.png', UploadedFile::fake()
            ->image('secret.png')
            ->getContent());
    $record = AssetReview::factory()
        ->create(['files' => ['../secret.png']]);

    get(route('filament.admin.assetReviews.file', ['assetReview' => $record, 'file' => 0]))
        ->assertNotFound();
});

test('missing images and out of range indexes return not found', function (int $file): void {
    actingAsAdmin();
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/missing.png']]);

    get(route('filament.admin.assetReviews.file', ['assetReview' => $record, 'file' => $file]))
        ->assertNotFound();
})
    ->with(['missing saved file' => 0, 'out of range index' => 1]);

test('HTML cannot be served as a review image', function (): void {
    actingAsAdmin();
    Storage::disk('local')
        ->put('asset-reviews/card.png', '<script>alert(1)</script>');
    $record = AssetReview::factory()
        ->create(['files' => ['asset-reviews/card.png']]);

    get(route('filament.admin.assetReviews.file', ['assetReview' => $record, 'file' => 0]))
        ->assertNotFound();
});
