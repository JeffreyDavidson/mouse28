<?php

use App\Filament\Resources\AssetReviews\AssetReviewResource;
use App\Models\AssetReview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('saved review previews fit desktop and phone screens and the approval dialog works', function (): void {
    // Arrange
    actingAsAdmin();
    Storage::fake('local');
    Storage::disk('local')->put('asset-reviews/preview.png', UploadedFile::fake()
        ->image('preview.png', 1080, 1440)
        ->getContent());
    $record = AssetReview::factory()->create(['files' => ['asset-reviews/preview.png']]);
    $page = visit(AssetReviewResource::getUrl('edit', ['record' => $record]));

    // Act
    $page->resize(1440, 1000);

    // Assert
    $page->assertSee('Pending review')
        ->assertScript(browserWaitForScript('document.querySelector("section[aria-label=\"Saved review set\"] img")?.naturalWidth > 0'), true)
        ->assertScript($this->horizontalOverflowScript(), 0)
        ->assertNoJavaScriptErrors()
        ->screenshot(filename: 'asset-review-desktop');
    copy(base_path('tests/Browser/Screenshots/asset-review-desktop.png'), storage_path('framework/testing/asset-review-desktop.png'));

    // Act
    $page->resize(390, 844);

    // Assert
    $page->assertScript($this->horizontalOverflowScript(), 0)
        ->assertNoJavaScriptErrors()
        ->screenshot(filename: 'asset-review-mobile');
    copy(base_path('tests/Browser/Screenshots/asset-review-mobile.png'), storage_path('framework/testing/asset-review-mobile.png'));

    // Act
    $page->resize(1440, 1000);
    $page->click('button:has-text("Approve")');

    // Assert
    $page->assertSee('Approval does not publish the set.')
        ->assertNoJavaScriptErrors();

    // Act
    $page->click('[role="dialog"] button:has-text("Approve")');

    // Assert
    $page->assertSee('Approved')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke');
