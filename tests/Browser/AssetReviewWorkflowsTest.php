<?php

use App\Filament\Resources\AssetReviews\AssetReviewResource;
use App\Models\AssetReview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function assetReviewRenderedFileOrderScript(): string
{
    return <<<'JS'
        (() => [...document.querySelectorAll('.filepond--item')]
            .filter((item) => item.getBoundingClientRect().height > 0)
            .sort((first, second) => first.getBoundingClientRect().top - second.getBoundingClientRect().top)
            .map((item) => item.querySelector('.filepond--file-info-main')?.textContent.trim() ?? '')
            .filter(Boolean)
            .join('|'))()
        JS;
}

function assetReviewPointerDragScript(string $sourceFile, string $targetFile): string
{
    $sourceFile = json_encode($sourceFile, JSON_THROW_ON_ERROR);
    $targetFile = json_encode($targetFile, JSON_THROW_ON_ERROR);

    return <<<JS
        (async () => {
            const findFile = (name) => [...document.querySelectorAll('.filepond--item')]
                .find((item) => item.querySelector('.filepond--file-info-main')?.textContent.trim() === name)

            const source = findFile({$sourceFile})
            const target = findFile({$targetFile})

            if (!source || !target) {
                return false
            }

            const sourceBounds = source.getBoundingClientRect()
            const targetBounds = target.getBoundingClientRect()
            const sourceX = sourceBounds.left + (sourceBounds.width / 2)
            const sourceY = sourceBounds.top + (sourceBounds.height / 2)
            const targetX = targetBounds.left + (targetBounds.width / 2)
            const targetY = targetBounds.top + (targetBounds.height / 2)
            const dispatch = (type, x, y, buttons) => source.dispatchEvent(new PointerEvent(type, {
                bubbles: true,
                button: 0,
                buttons,
                cancelable: true,
                clientX: x,
                clientY: y,
                composed: true,
                isPrimary: true,
                pointerId: 1,
                pointerType: 'mouse',
            }))

            dispatch('pointerdown', sourceX, sourceY, 1)

            for (let step = 1; step <= 10; step++) {
                const progress = step / 10

                dispatch(
                    'pointermove',
                    sourceX + ((targetX - sourceX) * progress),
                    sourceY + ((targetY - sourceY) * progress),
                    1,
                )

                await new Promise(requestAnimationFrame)
            }

            dispatch('pointerup', targetX, targetY, 0)

            return true
        })()
        JS;
}

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

test('dragged review image order survives saving and reopening', function (): void {
    // Arrange
    actingAsAdmin();
    Storage::fake('local');

    $files = [
        'asset-reviews/alpha.png' => UploadedFile::fake()
            ->image('alpha.png', 20, 30)
            ->getContent(),
        'asset-reviews/bravo.png' => UploadedFile::fake()
            ->image('bravo.png', 30, 20)
            ->getContent(),
        'asset-reviews/charlie.png' => UploadedFile::fake()
            ->image('charlie.png', 40, 20)
            ->getContent(),
    ];

    $originalHashes = [];

    foreach ($files as $path => $contents) {
        Storage::disk('local')
            ->put($path, $contents);
        $originalHashes[$path] = hash('sha256', $contents);
    }

    $record = AssetReview::factory()
        ->create(['files' => array_keys($files)]);
    $url = AssetReviewResource::getUrl('edit', ['record' => $record]);
    $page = visit($url)
        ->assertScript(browserWaitForScript("document.querySelectorAll('.filepond--item').length === 3"), true)
        ->assertNoJavaScriptErrors();

    $renderedFileOrder = $page->script(assetReviewRenderedFileOrderScript());

    if (! is_string($renderedFileOrder)) {
        throw new UnexpectedValueException('The rendered file order must be text.');
    }

    $initialOrder = explode('|', $renderedFileOrder);

    expect($initialOrder)
        ->toHaveCount(3)
        ->and($initialOrder)
        ->each()
        ->toBeIn(['alpha.png', 'bravo.png', 'charlie.png']);

    $draggedFile = $initialOrder[2];
    $dropTarget = $initialOrder[0];
    $selectedOrder = [$draggedFile, $dropTarget, $initialOrder[1]];
    $pathsByFilename = array_combine(array_map(basename(...), array_keys($files)), array_keys($files));
    $selectedPaths = array_map(
        fn (string $filename): string => $pathsByFilename[$filename],
        $selectedOrder,
    );

    // Act
    $dragCompleted = $page->script(assetReviewPointerDragScript($draggedFile, $dropTarget));

    // Assert
    expect($dragCompleted)
        ->toBeTrue();
    $page->assertScript(
        browserWaitForScript(assetReviewRenderedFileOrderScript().' === '.json_encode(implode('|', $selectedOrder), JSON_THROW_ON_ERROR)),
        true,
    );
    expect($selectedOrder)
        ->not->toBe($initialOrder);

    // Act
    $page->click('button[wire\\:target="save"]');

    // Assert
    $page->assertScript(
        browserWaitForScript('document.querySelector(\'.fi-no[role="status"] .fi-no-notification-title\')?.textContent.trim() === \'Saved\''),
        true,
    )
        ->assertNoJavaScriptErrors();

    // Act
    $reopenedPage = visit($url)
        ->assertScript(browserWaitForScript("document.querySelectorAll('.filepond--item').length === 3"), true);

    // Assert
    $reopenedPage
        ->assertScript(assetReviewRenderedFileOrderScript(), implode('|', $selectedOrder))
        ->assertNoJavaScriptErrors();

    $savedPaths = $record->refresh()
        ->files;
    $savedHashes = [];

    foreach ($savedPaths as $path) {
        $savedContents = Storage::disk('local')
            ->get($path);

        if (! is_string($savedContents)) {
            throw new UnexpectedValueException("The saved file [{$path}] could not be read.");
        }

        $savedHashes[$path] = hash('sha256', $savedContents);
    }

    ksort($originalHashes);
    ksort($savedHashes);

    expect($savedPaths)
        ->toBe($selectedPaths)
        ->and($savedHashes)
        ->toBe($originalHashes);
})->group('browser-smoke');
