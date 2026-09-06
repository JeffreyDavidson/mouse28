<?php

use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

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
