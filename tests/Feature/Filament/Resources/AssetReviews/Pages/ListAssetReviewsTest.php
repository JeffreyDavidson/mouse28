<?php

use App\Filament\Resources\AssetReviews\AssetReviewResource;
use App\Models\AssetReview;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()
    ->use(RefreshDatabase::class);

test('admins can browse pending review sets from the sidebar', function (): void {
    actingAsAdmin();
    $record = AssetReview::factory()
        ->create();

    get(AssetReviewResource::getUrl())
        ->assertOk()
        ->assertSee('Asset Reviews')
        ->assertSee($record->title)
        ->assertSee('Pending review');
});
