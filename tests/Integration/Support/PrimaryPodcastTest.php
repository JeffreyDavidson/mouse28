<?php

use App\Models\Podcast;
use App\Support\PrimaryPodcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

covers(PrimaryPodcast::class);

pest()->use(RefreshDatabase::class);

test('the current show uses the configured defaults without saving a record', function (): void {
    $podcast = app(PrimaryPodcast::class)->current();

    expect($podcast->exists)->toBeFalse()
        ->and($podcast->name)
        ->toBe('Mouse28')
        ->and($podcast->slug)
        ->toBe('mouse28')
        ->and($podcast->description)
        ->toBe('Disney parks through the lens of raising a daughter with autism.')
        ->and(Podcast::query()->exists())
        ->toBeFalse();
});

test('the default show values come from podcast configuration', function (): void {
    config()->set('podcast.default_show', ['name' => 'Example Show', 'slug' => 'example-show', 'description' => 'An example.']);

    $created = app(PrimaryPodcast::class)->findOrCreate();

    expect($created->only(['name', 'slug', 'description']))->toBe(['name' => 'Example Show', 'slug' => 'example-show', 'description' => 'An example.']);
});

test('the saved show is created once and found again', function (): void {
    $first = app(PrimaryPodcast::class)->findOrCreate();
    $first->update(['name' => 'Mouse28 Weekly']);

    $again = app(PrimaryPodcast::class)->findOrCreate();

    expect($again->id)->toBe($first->id)
        ->and($again->name)
        ->toBe('Mouse28 Weekly')
        ->and($again->refresh()
            ->is_active)
        ->toBeTrue()
        ->and(Podcast::query()->count())
        ->toBe(1);
});

test('the oldest show is the primary one', function (): void {
    $oldest = Podcast::factory()->create();
    Podcast::factory()->create();

    expect(app(PrimaryPodcast::class)->current()
        ->is($oldest))->toBeTrue()
        ->and(app(PrimaryPodcast::class)->findOrCreate()
            ->is($oldest))
        ->toBeTrue();
});

test('the current show is read once per request', function (): void {
    Podcast::factory()->create();
    DB::flushQueryLog();
    DB::enableQueryLog();

    $primary = app(PrimaryPodcast::class);
    $firstRead = $primary->current();
    $secondRead = app(PrimaryPodcast::class)->current();

    expect($secondRead)->toBe($firstRead)
        ->and(DB::getQueryLog())
        ->toHaveCount(1);
});

test('the podcast model leaves finding the primary show to the resolver', function (): void {
    expect(method_exists(Podcast::class, 'info'))->toBeFalse()
        ->and(method_exists(Podcast::class, 'settings'))
        ->toBeFalse();
});
