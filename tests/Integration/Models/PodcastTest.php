<?php

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('a podcast lists its episodes and its latest published one', function (): void {
    $podcast = Podcast::factory()->create();
    $older = Episode::factory()->for($podcast)->create(['published_at' => now()->subDays(3)]);
    $newer = Episode::factory()->for($podcast)->create(['published_at' => now()->subDay()]);
    Episode::factory()->for($podcast)->draft()->create();
    Episode::factory()->for(Podcast::factory())->create();

    expect($podcast->episodes()->count())->toBe(3)
        ->and($podcast->publishedEpisodes->modelKeys())->toEqualCanonicalizing([$older->id, $newer->id])
        ->and($podcast->latestEpisode()?->is($newer))->toBeTrue()
        ->and($newer->podcast?->is($podcast))->toBeTrue();
});

test('only active podcasts are in the active scope', function (): void {
    $active = Podcast::factory()->create();
    Podcast::factory()->inactive()->create();

    expect(Podcast::query()->active()->pluck('id')->all())->toBe([$active->id]);
});

test('trashing a podcast trashes its episodes and restoring it brings back only those', function (): void {
    $podcast = Podcast::factory()->create();
    $trashedEarlier = Episode::factory()->for($podcast)->create();
    $trashedEarlier->delete();
    $this->travel(5)->seconds();
    $episode = Episode::factory()->for($podcast)->create();

    $podcast->delete();

    expect($episode->refresh()->trashed())->toBeTrue();

    $this->travel(5)->seconds();
    $podcast->restore();

    expect($episode->refresh()->trashed())->toBeFalse()
        ->and($trashedEarlier->refresh()->trashed())->toBeTrue();
});

test('permanently deleting a podcast permanently deletes its episodes', function (): void {
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()->for($podcast)->create();

    $podcast->forceDelete();

    expect(Episode::withTrashed()->whereKey($episode->id)->exists())->toBeFalse()
        ->and(Podcast::withTrashed()->whereKey($podcast->id)->exists())->toBeFalse();
});

test('a new episode without a podcast joins the mouse28 show', function (): void {
    $episode = Episode::factory()->create(['podcast_id' => null]);

    expect($episode->podcast?->slug)->toBe('mouse28');
});

test('an episode keeps its guest details', function (): void {
    $episode = Episode::factory()->create([
        'guest_name' => 'Guest Name',
        'guest_title' => 'Guest title',
        'guest_url' => 'https://example.test/guest',
    ]);

    expect($episode->refresh())
        ->guest_name->toBe('Guest Name')
        ->guest_title->toBe('Guest title')
        ->guest_url->toBe('https://example.test/guest');
});

test('creating a podcast without a slug names it from its name', function (): void {
    $podcast = Podcast::factory()->create(['name' => 'Park Family Podcast', 'slug' => null]);

    expect($podcast->slug)->toBe('park-family-podcast');
});
