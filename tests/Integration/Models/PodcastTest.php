<?php

use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

pest()->use(RefreshDatabase::class);

test('podcast changes exclude the private contact email from the audit log', function (): void {
    $podcast = Podcast::query()->create(['name' => 'Original', 'email' => 'private@example.com']);
    $created = Activity::query()->firstOrFail();

    $podcast->update(['name' => 'Updated', 'email' => 'another@example.com']);

    $updated = Activity::query()->latest('id')->firstOrFail();
    expect($created->causer_id)->toBeNull()
        ->and($created->attribute_changes->get('attributes'))->not->toHaveKey('email')
        ->and($updated->attribute_changes->all())->toBe([
            'attributes' => ['name' => 'Updated'],
            'old' => ['name' => 'Original'],
        ]);

    $podcast->update(['email' => 'third@example.com']);

    expect(Activity::query()->count())->toBe(2);
});

test('podcast info provides site defaults without a settings record', function (): void {
    $podcast = Podcast::info();

    expect($podcast->exists)->toBeFalse()
        ->and($podcast->name)->toBe('Mouse28')
        ->and($podcast->description)->toBe('Disney parks through the lens of raising a daughter with autism.');
});

test('podcast settings persist one reusable settings record', function (): void {
    $settings = Podcast::settings();
    $settings->update(['name' => 'Mouse28 Weekly']);

    $retrievedSettings = Podcast::settings();

    expect($retrievedSettings->id)->toBe($settings->id)
        ->and($retrievedSettings->name)->toBe('Mouse28 Weekly')
        ->and(Podcast::query()->count())->toBe(1);
});

test('podcast info is read once per application request', function (): void {
    Podcast::query()->create(['name' => 'Mouse28 Weekly']);
    DB::flushQueryLog();
    DB::enableQueryLog();

    $firstRead = Podcast::info();
    $secondRead = Podcast::info();

    expect($secondRead)->toBe($firstRead)
        ->and(DB::getQueryLog())->toHaveCount(1);
});
