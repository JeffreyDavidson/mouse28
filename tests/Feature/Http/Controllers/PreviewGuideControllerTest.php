<?php

use App\Models\Guide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('a signed preview link shows the draft to anyone holding it without exposing it to search', function (): void {
    $guide = Guide::factory()->draft()->create();

    get(URL::temporarySignedRoute('preview.guide', Date::now()->addHour(), ['guide' => $guide]))
        ->assertOk()
        ->assertViewIs('pages.guides.show')
        ->assertViewHas('guide', fn (Guide $viewGuide): bool => $viewGuide->is($guide))
        ->assertViewHas('isPreview', true)
        ->assertSee('Preview mode')
        ->assertSeeHtml('noindex,nofollow')
        ->assertDontSeeHtml('application/ld+json')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

test('preview links must be signed, untampered, and unexpired', function (string $case): void {
    $guide = Guide::factory()->draft()->create();
    $signed = URL::temporarySignedRoute('preview.guide', Date::now()->addHour(), ['guide' => $guide]);
    $url = match ($case) {
        'unsigned' => route('preview.guide', $guide),
        'tampered' => "{$signed}0",
        default => $signed,
    };

    if ($case === 'expired') {
        Date::setTestNow(Date::now()->addHours(2));
    }

    get($url)->assertForbidden();
})->with(['unsigned', 'tampered', 'expired']);

test('an invalid signature is refused before the draft is looked up, whether or not it exists', function (string $signature, bool $draftExists): void {
    $guide = Guide::factory()
        ->draft()
        ->create();
    $slug = $draftExists ? $guide->slug : 'missing-draft';
    DB::enableQueryLog();

    get(invalidlySignedRoute('preview.guide', ['guide' => $slug], $signature))
        ->assertForbidden();

    expect(DB::getQueryLog())->toBeEmpty();
})
    ->with('invalid signatures')
    ->with('existing and missing records');

test('the former numeric preview address no longer shows the draft', function (): void {
    $guide = Guide::factory()->draft()->create();

    expect(get("/preview/guides/{$guide->id}")->status())->toBeIn([403, 404]);
});
