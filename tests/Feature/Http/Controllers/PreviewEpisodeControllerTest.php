<?php

use App\Models\Episode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;
use function Pest\Laravel\travel;

pest()->use(RefreshDatabase::class);

test('a signed preview link shows the draft to anyone holding it without exposing it to search', function (): void {
    $episode = Episode::factory()
        ->draft()
        ->create();

    get(URL::temporarySignedRoute('preview.episode', Date::now()->addHour(), ['episode' => $episode]))
        ->assertOk()
        ->assertViewIs('pages.episodes.show')
        ->assertViewHas('episode', fn (Episode $viewEpisode): bool => $viewEpisode->is($episode))
        ->assertViewHas('isPreview', true)
        ->assertViewHas('listenLinks')
        ->assertSee('Preview mode')
        ->assertSeeHtml('noindex,nofollow')
        ->assertDontSeeHtml('application/ld+json')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

test('expired preview links return forbidden', function (): void {
    $episode = Episode::factory()
        ->draft()
        ->create();
    $signed = URL::temporarySignedRoute('preview.episode', Date::now()->addHour(), ['episode' => $episode]);

    travel(2)->hours();

    get($signed)->assertForbidden();
});

test('tampered temporary preview links return forbidden', function (): void {
    $episode = Episode::factory()
        ->draft()
        ->create();
    $signed = URL::temporarySignedRoute('preview.episode', Date::now()->addHour(), ['episode' => $episode]);

    get("{$signed}0")->assertForbidden();
});

test('an invalid signature is refused before the draft is looked up, whether or not it exists', function (string $signature, bool $draftExists): void {
    $episode = Episode::factory()
        ->draft()
        ->create();
    $slug = $draftExists ? $episode->slug : 'missing-draft';
    DB::enableQueryLog();

    get(invalidlySignedRoute('preview.episode', ['episode' => $slug], $signature))
        ->assertForbidden();

    expect(DB::getQueryLog())->toBeEmpty();
})
    ->with('invalid signatures')
    ->with('existing and missing records');

test('the former numeric preview address no longer shows the draft', function (): void {
    $episode = Episode::factory()
        ->draft()
        ->create();

    expect(get("/preview/episodes/{$episode->id}")->status())->toBeIn([403, 404]);
});
